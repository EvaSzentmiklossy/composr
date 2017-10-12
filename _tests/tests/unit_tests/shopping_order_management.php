<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2017

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    testing_platform
 */

/**
 * Composr test case class (unit testing).
 */
class shopping_order_management_test_set extends cms_test_case
{
    public $admin_ecom;
    public $item_id;
    public $order_id;
    public $access_mapping;
    public $admin_shopping;

    public function setUp()
    {
        parent::setUp();

        require_code('ecommerce');
        require_code('autosave');
        require_code('shopping');
        require_code('form_templates');

        require_lang('shopping');

        $txn_id = 'ddfsfdsdfsdfs';

        $this->order_id = $GLOBALS['SITE_DB']->query_insert('shopping_orders', array(
            'member_id' => get_member(),
            'session_id' => get_session_id(),
            'add_date' => time(),
            'order_status' => 'NEW',
            'total_price' => 10.00,
            'total_tax_derivation' => '',
            'total_tax' => 1.00,
            'total_tax_tracking' => '',
            'total_shipping_cost' => 2.00,
            'total_shipping_tax' => 0.00,
            'total_product_weight' => 0.00,
            'total_product_length' => 0.00,
            'total_product_width' => 0.00,
            'total_product_height' => 0.00,
            'order_currency' => 'GBP',
            'notes' => '',
            'txn_id' => $txn_id,
            'purchase_through' => 'cart',
        ), true);

        $GLOBALS['SITE_DB']->query_delete('ecom_transactions', array('id' => $txn_id), '', 1);
        $GLOBALS['SITE_DB']->query_insert('ecom_transactions', array(
            'id' => $txn_id,
            't_type_code' => 'cart_orders',
            't_purchase_id' => strval($this->order_id),
            't_status' => 'Completed',
            't_reason' => '',
            't_amount' => 12.00,
            't_tax_derivation' => '',
            't_tax' => 1.00,
            't_tax_tracking' => '',
            't_currency' => 'GBP',
            't_parent_txn_id' => '',
            't_time' => time(),
            't_pending_reason' => '',
            't_memo' => '',
            't_payment_gateway' => 'manual',
            't_invoicing_breakdown' => '',
            't_member_id' => get_member(),
            't_session_id' => get_session_id(),
        ));

        $this->access_mapping = array(db_get_first_id() => 4);

        require_code('adminzone/pages/modules/admin_ecommerce.php');
        $this->admin_ecom = new Module_admin_ecommerce();

        require_code('adminzone/pages/modules/admin_shopping.php');
        $this->admin_shopping = new Module_admin_shopping();
        if (method_exists($this->admin_shopping, 'pre_run')) {
            $this->admin_shopping->pre_run();
        }
        $this->admin_shopping->run();
    }

    public function testShowOrders()
    {
        return $this->admin_shopping->show_orders();
    }

    public function testOrderDetails()
    {
        $order_id = $GLOBALS['SITE_DB']->query_select_value('shopping_orders', 'MAX(id)');
        $_GET['id'] = strval($order_id);
        return $this->admin_shopping->order_details();
    }

    public function testAddNoteToOrderUI()
    {
        $order_id = $GLOBALS['SITE_DB']->query_select_value('shopping_orders', 'MAX(id)');
        $_GET['id'] = strval($order_id);
        $this->admin_shopping->add_note();
    }

    public function testAddNoteToOrderActualiser()
    {
        $order_id = $GLOBALS['SITE_DB']->query_select_value('shopping_orders', 'MAX(id)');
        $_POST['order_id'] = $order_id;
        $_POST['note'] = 'Test note';
        $this->admin_shopping->_add_note();
    }

    public function testOrderDispatch()
    {
        $order_id = $GLOBALS['SITE_DB']->query_select_value_if_there('shopping_orders', 'MAX(id)', array('order_status' => 'ORDER_STATUS_payment_received'));
        if ($order_id !== null) {
            $_GET['id'] = $order_id;
            $this->admin_shopping->dispatch();
        }
    }

    public function testOrderDispatchNotification()
    {
        $order_id = $GLOBALS['SITE_DB']->query_select_value('shopping_orders', 'MAX(id)');
        $this->admin_shopping->send_dispatch_notification($order_id);
    }

    public function testDeleteOrder()
    {
        $order_id = $GLOBALS['SITE_DB']->query_select_value('shopping_orders', 'MAX(id)');
        $_GET['id'] = $order_id;
        $this->admin_shopping->delete_order();
    }

    public function testReturnOrder()
    {
        $order_id = $GLOBALS['SITE_DB']->query_select_value('shopping_orders', 'MAX(id)');
        $_GET['id'] = $order_id;
        $this->admin_shopping->return_order();
    }

    public function testHoldOrder()
    {
        $order_id = $GLOBALS['SITE_DB']->query_select_value('shopping_orders', 'MAX(id)');
        $_GET['id'] = $order_id;
        $this->admin_shopping->hold_order();
    }

    public function testOrderExportUI()
    {
        $this->admin_shopping->export_orders();
    }

    public function tearDown()
    {
        $GLOBALS['SITE_DB']->query_delete('shopping_orders', array('id' => $this->order_id), '', 1);
        parent::tearDown();
    }
}