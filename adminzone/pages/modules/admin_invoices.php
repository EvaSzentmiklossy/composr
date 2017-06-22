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
 * Module page class.
 */
class Module_admin_invoices
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = false;
        return $info;
    }

    /**
     * Find entry-points available within this module.
     *
     * @param  boolean $check_perms Whether to check permissions.
     * @param  ?MEMBER $member_id The member to check permissions as (null: current user).
     * @param  boolean $support_crosslinks Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name).
     * @param  boolean $be_deferential Whether to avoid any entry-point (or even return null to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "browse" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
     * @return ?array A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled).
     */
    public function get_entry_points($check_perms = true, $member_id = null, $support_crosslinks = true, $be_deferential = false)
    {
        if ($be_deferential || $support_crosslinks) {
            return null;
        }

        return array(
            'browse' => array('INVOICES', 'menu/adminzone/audit/ecommerce/invoices'),
            'outstanding' => array('OUTSTANDING_INVOICES', 'menu/adminzone/audit/ecommerce/outstanding_invoices'),
            'undelivered' => array('UNDELIVERED_INVOICES', 'menu/adminzone/audit/ecommerce/undelivered_invoices'),
            'add' => array('CREATE_INVOICE', 'menu/adminzone/audit/ecommerce/create_invoice'),
        );
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none).
     */
    public function pre_run()
    {
        require_code('form_templates'); // Needs to run high so that the anti-click-hacking header is sent

        $type = get_param_string('type', 'add');

        require_lang('ecommerce');

        set_helper_panel_tutorial('tut_ecommerce');

        if ($type == 'browse') {
            breadcrumb_set_self(do_lang_tempcode('INVOICES'));
            breadcrumb_set_parents(array(array('_SEARCH:admin_ecommerce_logs:browse', do_lang_tempcode('ECOMMERCE'))));
        }

        if ($type == 'add') {
            breadcrumb_set_parents(array(array('_SEARCH:admin_ecommerce_logs:browse', do_lang_tempcode('ECOMMERCE')), array('_SELF:_SELF:browse', do_lang_tempcode('INVOICES'))));

            $this->title = get_screen_title('CREATE_INVOICE');
        }

        if ($type == '_add') {
            breadcrumb_set_self(do_lang_tempcode('DONE'));
            breadcrumb_set_parents(array(array('_SEARCH:admin_ecommerce_logs:browse', do_lang_tempcode('ECOMMERCE')), array('_SELF:_SELF:browse', do_lang_tempcode('INVOICES')), array('_SELF:_SELF:add', do_lang_tempcode('CREATE_INVOICE'))));

            $this->title = get_screen_title('CREATE_INVOICE');
        }

        if ($type == 'outstanding') {
            breadcrumb_set_parents(array(array('_SEARCH:admin_ecommerce_logs:browse', do_lang_tempcode('ECOMMERCE')), array('_SELF:_SELF:browse', do_lang_tempcode('INVOICES'))));

            $this->title = get_screen_title('OUTSTANDING_INVOICES');
        }

        if ($type == 'undelivered') {
            breadcrumb_set_parents(array(array('_SEARCH:admin_ecommerce_logs:browse', do_lang_tempcode('ECOMMERCE')), array('_SELF:_SELF:browse', do_lang_tempcode('INVOICES'))));

            $this->title = get_screen_title('UNDELIVERED_INVOICES');
        }

        if ($type == 'delete') {
            breadcrumb_set_parents(array(array('_SEARCH:admin_ecommerce_logs:browse', do_lang_tempcode('ECOMMERCE')), array('_SELF:_SELF:browse', do_lang_tempcode('INVOICES')), array('_SELF:_SELF:undelivered', do_lang_tempcode('UNDELIVERED_INVOICES'))));
            if (post_param_integer('confirmed', 0) != 1) {
                breadcrumb_set_self(do_lang_tempcode('CONFIRM'));
            } else {
                breadcrumb_set_self(do_lang_tempcode('DONE'));
            }

            $this->title = get_screen_title('DELETE_INVOICE');
        }

        if ($type == 'deliver') {
            breadcrumb_set_self(do_lang_tempcode('DONE'));
            breadcrumb_set_parents(array(array('_SEARCH:admin_ecommerce_logs:browse', do_lang_tempcode('ECOMMERCE')), array('_SELF:_SELF:browse', do_lang_tempcode('INVOICES')), array('_SELF:_SELF:undelivered', do_lang_tempcode('UNDELIVERED_INVOICES'))));

            $this->title = get_screen_title('MARK_AS_DELIVERED');
        }

        return null;
    }

    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution.
     */
    public function run()
    {
        require_code('ecommerce');

        $type = get_param_string('type', 'add');

        if ($type == 'browse') {
            return $this->browse();
        }
        if ($type == 'add') {
            return $this->add();
        }
        if ($type == '_add') {
            return $this->_add();
        }
        if ($type == 'outstanding') {
            return $this->outstanding();
        }
        if ($type == 'undelivered') {
            return $this->undelivered();
        }
        if ($type == 'delete') {
            return $this->delete();
        }
        if ($type == 'deliver') {
            return $this->deliver();
        }
        return new Tempcode();
    }

    /**
     * The do-next manager for before invoice management.
     *
     * @return Tempcode The UI
     */
    public function browse()
    {
        require_code('templates_donext');
        return do_next_manager(
            get_screen_title('INVOICES'),
            comcode_lang_string('DOC_ECOMMERCE'),
            array(
                array('menu/_generic_admin/add_one', array('_SELF', array('type' => 'add'), '_SELF'), do_lang('CREATE_INVOICE')),
                array('menu/adminzone/audit/ecommerce/outstanding_invoices', array('_SELF', array('type' => 'outstanding'), '_SELF'), do_lang('OUTSTANDING_INVOICES')),
                array('menu/adminzone/audit/ecommerce/undelivered_invoices', array('_SELF', array('type' => 'undelivered'), '_SELF'), do_lang('UNDELIVERED_INVOICES')),
            ),
            do_lang('INVOICES')
        );
    }

    /**
     * UI to add an invoice.
     *
     * @return Tempcode The interface.
     */
    public function add()
    {
        $to = get_param_string('to', '');

        $products = find_all_products();
        $list = new Tempcode();
        foreach ($products as $type_code => $details) {
            if ($details[0] == PRODUCT_INVOICE) {
                $text = do_lang_tempcode('CUSTOM_PRODUCT_' . $type_code);
                if ($details[1] != '?') {
                    $text->attach(escape_html(' (' . $details[1] . ' ' . get_option('currency') . ')'));
                }
                $list->attach(form_input_list_entry($type_code, false, $text));
            }
        }
        if ($list->is_empty()) {
            inform_exit(do_lang_tempcode('NOTHING_TO_INVOICE_FOR'));
        }
        $fields = new Tempcode();
        $fields->attach(form_input_list(do_lang_tempcode('PRODUCT'), '', 'type_code', $list));
        $fields->attach(form_input_username(do_lang_tempcode('USERNAME'), do_lang_tempcode('DESCRIPTION_INVOICE_FOR'), 'to', $to, true));
        $fields->attach(form_input_float(do_lang_tempcode('AMOUNT'), do_lang_tempcode('INVOICE_AMOUNT_TEXT', escape_html(get_option('currency'))), 'amount', null, false));
        $fields->attach(form_input_line(do_lang_tempcode('INVOICE_SPECIAL'), do_lang_tempcode('DESCRIPTION_INVOICE_SPECIAL'), 'special', '', false));
        $fields->attach(form_input_text(do_lang_tempcode('NOTE'), do_lang_tempcode('DESCRIPTION_INVOICE_NOTE'), 'note', '', false));

        $post_url = build_url(array('page' => '_SELF', 'type' => '_add'), '_SELF');
        $submit_name = do_lang_tempcode('CREATE_INVOICE');

        return do_template('FORM_SCREEN', array('_GUID' => 'b8a08145bd1262c277e00a1151d6383e', 'HIDDEN' => '', 'TITLE' => $this->title, 'URL' => $post_url, 'FIELDS' => $fields, 'SUBMIT_ICON' => 'buttons__proceed', 'SUBMIT_NAME' => $submit_name, 'TEXT' => do_lang_tempcode('DESCRIPTION_INVOICE_PAGE'), 'SUPPORT_AUTOSAVE' => true));
    }

    /**
     * Actualiser to add an invoice.
     *
     * @return Tempcode The interface.
     */
    public function _add()
    {
        $type_code = post_param_string('type_code');
        $object = find_product($type_code);

        $amount = post_param_string('amount', '');
        if ($amount == '') {
            $products = $object->get_products(false, $type_code);
            $amount = $products[$type_code][1];
            if ($amount == '?') {
                warn_exit(do_lang_tempcode('INVOICE_REQUIRED_AMOUNT'));
            }
        }

        $to = post_param_string('to');
        $member_id = $GLOBALS['FORUM_DRIVER']->get_member_from_username($to);
        if ($member_id === null) {
            warn_exit(do_lang_tempcode('_MEMBER_NO_EXIST', $to));
        }

        $id = $GLOBALS['SITE_DB']->query_insert('invoices', array(
            'i_type_code' => $type_code,
            'i_member_id' => $member_id,
            'i_state' => 'new',
            'i_amount' => $amount,
            'i_special' => post_param_string('special'),
            'i_time' => time(),
            'i_note' => post_param_string('note')
        ), true);

        log_it('CREATE_INVOICE', strval($id), $type_code);

        send_invoice_notification($member_id, $id);

        $url = build_url(array('page' => '_SELF', 'type' => 'outstanding'), '_SELF');
        return redirect_screen($this->title, $url, do_lang_tempcode('SUCCESS'));
    }

    /**
     * Show outstanding invoices.
     *
     * @return Tempcode The interface.
     */
    public function outstanding()
    {
        $invoices = array();
        $rows = $GLOBALS['SITE_DB']->query_select('invoices', array('*'), array('i_state' => 'new'), 'ORDER BY i_time');
        foreach ($rows as $row) {
            $invoice_title = do_lang('CUSTOM_PRODUCT_' . $row['i_type_code']);
            $date = get_timezoned_date_time($row['i_time']);
            $username = $GLOBALS['FORUM_DRIVER']->get_username($row['i_member_id']);
            $profile_url = $GLOBALS['FORUM_DRIVER']->member_profile_url($row['i_member_id'], true);
            $invoices[] = array('INVOICE_TITLE' => $invoice_title, 'PROFILE_URL' => $profile_url, 'USERNAME' => $username, 'ID' => strval($row['id']), 'STATE' => $row['i_state'], 'AMOUNT' => float_format($row['i_amount']), 'DATE' => $date, 'NOTE' => $row['i_note'], 'TYPE_CODE' => $row['i_type_code']);
        }
        if (count($invoices) == 0) {
            inform_exit(do_lang_tempcode('NO_ENTRIES'));
        }

        return do_template('ECOM_OUTSTANDING_INVOICES_SCREEN', array('_GUID' => 'fab0fa7dbcd9d6484fa1861ce170717a', 'TITLE' => $this->title, 'FROM' => 'outstanding', 'INVOICES' => $invoices));
    }

    /**
     * Show undelivered invoices.
     *
     * @return Tempcode The interface.
     */
    public function undelivered()
    {
        $invoices = array();
        $rows = $GLOBALS['SITE_DB']->query_select('invoices', array('*'), array('i_state' => 'paid'), 'ORDER BY i_time');
        foreach ($rows as $row) {
            $invoice_title = do_lang('CUSTOM_PRODUCT_' . $row['i_type_code']);
            $date = get_timezoned_date_time($row['i_time']);
            $username = $GLOBALS['FORUM_DRIVER']->get_username($row['i_member_id']);
            $profile_url = $GLOBALS['FORUM_DRIVER']->member_profile_url($row['i_member_id'], true);
            $invoices[] = array('INVOICE_TITLE' => $invoice_title, 'PROFILE_URL' => $profile_url, 'USERNAME' => $username, 'ID' => strval($row['id']), 'STATE' => $row['i_state'], 'AMOUNT' => float_format($row['i_amount']), 'DATE' => $date, 'NOTE' => $row['i_note'], 'TYPE_CODE' => $row['i_type_code']);
        }
        if (count($invoices) == 0) {
            inform_exit(do_lang_tempcode('NO_ENTRIES'));
        }

        return do_template('ECOM_OUTSTANDING_INVOICES_SCREEN', array('_GUID' => '672e41d8cbe06f046a47762ff75c8337', 'TITLE' => $this->title, 'FROM' => 'undelivered', 'INVOICES' => $invoices));
    }

    /**
     * Actualiser to delete an invoice.
     *
     * @return Tempcode The result.
     */
    public function delete()
    {
        if (post_param_integer('confirmed', 0) != 1) {
            $url = get_self_url();
            $text = do_lang_tempcode('DELETE_INVOICE');

            $hidden = build_keep_post_fields();
            $hidden->attach(form_input_hidden('confirmed', '1'));
            $hidden->attach(form_input_hidden('from', get_param_string('from', 'browse')));

            return do_template('CONFIRM_SCREEN', array('_GUID' => '45707062c00588c33726b256e8f9ba40', 'TITLE' => $this->title, 'FIELDS' => $hidden, 'PREVIEW' => $text, 'URL' => $url));
        }

        $GLOBALS['SITE_DB']->query_delete('invoices', array('id' => get_param_integer('id')), '', 1);

        $url = build_url(array('page' => '_SELF', 'type' => post_param_string('from', 'browse')), '_SELF');
        return redirect_screen($this->title, $url, do_lang_tempcode('SUCCESS'));
    }

    /**
     * Actualiser to deliver an invoice.
     *
     * @return Tempcode The result.
     */
    public function deliver()
    {
        $GLOBALS['SITE_DB']->query_update('invoices', array('i_state' => 'delivered'), array('id' => get_param_integer('id')), '', 1);

        $url = build_url(array('page' => '_SELF', 'type' => 'undelivered'), '_SELF');
        return redirect_screen($this->title, $url, do_lang_tempcode('SUCCESS'));
    }
}
