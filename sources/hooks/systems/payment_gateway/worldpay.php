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
 * @package    ecommerce
 */

/**
 * Hook class.
 */
class Hook_payment_gateway_worldpay
{
    // This is the Hosted Payment Pages API http://support.worldpay.com/support/kb/gg/hpp/Content/Home.htm
    // Requires:
    //  the "Payment Response URL" set in control panel should be set to "http://<WPDISPLAY ITEM=MC_callback>"
    //  the "Payment Response enabled?" and "Enable Recurring Payment Response" and "Enable the Shopper Response" should all be ticked (checked)
    //  the "Payment Response password" is the Composr "Callback password" option; it may be blank
    //  the "Installation ID" (a number given to you) is the Composr "Gateway username" option and also "Testing mode gateway username" option (it's all the same installation ID)
    //  the "MD5 secret for transactions" is the Composr "Gateway digest code" option; it may be blank
    //  the account must be set as 'live' in control panel once testing is done
    //  the "Shopper Redirect URL" should be left blank - arbitrary URLs are not supported, and Composr automatically injects a redirect response into Payment Response URL
    //  Logos, refund policies, and contact details [e-mail, phone, postal], may need coding into the templates (Worldpay have policies and checks). ECOM_LOGOS_WORLDPAY.tpl is included into the payment process automatically and does much of this
    //  FuturePay must be enabled for subscriptions to work (contact WorldPay about it)

    /**
     * Find a transaction fee from a transaction amount. Regular fees aren't taken into account.
     *
     * @param  float $amount A transaction amount.
     * @return float The fee.
     */
    public function get_transaction_fee($amount)
    {
        return 0.045 * $amount; // for credit card. Debit card is a flat 50p
    }

    /**
     * Get the gateway username.
     *
     * @return string The answer.
     */
    protected function _get_username()
    {
        return ecommerce_test_mode() ? get_option('payment_gateway_test_username') : get_option('payment_gateway_username');
    }

    /**
     * Get the remote form URL.
     *
     * @return URLPATH The remote form URL.
     */
    protected function _get_remote_form_url()
    {
        return 'https://' . (ecommerce_test_mode() ? 'select-test' : 'select') . '.worldpay.com/wcc/purchase';
    }

    /**
     * Get the card/gateway logos and other gateway-required details.
     *
     * @return Tempcode The stuff.
     */
    public function get_logos()
    {
        $inst_id = ecommerce_test_mode() ? get_option('payment_gateway_test_username') : get_option('payment_gateway_username');
        $address = str_replace("\n", '<br />', escape_html(get_option('pd_address')));
        $email = get_option('pd_email');
        $number = get_option('pd_number');
        return do_template('ECOM_LOGOS_WORLDPAY', array('_GUID' => '4b3254b330b3b1719d66d2b754c7a8c8', 'INST_ID' => $inst_id, 'PD_ADDRESS' => $address, 'PD_EMAIL' => $email, 'PD_NUMBER' => $number));
    }

    /**
     * Generate a transaction ID.
     *
     * @return string A transaction ID.
     */
    public function generate_trans_id()
    {
        require_code('crypt');
        return get_rand_password();
    }

    /**
     * Make a transaction (payment) button.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  SHORT_TEXT $item_name The human-readable product title.
     * @param  ID_TEXT $purchase_id The purchase ID.
     * @param  float $amount A transaction amount.
     * @param  ID_TEXT $currency The currency to use.
     * @return Tempcode The button.
     */
    public function make_transaction_button($type_code, $item_name, $purchase_id, $amount, $currency)
    {
        $username = $this->_get_username();
        $ipn_url = $this->_get_remote_form_url();
        $email_address = $GLOBALS['FORUM_DRIVER']->get_member_email_address(get_member());
        $trans_id = $this->generate_trans_id();
        $digest_option = get_option('payment_gateway_digest');
        //$digest = md5((($digest_option == '') ? ($digest_option . ':') : '') . $trans_id . ':' . float_to_raw_string($amount) . ':' . $currency);  Deprecated
        $digest = md5((($digest_option == '') ? ($digest_option . ':') : '') . ';' . 'cartId:amount:currency;' . $trans_id . ';' . float_to_raw_string($amount) . ';' . $currency);

        $GLOBALS['SITE_DB']->query_insert('trans_expecting', array(
            'id' => $trans_id,
            'e_type_code' => $type_code,
            'e_purchase_id' => $purchase_id,
            'e_item_name' => $item_name,
            'e_member_id' => get_member(),
            'e_amount' => float_to_raw_string($amount),
            'e_currency' => $currency,
            'e_ip_address' => get_ip_address(),
            'e_session_id' => get_session_id(),
            'e_time' => time(),
            'e_length' => null,
            'e_length_units' => '',
        ));

        return do_template('ECOM_TRANSACTION_BUTTON_VIA_WORLDPAY', array(
            '_GUID' => '56c78a4e16c0e7f36fcfbe57d37bc3d3',
            'TYPE_CODE' => $type_code,
            'ITEM_NAME' => $item_name,
            'DIGEST' => $digest,
            'TEST_MODE' => ecommerce_test_mode(),
            'PURCHASE_ID' => $trans_id, // cartID in Worldpay, has to be unique so we generate a transaction ID and store true purchase_id within that
            'AMOUNT' => float_to_raw_string($amount),
            'CURRENCY' => $currency,
            'USERNAME' => $username,
            'IPN_URL' => $ipn_url,
            'EMAIL_ADDRESS' => $email_address,
        ));
    }

    /**
     * Make a subscription (payment) button.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  SHORT_TEXT $item_name The human-readable product title.
     * @param  ID_TEXT $purchase_id The purchase ID.
     * @param  float $amount A transaction amount.
     * @param  integer $length The subscription length in the units.
     * @param  ID_TEXT $length_units The length units.
     * @set    d w m y
     * @param  ID_TEXT $currency The currency to use.
     * @return Tempcode The button.
     */
    public function make_subscription_button($type_code, $item_name, $purchase_id, $amount, $length, $length_units, $currency)
    {
        $username = $this->_get_username();
        $ipn_url = $this->_get_remote_form_url();
        $trans_id = $this->generate_trans_id();
        $length_units_2 = '1';
        $first_repeat = time();
        switch ($length_units) {
            case 'd':
                $length_units_2 = '1';
                $first_repeat = 60 * 60 * 24 * $length;
                break;
            case 'w':
                $length_units_2 = '2';
                $first_repeat = 60 * 60 * 24 * 7 * $length;
                break;
            case 'm':
                $length_units_2 = '3';
                $first_repeat = 60 * 60 * 24 * 31 * $length;
                break;
            case 'y':
                $length_units_2 = '4';
                $first_repeat = 60 * 60 * 24 * 365 * $length;
                break;
        }
        $digest_option = get_option('payment_gateway_digest');
        //$digest = md5((($digest_option == '') ? ($digest_option . ':') : '') . $trans_id . ':' . float_to_raw_string($amount) . ':' . $currency . $length_units_2 . strval($length));   Deprecated
        $digest = md5((($digest_option == '') ? ($digest_option . ':') : '') . ';' . 'cartId:amount:currency:intervalUnit:intervalMult;' . $trans_id . ';' . float_to_raw_string($amount) . ';' . $currency . $length_units_2 . strval($length));

        $GLOBALS['SITE_DB']->query_insert('trans_expecting', array(
            'id' => $trans_id,
            'e_type_code' => $type_code,
            'e_purchase_id' => $purchase_id,
            'e_item_name' => $item_name,
            'e_member_id' => get_member(),
            'e_amount' => float_to_raw_string($amount),
            'e_currency' => $currency,
            'e_ip_address' => get_ip_address(),
            'e_session_id' => get_session_id(),
            'e_time' => time(),
            'e_length' => null,
            'e_length_units' => '',
        ));

        return do_template('ECOM_SUBSCRIPTION_BUTTON_VIA_WORLDPAY', array(
            '_GUID' => '1f88716137762a467edbf5fbb980c6fe',
            'TYPE_CODE' => $type_code,
            'DIGEST' => $digest,
            'TEST' => ecommerce_test_mode(),
            'LENGTH' => strval($length),
            'LENGTH_UNITS_2' => $length_units_2,
            'ITEM_NAME' => $item_name,
            'PURCHASE_ID' => strval($trans_id),
            'AMOUNT' => float_to_raw_string($amount),
            'FIRST_REPEAT' => date('Y-m-d', $first_repeat),
            'CURRENCY' => $currency,
            'USERNAME' => $username,
            'IPN_URL' => $ipn_url,
        ));
    }

    /**
     * Make a subscription cancellation button.
     *
     * @param  ID_TEXT $purchase_id The purchase ID.
     * @return Tempcode The button.
     */
    public function make_cancel_button($purchase_id)
    {
        $cancel_url = build_url(array('page' => 'subscriptions', 'type' => 'cancel', 'id' => $purchase_id), get_module_zone('subscriptions'));
        return do_template('ECOM_SUBSCRIPTION_CANCEL_BUTTON_VIA_WORLDPAY', array('_GUID' => '187fba57424e7850b9e21fc147de48eb', 'CANCEL_URL' => $cancel_url, 'PURCHASE_ID' => $purchase_id));
    }

    /**
     * Handle IPN's. The function may produce output, which would be returned to the Payment Gateway. The function may do transaction verification.
     *
     * @return array A long tuple of collected data. Emulates some of the key variables of the PayPal IPN response.
     */
    public function handle_ipn_transaction()
    {
        // Test case...
        //$_POST = unserialize('a:36:{s:8:"testMode";s:3:"100";s:8:"authCost";s:4:"15.0";s:8:"currency";s:3:"GBP";s:7:"address";s:1:"a";s:13:"countryString";s:11:"South Korea";s:10:"callbackPW";s:10:"s35645dxr4";s:12:"installation";s:5:"84259";s:3:"fax";s:1:"a";s:12:"countryMatch";s:1:"B";s:7:"transId";s:9:"222873126";s:3:"AVS";s:4:"0000";s:12:"amountString";s:11:"&#163;15.00";s:8:"postcode";s:1:"a";s:7:"msgType";s:10:"authResult";s:4:"name";s:1:"a";s:3:"tel";s:1:"a";s:11:"transStatus";s:1:"Y";s:4:"desc";s:15:"Property Advert";s:8:"cardType";s:10:"Mastercard";s:4:"lang";s:2:"en";s:9:"transTime";s:13:"1171243476007";s:16:"authAmountString";s:11:"&#163;15.00";s:10:"authAmount";s:4:"15.0";s:9:"ipAddress";s:12:"84.9.162.135";s:4:"cost";s:4:"15.0";s:6:"instId";s:5:"84259";s:6:"amount";s:4:"15.0";s:8:"compName";s:32:"The Accessible Property Register";s:7:"country";s:2:"KR";s:11:"MC_callback";s:63:"www.kivi.co.uk/ClientFiles/APR/data/ecommerce.php?from=worldpay";s:14:"rawAuthMessage";s:22:"cardbe.msg.testSuccess";s:5:"email";s:16:"vaivak@gmail.com";s:12:"authCurrency";s:3:"GBP";s:11:"rawAuthCode";s:1:"A";s:6:"cartId";s:32:"3ecd645f632f0304067fb565e71b4dcd";s:8:"authMode";s:1:"A";}');
        //$_GET = unserialize('a:3:{s:4:"from";s:8:"worldpay";s:7:"msgType";s:10:"authResult";s:12:"installation";s:5:"84259";}');

        $code = post_param_string('transStatus');
        if ($code == 'C') {
            exit(); // Cancellation signal, won't process
        }

        $txn_id = post_param_string('transId');
        $cart_id = post_param_string('cartId');
        if (post_param_string('futurePayType', '') == 'regular') {
            $subscription = true;
        } else {
            $subscription = false;
        }

        $transaction_rows = $GLOBALS['SITE_DB']->query_select('trans_expecting', array('*'), array('id' => $cart_id), '', 1);
        if (!array_key_exists(0, $transaction_rows)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
        }
        $transaction_row = $transaction_rows[0];

        $member_id = $transaction_row['e_member_id'];
        $item_name = $subscription ? '' : $transaction_row['e_item_name'];
        $purchase_id = $transaction_row['e_purchase_id'];

        $success = ($code == 'Y');
        $message = post_param_string('rawAuthMessage');

        $payment_status = $success ? 'Completed' : 'Failed';
        $reason_code = '';
        $pending_reason = '';
        $memo = '';
        $mc_gross = post_param_string('authAmount');
        $mc_currency = post_param_string('authCurrency');
        $email = $GLOBALS['FORUM_DRIVER']->get_member_email_address($member_id);

        if (post_param_string('callbackPW') != get_option('payment_gateway_callback_password')) {
            fatal_ipn_exit(do_lang('IPN_UNVERIFIED'));
        }

        if ($success) {
            require_code('notifications');
            dispatch_notification('payment_received', null, do_lang('PAYMENT_RECEIVED_SUBJECT', $txn_id, null, null, get_lang($member_id)), do_notification_lang('PAYMENT_RECEIVED_BODY', float_format(floatval($mc_gross)), $mc_currency, get_site_name(), get_lang($member_id)), array($member_id), A_FROM_SYSTEM_PRIVILEGED);
        }

        if (addon_installed('shopping')) {
            if ($transaction_row['e_type_code'] == 'cart_orders') {
                $this->store_shipping_address(intval($purchase_id));
            }
        }

        return array($purchase_id, $item_name, $payment_status, $reason_code, $pending_reason, $memo, $mc_gross, $mc_currency, $txn_id, '', '');
    }

    /**
     * Show a payment response after IPN runs (for hooks that handle redirects in this way).
     *
     * @param  ID_TEXT $product Product.
     * @param  ID_TEXT $purchase_id Purchase ID.
     * @return string The response.
     */
    public function show_payment_response($product, $purchase_id)
    {
        $txn_id = post_param_string('transId');
        $message = do_lang('TRANSACTION_ID_WRITTEN', $txn_id);
        $url = build_url(array('page' => 'purchase', 'type' => 'finish', 'message' => $message, 'product' => $product, 'purchase_id' => $purchase_id, 'from' => 'worldpay'), get_module_zone('purchase'));
        return '<meta http-equiv="refresh" content="0;url=' . escape_html($url->evaluate()) . '" />';
    }

    /**
     * Store shipping address for orders.
     *
     * @param  AUTO_LINK $order_id Order ID
     * @return ?mixed Address ID (null: No address record found).
     */
    public function store_shipping_address($order_id)
    {
        if (is_null(post_param_string('first_name', null))) {
            return null;
        }

        if (is_null($GLOBALS['SITE_DB']->query_select_value_if_there('shopping_order_addresses', 'id', array('order_id' => $order_id)))) {
            $shipping_address = array(
                'order_id' => $order_id,
                'firstname' => post_param_string('delvName', ''),
                'lastname' => '',
                'street_address' => post_param_string('delvAddress', ''),
                'city' => post_param_string('city', ''),
                'county' => '',
                'state' => '',
                'post_code' => post_param_string('delvPostcode', ''),
                'country' => post_param_string('delvCountryString', ''),
                'email' => post_param_string('email', ''),
                'phone' => post_param_string('tel', ''),
            );
            return $GLOBALS['SITE_DB']->query_insert('shopping_order_addresses', $shipping_address, true);
        }

        return null;
    }

    /**
     * Find whether the hook auto-cancels (if it does, auto cancel the given subscription).
     *
     * @param  AUTO_LINK $subscription_id ID of the subscription to cancel.
     * @return ?boolean True: yes. False: no. (null: cancels via a user-URL-directioning)
     */
    public function auto_cancel($subscription_id)
    {
        return false;
    }
}
