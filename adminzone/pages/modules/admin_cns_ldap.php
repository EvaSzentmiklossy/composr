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
 * @package    ldap
 */

/**
 * Module page class.
 */
class Module_admin_cns_ldap
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
        $info['version'] = 4;
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
        if (!addon_installed('ldap')) {
            return null;
        }

        if (get_forum_type() != 'cns') {
            return null;
        }

        return array(
            'browse' => array('LDAP_SYNC', 'menu/adminzone/security/ldap'),
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
        if (!addon_installed__autoinstall('ldap', $error_msg)) {
            return $error_msg;
        }

        $type = get_param_string('type', 'browse');

        require_lang('cns');
        require_css('cns_admin');

        set_helper_panel_tutorial('tut_ldap');

        if ($type == 'actual') {
            breadcrumb_set_parents(array(array('_SELF:_SELF:browse', do_lang_tempcode('LDAP_SYNC'))));
            breadcrumb_set_self(do_lang_tempcode('DONE'));
        }

        $this->title = get_screen_title('LDAP_SYNC');

        return null;
    }

    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution
     */
    public function run()
    {
        if (get_forum_type() != 'cns') {
            warn_exit(do_lang_tempcode('NO_CNS'));
        } else {
            cns_require_all_forum_stuff();
        }
        require_code('cns_groups_action');
        require_code('cns_groups_action2');

        global $LDAP_CONNECTION;
        if (($LDAP_CONNECTION === null) && !$GLOBALS['DEV_MODE']) {
            warn_exit(do_lang_tempcode('LDAP_DISABLED'));
        }

        require_code('cns_ldap');

        // Decide what we're doing
        $type = get_param_string('type', 'browse');

        if ($type == 'browse') {
            return $this->gui();
        }
        if ($type == 'actual') {
            return $this->actual();
        }
        return new Tempcode();
    }

    /**
     * The UI for LDAP synchronisation.
     *
     * @return Tempcode The UI
     */
    public function gui()
    {
        $groups_add = new Tempcode();
        $groups_delete = new Tempcode();
        $members_delete = new Tempcode();

        $all_ldap_groups = cns_get_all_ldap_groups();
        if ($GLOBALS['DEV_MODE'] && count($all_ldap_groups) == 0) {
            $all_ldap_groups[] = 'Example LDAP group';
        }
        foreach ($all_ldap_groups as $group) {
            if (cns_group_ldapcn_to_cnsid($group) === null) {
                $_group = str_replace(' ', '_space_', $group);
                $tpl = do_template('CNS_LDAP_LIST_ENTRY', array('_GUID' => '99aa6dd1a7a4caafd0199f8b5512cf29', 'NAME' => 'add_group_' . $_group, 'NICE_NAME' => $group));
                $groups_add->attach($tpl);
            }
        }
        $all_cms_groups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list();
        foreach ($all_cms_groups as $id => $group) {
            if ((!in_array($group, $all_ldap_groups)) && ($id != db_get_first_id() + 0) && ($id != db_get_first_id() + 1) && ($id != db_get_first_id() + 8)) {
                $tpl = do_template('CNS_LDAP_LIST_ENTRY', array('_GUID' => '48de4d176157941a0ce7caa7a1c395fb', 'NAME' => 'delete_group_' . strval($id), 'NICE_NAME' => $group));
                $groups_delete->attach($tpl);
            }
        }

        if (php_function_allowed('set_time_limit')) {
            @set_time_limit(0);
        }
        send_http_output_ping();

        $start = 0;
        do {
            $all_ldap_members = $GLOBALS['FORUM_DB']->query_select('f_members', array('id', 'm_username'), array('m_password_compat_scheme' => 'ldap'), '', 400, $start);
            foreach ($all_ldap_members as $row) {
                $id = $row['id'];
                $username = $row['m_username'];

                if (!cns_is_ldap_member_potential($username)) {
                    $tpl = do_template('CNS_LDAP_LIST_ENTRY', array('_GUID' => '572c0f1e87a2dbe6cdf31d97fd71d3a4', 'NAME' => 'delete_member_' . strval($id), 'NICE_NAME' => $username));
                    $members_delete->attach($tpl);
                }
            }
            $start += 400;
        } while (array_key_exists(0, $all_ldap_members));

        $post_url = build_url(array('page' => '_SELF', 'type' => 'actual'), '_SELF');

        return do_template('CNS_LDAP_SYNC_SCREEN', array(
            '_GUID' => '38c608ce56cf3dbafb1dd1446c65d592',
            'URL' => $post_url,
            'TITLE' => $this->title,
            'MEMBERS_DELETE' => $members_delete,
            'GROUPS_DELETE' => $groups_delete,
            'GROUPS_ADD' => $groups_add,
        ));
    }

    /**
     * The actualiser for LDAP synchronisation.
     *
     * @return Tempcode The UI
     */
    public function actual()
    {
        $all_ldap_groups = cns_get_all_ldap_groups();
        foreach ($all_ldap_groups as $group) {
            if (post_param_integer('add_group_' . str_replace(' ', '_space_', $group), 0) == 1) {
                cns_make_group($group, 0, 0, 0, '');
            }
        }
        $all_cms_groups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list();
        foreach ($all_cms_groups as $id => $group) {
            if (post_param_integer('delete_group_' . strval($id), 0) == 1) {
                cns_delete_group($id);
            }
        }

        $all_ldap_members = $GLOBALS['FORUM_DB']->query_select('f_members', array('id'), array('m_password_compat_scheme' => 'ldap'));
        require_code('cns_groups_action');
        require_code('cns_groups_action2');
        require_code('cns_members_action2');
        foreach ($all_ldap_members as $row) {
            $id = $row['id'];

            if (post_param_integer('delete_member_' . strval($id), 0) == 1) {
                cns_delete_member($id);
            }
        }

        $url = build_url(array('page' => '_SELF', 'type' => 'browse'), '_SELF');
        return redirect_screen($this->title, $url, do_lang_tempcode('SUCCESS'));
    }
}
