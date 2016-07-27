<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    shopping
 */

/*
Orders are compound-products. They link together multiple eCommerce items into a single purchasable set with a fixed price.
*/

/**
 * Handling shopping orders and dispatch thereof.
 *
 * @param  ID_TEXT $purchase_id The purchase ID.
 * @param  array $details Details of the product.
 * @param  ID_TEXT $type_code The product codename.
 * @param  ID_TEXT $payment_status The status this transaction is telling of
 * @set    Pending Completed SModified SCancelled
 * @param  SHORT_TEXT $txn_id The transaction ID
 */
function handle_product_orders($purchase_id, $details, $type_code, $payment_status, $txn_id)
{
    require_code('shopping');

    $old_status = $GLOBALS['SITE_DB']->query_select_value('shopping_order_details', 'dispatch_status', array('order_id' => intval($purchase_id)));

    if ($old_status != $details['ORDER_STATUS']) {
        $GLOBALS['SITE_DB']->query_update('shopping_order_details', array('dispatch_status' => $details['ORDER_STATUS']), array('order_id' => intval($purchase_id)));

        $GLOBALS['SITE_DB']->query_update('shopping_order', array('order_status' => $details['ORDER_STATUS'], 'transaction_id' => $details['txn_id']), array('id' => intval($purchase_id)));

        // Copy in memo from transaction, as customer notes
        $old_memo = $GLOBALS['SITE_DB']->query_select_value('shopping_order', 'notes', array('id' => intval($purchase_id)));
        if ($old_memo == '') {
            $memo = $GLOBALS['SITE_DB']->query_select_value('transactions', 't_memo', array('id' => $txn_id));
            if ($memo != '') {
                require_lang('shopping');
                $memo = do_lang('CUSTOMER_NOTES') . "\n" . $memo;
                $GLOBALS['SITE_DB']->query_update('shopping_order', array('notes' => $memo), array('id' => intval($purchase_id)), '', 1);
            }
        }

        if ($details['ORDER_STATUS'] == 'ORDER_STATUS_payment_received') {
            purchase_done_staff_mail(intval($purchase_id));
        }
    }
}

/**
 * eCommerce product hook.
 */
class Hook_ecommerce_cart_orders
{
    /**
     * Get the products handled by this eCommerce hook.
     *
     * IMPORTANT NOTE TO PROGRAMMERS: This function may depend only on the database, and not on get_member() or any GET/POST values.
     *  Such dependencies will break IPN, which works via a Guest and no dependable environment variables. It would also break manual transactions from the Admin Zone.
     *
     * @param  boolean $site_lang Whether to make sure the language for item_name is the site default language (crucial for when we read/go to third-party sales systems and use the item_name as a key).
     * @param  ?ID_TEXT $search Product being searched for (null: none).
     * @param  boolean $search_item_names Whether $search refers to the item name rather than the product codename.
     * @return array A map of product name to list of product details.
     */
    public function get_products($site_lang = false, $search = null, $search_item_names = false)
    {
        $products = array();

        require_lang('shopping');

        if (php_function_allowed('set_time_limit')) {
            @set_time_limit(0);
        }

        if (!is_null($search)) {
            $where = '1=1';
            if (!$search_item_names) {
                $l = do_lang('CART_ORDER', '', null, null, $site_lang ? get_site_default_lang() : user_lang());
                if (substr($search, 0, strlen($l)) != $l) {
                    return array();
                }
                $where .= ' AND id=' . strval(intval(substr($search, strlen($l))));
            }
        } else {
            $where = ('(' . db_string_equal_to('order_status', 'ORDER_STATUS_awaiting_payment') . ' OR ' . db_string_equal_to('order_status', 'ORDER_STATUS_payment_received') . ')');
        }

        if (is_null($search)) {
            $count = $GLOBALS['SITE_DB']->query_value_if_there('SELECT COUNT(*) FROM ' . get_table_prefix() . 'shopping_order WHERE ' . $where);
            if ($count > 50) {
                return array(); // Too many to list
            }
        }

        $start = 0;
        do {
            $orders = $GLOBALS['SITE_DB']->query('SELECT id,tot_price FROM ' . get_table_prefix() . 'shopping_order WHERE ' . $where, 500, null, false, true);

            foreach ($orders as $order) {
                $products[do_lang('shopping:CART_ORDER', strval($order['id']), null, null, $site_lang ? get_site_default_lang() : user_lang())] = array(
                    PRODUCT_ORDERS,
                    $order['tot_price'],
                    'handle_product_orders',
                    array(),
                    do_lang('CART_ORDER', strval($order['id']), null, null, $site_lang ? get_site_default_lang() : user_lang()),
                    get_option('currency'),
                );
            }

            $start += 500;
        } while (count($orders) == 500);

        return $products;
    }

    /**
     * Find the corresponding member to a given purchase ID.
     *
     * @param  ID_TEXT $purchase_id The purchase ID.
     * @return ?MEMBER The member (null: unknown / can't perform operation).
     */
    public function member_for($purchase_id)
    {
        return $GLOBALS['SITE_DB']->query_select_value_if_there('shopping_order', 'c_member', array('id' => intval($purchase_id)));
    }

    /**
     * Function to return dispatch type of product.
     * (this hook represents a cart order, so find all of it's sub products's dispatch type and decide cart order product's dispatch type - automatic or manual)
     *
     * @param  SHORT_TEXT $order_id Item ID.
     * @return SHORT_TEXT Dispatch type.
     */
    public function get_product_dispatch_type($order_id)
    {
        $rows = $GLOBALS['SITE_DB']->query_select('shopping_order_details', array('*'), array('order_id' => $order_id));

        foreach ($rows as $item) {
            if (is_null($item['p_type'])) {
                continue;
            }

            require_code('hooks/systems/ecommerce/' . filter_naughty_harsh($item['p_type']));

            $object = object_factory('Hook_ecommerce_' . filter_naughty_harsh($item['p_type']));

            //if any of the product's dispatch type is manual, return type as 'manual'
            if ($object->get_product_dispatch_type() == 'manual') {
                return 'manual';
            }
        }

        // If none of product items have manual dispatch, return order dispatch as automatic.
        return 'automatic';
    }

    /**
     * Function to return order ID from formatted of order ID.
     *
     * @param  SHORT_TEXT $item_name Item ID.
     * @return SHORT_TEXT Dispatch type.
     */
    public function set_needed_fields($item_name)
    {
        return str_replace('#', '', $item_name);
    }
}
