<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licensing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    tickets
 */

/**
 * Module page class.
 */
class Module_admin_tickets
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled)
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
     * @param  boolean $check_perms Whether to check permissions
     * @param  ?MEMBER $member_id The member to check permissions as (null: current user)
     * @param  boolean $support_crosslinks Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name)
     * @param  boolean $be_deferential Whether to avoid any entry-point (or even return null to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "browse" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
     * @return ?array A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled)
     */
    public function get_entry_points($check_perms = true, $member_id = null, $support_crosslinks = true, $be_deferential = false)
    {
        if (!addon_installed('tickets')) {
            return null;
        }

        if (get_forum_type() == 'none') {
            return null;
        }

        return array(
            'browse' => array('MANAGE_TICKET_TYPES', 'menu/site_meta/tickets'),
        );
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none)
     */
    public function pre_run()
    {
        $error_msg = new Tempcode();
        if (!addon_installed__autoinstall('tickets', $error_msg)) {
            return $error_msg;
        }

        $type = get_param_string('type', 'browse');

        require_lang('tickets');

        set_helper_panel_tutorial('tut_support_desk');

        if ($type != 'browse') {
            breadcrumb_set_parents(array(array('_SELF:_SELF:browse', do_lang_tempcode('MANAGE_TICKET_TYPES'))));
        }

        if ($type == 'browse') {
            $this->title = get_screen_title('MANAGE_TICKET_TYPES');
        }

        if ($type == 'add') {
            $this->title = get_screen_title('ADD_TICKET_TYPE');
        }

        if ($type == 'edit') {
            $this->title = get_screen_title('EDIT_TICKET_TYPE');
        }

        if ($type == '_edit') {
            if (post_param_integer('delete', 0) == 1) {
                $this->title = get_screen_title('DELETE_TICKET_TYPE');
            } else {
                $this->title = get_screen_title('EDIT_TICKET_TYPE');
            }
        }

        return null;
    }

    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution
     */
    public function run()
    {
        require_css('tickets');

        require_code('tickets');
        require_code('tickets2');

        $type = get_param_string('type', 'browse');

        if ($type == 'browse') {
            return $this->ticket_type_interface();
        }
        if ($type == 'add') {
            return $this->add_ticket_type();
        }
        if ($type == 'edit') {
            return $this->edit_ticket_type();
        }
        if ($type == '_edit') {
            return $this->_edit_ticket_type();
        }

        return new Tempcode();
    }

    /**
     * The UI to choose a ticket type to edit, or to add a ticket.
     *
     * @return Tempcode The UI
     */
    public function ticket_type_interface()
    {
        require_lang('permissions');

        $list = new Tempcode();
        require_code('form_templates');
        $ticket_types = collapse_2d_complexity('id', 'ticket_type_name', $GLOBALS['SITE_DB']->query_select('ticket_types', array('*'), array(), 'ORDER BY ' . $GLOBALS['SITE_DB']->translate_field_ref('ticket_type_name')));
        foreach ($ticket_types as $ticket_type_id => $ticket_type_name) {
            $list->attach(form_input_list_entry(strval($ticket_type_id), false, get_translated_text($ticket_type_name)));
        }
        if (!$list->is_empty()) {
            $edit_url = build_url(array('page' => '_SELF', 'type' => 'edit'), '_SELF', array(), false, true);
            $submit_name = do_lang_tempcode('EDIT');
            $fields = form_input_huge_list(do_lang_tempcode('TITLE'), do_lang_tempcode('DESCRIPTION_TICKET_TYPE'), 'ticket_type_id', $list);

            $tpl = do_template('FORM', array(
                '_GUID' => '2d2e76f5cfc397a78688db72170918d4',
                'TABINDEX' => strval(get_form_field_tabindex()),
                'GET' => true,
                'HIDDEN' => '',
                'TEXT' => '',
                'FIELDS' => $fields,
                'URL' => $edit_url,
                'SUBMIT_ICON' => 'admin--edit-this-category',
                'SUBMIT_NAME' => $submit_name,
            ));
        } else {
            $tpl = new Tempcode();
        }

        // Do a form so people can add...

        $post_url = build_url(array('page' => '_SELF', 'type' => 'add'), '_SELF');

        $submit_name = do_lang_tempcode('ADD_TICKET_TYPE');

        $fields = form_input_line(do_lang_tempcode('TITLE'), do_lang_tempcode('DESCRIPTION_TICKET_TYPE'), 'ticket_type_name_2', '', false);
        $fields->attach(form_input_tick(do_lang_tempcode('TICKET_GUEST_EMAILS_MANDATORY'), do_lang_tempcode('DESCRIPTION_TICKET_GUEST_EMAILS_MANDATORY'), 'guest_emails_mandatory', false));
        $fields->attach(form_input_tick(do_lang_tempcode('TICKET_SEARCH_FAQ'), do_lang_tempcode('DESCRIPTION_TICKET_SEARCH_FAQ'), 'search_faq', false));

        // Permissions
        $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => '87ef39b0a5c3c45c1c1319c7f85d0e2a', 'TITLE' => do_lang_tempcode('PERMISSIONS'), 'SECTION_HIDDEN' => true)));
        $admin_groups = $GLOBALS['FORUM_DRIVER']->get_super_admin_groups();
        $groups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list(false, true);
        foreach ($groups as $id => $group_name) {
            if (in_array($id, $admin_groups)) {
                continue;
            }
            $fields->attach(form_input_tick(do_lang_tempcode('ACCESS_FOR', escape_html($group_name)), do_lang_tempcode('DESCRIPTION_ACCESS_FOR', escape_html($group_name)), 'access_' . strval($id), true));
        }
        if (addon_installed('ecommerce')) {
            require_code('ecommerce_permission_products');
            $fields->attach(permission_product_form('ticket_type'));
        }

        $add_form = do_template('FORM', array(
            '_GUID' => '382f6fab6c563d81303ecb26495e76ec',
            'TABINDEX' => strval(get_form_field_tabindex()),
            'SECONDARY_FORM' => true,
            'HIDDEN' => '',
            'TEXT' => '',
            'FIELDS' => $fields,
            'SUBMIT_ICON' => 'admin--add-one-category',
            'SUBMIT_NAME' => $submit_name,
            'URL' => $post_url,
            'SUPPORT_AUTOSAVE' => true,
        ));

        return do_template('SUPPORT_TICKET_TYPE_SCREEN', array('_GUID' => '28645dc4a86086fa865ec7e166b84bb6', 'TITLE' => $this->title, 'TPL' => $tpl, 'ADD_FORM' => $add_form));
    }

    /**
     * The actualiser to add a ticket type.
     *
     * @return Tempcode The UI
     */
    public function add_ticket_type()
    {
        $ticket_type_name = post_param_string('ticket_type_name', post_param_string('ticket_type_name_2'));
        $ticket_type_id = add_ticket_type($ticket_type_name, post_param_integer('guest_emails_mandatory', 0), post_param_integer('search_faq', 0));

        // Permissions
        require_code('permissions2');
        set_category_permissions_from_environment('tickets', strval($ticket_type_id));
        if (addon_installed('ecommerce')) {
            require_code('ecommerce_permission_products');
            permission_product_save('ticket_type', strval($ticket_type_id));
        }

        // Show it worked / Refresh
        $url = build_url(array('page' => '_SELF', 'type' => 'browse'), '_SELF');
        return redirect_screen($this->title, $url, do_lang_tempcode('SUCCESS'));
    }

    /**
     * The UI to edit a ticket.
     *
     * @return Tempcode The UI
     */
    public function edit_ticket_type()
    {
        require_code('form_templates');
        require_code('permissions2');

        $ticket_type_id = get_param_integer('ticket_type_id');
        $details = get_ticket_type($ticket_type_id);
        $ticket_type_name = get_translated_text($details['ticket_type_name']);

        $post_url = build_url(array('page' => '_SELF', 'type' => '_edit', 'ticket_type_id' => $ticket_type_id), '_SELF');

        $submit_name = do_lang_tempcode('SAVE');

        $fields = new Tempcode();

        $fields->attach(form_input_line(do_lang_tempcode('TYPE'), do_lang_tempcode('DESCRIPTION_TICKET_TYPE'), 'ticket_type_name', $ticket_type_name, false));
        $fields->attach(form_input_tick(do_lang_tempcode('TICKET_GUEST_EMAILS_MANDATORY'), do_lang_tempcode('DESCRIPTION_TICKET_GUEST_EMAILS_MANDATORY'), 'guest_emails_mandatory', $details['guest_emails_mandatory']));
        $fields->attach(form_input_tick(do_lang_tempcode('TICKET_SEARCH_FAQ'), do_lang_tempcode('DESCRIPTION_TICKET_SEARCH_FAQ'), 'search_faq', $details['search_faq']));

        // Permissions
        $fields->attach(get_category_permissions_for_environment('tickets', strval($ticket_type_id)));
        if (addon_installed('ecommerce')) {
            require_code('ecommerce_permission_products');
            $fields->attach(permission_product_form('ticket_type', ($ticket_type_id === null) ? null : strval($ticket_type_id)));
        }

        $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => '09e6f1d2276ee679f280b33a79bff089', 'TITLE' => do_lang_tempcode('ACTIONS'))));
        $fields->attach(form_input_tick(do_lang_tempcode('DELETE'), do_lang_tempcode('DESCRIPTION_DELETE'), 'delete', false));

        return do_template('FORM_SCREEN', array(
            '_GUID' => '0a505a779c1639fd2d3ee10c24a7905a',
            'SKIP_WEBSTANDARDS' => true,
            'TITLE' => $this->title,
            'HIDDEN' => '',
            'TEXT' => '',
            'FIELDS' => $fields,
            'SUBMIT_ICON' => 'admin--edit-this',
            'SUBMIT_NAME' => $submit_name,
            'URL' => $post_url,
            'SUPPORT_AUTOSAVE' => true,
        ));
    }

    /**
     * The actualiser to edit/delete a ticket type.
     *
     * @return Tempcode The UI
     */
    public function _edit_ticket_type()
    {
        $ticket_type_id = get_param_integer('ticket_type_id');

        if (post_param_integer('delete', 0) == 1) {
            delete_ticket_type($ticket_type_id);
        } else {
            edit_ticket_type($ticket_type_id, post_param_string('ticket_type_name'), post_param_integer('guest_emails_mandatory', 0), post_param_integer('search_faq', 0));

            $GLOBALS['SITE_DB']->query_delete('group_category_access', array('module_the_name' => 'tickets', 'category_name' => strval($ticket_type_id)), '', 1);
            require_code('permissions2');
            set_category_permissions_from_environment('tickets', strval($ticket_type_id));
            if (addon_installed('ecommerce')) {
                require_code('ecommerce_permission_products');
                permission_product_save('ticket_type', strval($ticket_type_id));
            }
        }

        // Show it worked / Refresh
        $url = build_url(array('page' => '_SELF', 'type' => 'browse'), '_SELF');
        return redirect_screen($this->title, $url, do_lang_tempcode('SUCCESS'));
    }
}
