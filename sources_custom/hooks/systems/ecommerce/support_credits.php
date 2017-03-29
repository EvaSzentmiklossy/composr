<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    composr_homesite_support_credits
 */

/**
 * eCommerce product hook.
 */
class Hook_ecommerce_support_credits
{
    /**
     * Get the overall categorisation for the products handled by this eCommerce hook.
     *
     * @return ?array A map of product categorisation details (null: disabled).
     */
    public function get_product_category()
    {
        require_lang('customers');

        return array(
            'category_name' => do_lang('CREDITS'),
            'category_description' => do_lang_tempcode('CUSTOMER_SUPPORT_CREDITS_DESCRIPTION'),
            'category_image_url' => find_theme_image('icons/48x48/menu/pages/help'),
        );
    }

    /**
     * Get the products handled by this eCommerce hook.
     *
     * IMPORTANT NOTE TO PROGRAMMERS: This function may depend only on the database, and not on get_member() or any GET/POST values.
     *  Such dependencies will break IPN, which works via a Guest and no dependable environment variables. It would also break manual transactions from the Admin Zone.
     *
     * @param  ?ID_TEXT $search Product being searched for (null: none).
     * @return array A map of product name to list of product details.
     */
    public function get_products($search = null)
    {
        if (!$GLOBALS['SITE_DB']->table_exists('credit_purchases')) {
            return array();
        }

        if (get_forum_type() != 'cns') {
            return array();
        }

        require_lang('customers');

        $products = array();
        $bundles = array(1, 2, 3, 4, 5, 6, 9, 20, 25, 35, 50, 90, 180, 550);
        foreach ($bundles as $bundle) {
            $products[strval($bundle) . '_CREDITS'] = array(
                'item_name' => do_lang('CUSTOMER_SUPPORT_CREDITS', integer_format($bundle)),
                'item_description' => new Tempcode(),
                'item_image_url' => '',

                'type' => PRODUCT_PURCHASE,
                'type_special_details' => null,

                'price' => $bundle * floatval(get_option('support_credit_price')),
                'currency' => get_option('currency'),
                'price_points' => null,
                'discount_points__num_points' => null,
                'discount_points__price_reduction' => null,

                'tax_code' => get_option('support_credit_tax_code'),
                'shipping_cost' => 0.00,
                'needs_shipping_address' => false,
            );
        }

        return $products;
    }

    /**
     * Check whether the product codename is available for purchase by the member.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  MEMBER $member_id The member we are checking against.
     * @param  integer $req_quantity The number required.
     * @param  boolean $must_be_listed Whether the product must be available for public listing.
     * @return integer The availability code (a ECOMMERCE_PRODUCT_* constant).
     */
    public function is_available($type_code, $member_id, $req_quantity = 1, $must_be_listed = false)
    {
        return ($member_id != $GLOBALS['FORUM_DRIVER']->get_guest_id()) ? ECOMMERCE_PRODUCT_AVAILABLE : ECOMMERCE_PRODUCT_NO_GUESTS;
    }

    /**
     * Get the terms and conditions for use in the purchasing module.
     *
     * @param  string $type_code The product in question.
     * @return string The message.
     */
    public function get_terms($type_code)
    {
        require_code('textfiles');
        return read_text_file('support_credits_terms', 'EN');
    }

    /**
     * Get fields that need to be filled in in the purchasing module.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  boolean $from_admin Whether this is being called from the Admin Zone. If so, optionally different fields may be used, including a purchase_id field for direct purchase ID input.
     * @return ?array A triple: The fields (null: none), The text (null: none), The JavaScript (null: none).
     */
    public function get_needed_fields($type_code, $from_admin = false)
    {
        if (!$from_admin) {
            return null;
        }

        require_lang('customers');

        // Check if we've already been passed a member ID and use it to pre-populate the field
        $member_id = get_param_integer('member_id', null);
        $username = $GLOBALS['FORUM_DRIVER']->get_username(($member_id === null) ? get_member() : $member_id);

        $fields = new Tempcode();
        $fields->attach(form_input_username(do_lang('USERNAME'), do_lang('USERNAME_CREDITS_FOR'), 'member_username', $username, true));

        ecommerce_attach_memo_field_if_needed($fields);

        return array($fields, null, null);
    }

    /**
     * Get the filled in fields and do something with them.
     * May also be called from Admin Zone to get a default purchase ID (i.e. when there's no post context).
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  boolean $from_admin Whether this is being called from the Admin Zone. If so, optionally different fields may be used, including a purchase_id field for direct purchase ID input.
     * @return array A pair: The purchase ID, a confirmation box to show (null for no specific confirmation).
     */
    public function handle_needed_fields($type_code, $from_admin = false)
    {
        $product_array = explode('_', $type_code, 2);
        $num_credits = intval($product_array[0]);
        if ($num_credits == 0) {
            return;
        }

        $manual = 0;

        $member_id = get_member();

        // Allow admins to specify the member who should receive the credits with the field in get_needed_fields
        if ($from_admin) {
            $id = post_param_integer('member_id', null);
            if ($id !== null) {
                $manual = 1;
                $member_id = $id;
            } else {
                $username = post_param_string('member_username', null);
                if ($username !== null) {
                    $manual = 1;
                    $member_id = $GLOBALS['FORUM_DRIVER']->get_member_from_username($username);
                }
            }
        }

        $purchase_id = strval($GLOBALS['SITE_DB']->query_insert('credit_purchases', array('member_id' => $member_id, 'date_and_time' => time(), 'num_credits' => $num_credits, 'is_manual' => $manual, 'purchase_validated' => 0), true));
        return array($purchase_id, null);
    }

    /**
     * Handling of a product purchase change state.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  ID_TEXT $purchase_id The purchase ID.
     * @param  array $details Details of the product, with added keys: TXN_ID, STATUS, ORDER_STATUS.
     * @return boolean Whether the product was automatically dispatched (if not then hopefully this function sent a staff notification).
     */
    public function actualiser($type_code, $purchase_id, $details)
    {
        if ($details['STATUS'] != 'Completed') {
            return false;
        }

        $row = $GLOBALS['SITE_DB']->query_select('credit_purchases', array('member_id', 'num_credits'), array('purchase_validated' => 0, 'purchase_id' => intval($purchase_id)), '', 1);
        if (count($row) != 1) {
            return;
        }
        $member_id = $row[0]['member_id'];
        if ($member_id === null) {
            return;
        }
        $num_credits = $row[0]['num_credits'];

        require_code('mantis');
        $cpf_id = get_credits_profile_field_id();
        if ($cpf_id === null) {
            return;
        }

        // Increment the number of credits this customer has
        require_code('cns_members_action2');
        $fields = cns_get_custom_field_mappings($member_id);
        cns_set_custom_field($member_id, $cpf_id, strval($fields['field_' . strval($cpf_id)] + $num_credits));

        // Update the row in the credit_purchases table
        $GLOBALS['SITE_DB']->query_update('credit_purchases', array('purchase_validated' => 1), array('purchase_id' => intval($purchase_id)));

        $GLOBALS['SITE_DB']->query_insert('ecom_sales', array('date_and_time' => time(), 'member_id' => $member_id, 'details' => do_lang('CREDITS', null, null, null, get_site_default_lang()), 'details2' => strval($num_credits), 'txn_id' => $details['TXN_ID']));

        return true;
    }

    /**
     * Get the member who made the purchase.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  ID_TEXT $purchase_id The purchase ID.
     * @return ?MEMBER The member ID (null: none).
     */
    public function member_for($type_code, $purchase_id)
    {
        return $GLOBALS['SITE_DB']->query_select_value_if_there('credit_purchases', 'member_id', array('purchase_id' => intval($purchase_id)));
    }
}
