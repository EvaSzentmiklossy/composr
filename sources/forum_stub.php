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
 * @package    core
 */

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__forum_stub()
{
    if (!defined('USERNAME_GUEST_AS_DEFAULT')) {
        define('USERNAME_GUEST_AS_DEFAULT', 1);
        define('USERNAME_DEFAULT_DELETED', 2);
        define('USERNAME_DEFAULT_NULL', 4);
        define('USERNAME_DEFAULT_ID_RAW', 8);
        define('USERNAME_DEFAULT_ID_TIDY', 16);
        define('USERNAME_DEFAULT_BLANK', 32);
        define('USERNAME_DEFAULT_ERROR', 64);
    }
}

/**
 * Forum Driver base class.
 *
 * @package    core
 */
class Forum_driver_base
{
    public $db;

    public $MEMBER_ROWS_CACHED = array();

    public $EMOTICON_CACHE = null;

     /**
      * Run whatever initialisation code we need to run. Not used within minikernel (i.e. installer).
      */
     public function forum_layer_initialise()
     {
     }

    /**
     * Delete the specified custom field from the forum.
     *
     * @param  string $name The name of the new custom field
     */
    public function install_delete_custom_field($name)
    {
        if (method_exists($this, '_install_delete_custom_field')) {
            $this->_install_delete_custom_field($name);
        }
    }

    /**
     * Find the usergroup ID of the forum guest member.
     *
     * @return GROUP The usergroup ID of the forum guest member
     */
    public function get_guest_group()
    {
        return db_get_first_id();
    }

    /**
     * Get a URL to a forum member's member profile.
     *
     * @param  MEMBER $id The forum member
     * @param  boolean $tempcode_okay Whether it is okay to return the result using Tempcode (more efficient, and allows keep_* parameters to propagate which you almost certainly want!)
     * @return mixed The URL
     */
    public function member_profile_url($id, $tempcode_okay = false)
    {
        $url = $this->_member_profile_url($id, $tempcode_okay);
        if (($tempcode_okay) && (!is_object($url))) {
            $url = make_string_tempcode($url);
        }
        if ((get_forum_type() != 'none') && (get_forum_type() != 'cns') && (get_option('forum_in_portal') == '1')) {
            $url = build_url(array('page' => 'forums', 'url' => protect_url_parameter($url)), get_module_zone('forums'));
            if (!$tempcode_okay) {
                $url = $url->evaluate();
            }
        }
        return $url;
    }

    /**
     * Get a hyperlink (i.e. HTML link, not just a URL) to a forum member's member profile.
     *
     * @param  MEMBER $id The forum member
     * @param  string $_username The username (blank: look it up)
     * @param  boolean $use_displayname Whether to use the displayname rather than the username (if we have them)
     * @return Tempcode The hyperlink
     */
    public function member_profile_hyperlink($id, $_username = '', $use_displayname = true)
    {
        if (is_guest($id)) {
            return ($_username == '') ? make_string_tempcode($this->get_username($this->get_guest_id())) : make_string_tempcode(escape_html($_username));
        }
        if ($_username == '') {
            $_username = $this->get_username($id, $use_displayname);
        }
        $url = $this->member_profile_url($id, true);
        return hyperlink($url, $_username, false, true);
    }

    /**
     * Get a URL to a forum join page.
     *
     * @param  boolean $tempcode_okay Whether it is okay to return the result using Tempcode (more efficient, and allows keep_* parameters to propagate which you almost certainly want!)
     * @return mixed The URL
     */
    public function join_url($tempcode_okay = false)
    {
        $url = $this->_join_url($tempcode_okay);
        if ((get_forum_type() != 'none') && (get_forum_type() != 'cns') && (get_option('forum_in_portal') == '1')) {
            $url = build_url(array('page' => 'forums', 'url' => protect_url_parameter($url)), get_module_zone('forums'));
            if (!$tempcode_okay) {
                $url = $url->evaluate();
            }
        }
        return $url;
    }

    /**
     * Get a URL to a forum 'user online' list.
     *
     * @param  boolean $tempcode_okay Whether it is okay to return the result using Tempcode (more efficient)
     * @return mixed The URL
     */
    public function users_online_url($tempcode_okay = false)
    {
        $url = $this->_users_online_url($tempcode_okay);
        if ((get_forum_type() != 'none') && (get_forum_type() != 'cns') && (get_option('forum_in_portal') == '1')) {
            $url = build_url(array('page' => 'forums', 'url' => protect_url_parameter($url)), get_module_zone('forums'));
        }
        return $url;
    }

    /**
     * Get a URL to send a forum member a PM.
     *
     * @param  MEMBER $id The forum member
     * @param  boolean $tempcode_okay Whether it is okay to return the result using Tempcode (more efficient)
     * @return mixed The URL
     */
    public function member_pm_url($id, $tempcode_okay = false)
    {
        $url = $this->_member_pm_url($id, $tempcode_okay);
        if ((get_forum_type() != 'none') && (get_forum_type() != 'cns') && (get_option('forum_in_portal') == '1')) {
            $url = build_url(array('page' => 'forums', 'url' => protect_url_parameter($url)), get_module_zone('forums'));
        }
        return $url;
    }

    /**
     * Get a URL to a forum.
     *
     * @param  integer $id The ID of the forum
     * @param  boolean $tempcode_okay Whether it is okay to return the result using Tempcode (more efficient)
     * @return mixed The URL
     */
    public function forum_url($id, $tempcode_okay = false)
    {
        $url = $this->_forum_url($id, $tempcode_okay);
        if ((get_forum_type() != 'none') && (get_forum_type() != 'cns') && (get_option('forum_in_portal') == '1')) {
            $url = build_url(array('page' => 'forums', 'url' => protect_url_parameter($url)), get_module_zone('forums'));
        }
        return $url;
    }

    /**
     * Get a member's username.
     *
     * @param  MEMBER $id The member
     * @param  boolean $use_displayname Whether to use the displayname rather than the username (if we have them)
     * @param  integer $options A bitmask of USERNAME_* options to define how to handle missing members
     * @return ?SHORT_TEXT The username (null: deleted/missing member)
     */
    public function get_username($id, $use_displayname = false, $options = USERNAME_DEFAULT_DELETED)
    {
        $guest_id = $this->get_guest_id();

        if (($id == $guest_id) && (($options & USERNAME_GUEST_AS_DEFAULT) != 0)) {
            $ret = null;
        } else {
            // Special case: Guest
            if ($id == $guest_id) {
                if (!function_exists('do_lang')) {
                    return 'Guest';
                }
            }

            // Special case: Cache
            global $USER_NAME_CACHE;
            if (isset($USER_NAME_CACHE[$id])) {
                $ret = $USER_NAME_CACHE[$id];
                if ($use_displayname) {
                    $ret = get_displayname($ret);
                }
                return $ret;
            }

            // Lookup
            $ret = $this->_get_username($id);

            // Clean data
            if ($ret === '') {
                $ret = null; // Odd, but sometimes
            }

            // Cache
            $USER_NAME_CACHE[$id] = $ret;
        }

        // Make a display name?
        if ($ret !== null) {
            if (($use_displayname) && (function_exists('get_displayname'))) {
                $ret = get_displayname($ret);
            }
        }

        // How to handle missing members
        if ($ret === null) {
            if (($options & USERNAME_DEFAULT_DELETED) != 0) {
                $ret = do_lang('DELETED');
            } elseif (($options & USERNAME_DEFAULT_ID_RAW) != 0) {
                $ret = strval($id);
            } elseif (($options & USERNAME_DEFAULT_ID_TIDY) != 0) {
                $ret = '#' . strval($id);
            } elseif (($options & USERNAME_DEFAULT_BLANK) != 0) {
                $ret = '';
            } elseif (($options & USERNAME_DEFAULT_ERROR) != 0) {
                warn_exit(do_lang_tempcode('_MEMBER_NO_EXIST', escape_html(strval($id))));
            }
        }

        return $ret;
    }

    /**
     * Get the display name of a username.
     * If no display name generator is configured, this will be the same as the username.
     *
     * @param  ID_TEXT $username The username
     * @return SHORT_TEXT The display name
     */
    public function get_displayname($username)
    {
        if (!method_exists($this, '_get_displayname')) {
            return $username;
        }
        return $this->_get_displayname($username);
    }

    /**
     * Get a member's e-mail address.
     *
     * @param  MEMBER $id The member
     * @return SHORT_TEXT The e-mail address (blank: not known)
     */
    public function get_member_email_address($id)
    {
        static $member_email_cache = array();
        if (array_key_exists($id, $member_email_cache)) {
            return $member_email_cache[$id];
        }

        $ret = $this->_get_member_email_address($id);
        $member_email_cache[$id] = $ret;
        return $ret;
    }

    /**
     * Find whether a member is staff.
     *
     * @param  MEMBER $id The member
     * @return boolean The answer
     */
    public function is_staff($id)
    {
        if (is_guest($id)) {
            return false;
        }

        static $is_staff_cache = array();
        if (isset($is_staff_cache[$id])) {
            return $is_staff_cache[$id];
        }

        $is_staff_cache[$id] = $this->_is_staff($id);
        return $is_staff_cache[$id];
    }

    /**
     * Find whether a member is a super administrator.
     *
     * @param  MEMBER $id The member
     * @return boolean The answer
     */
    public function is_super_admin($id)
    {
        static $is_super_admin_cache = array();
        if (isset($is_super_admin_cache[$id])) {
            return $is_super_admin_cache[$id];
        }

        if (is_guest($id)) {
            $is_super_admin_cache[$id] = false;
            return false;
        }

        $ret = $this->_is_super_admin($id);
        $is_super_admin_cache[$id] = $ret;
        return $ret;
    }

    /**
     * Get a list of the super admin usergroups.
     *
     * @return array The list of usergroups
     */
    public function get_super_admin_groups()
    {
        static $admin_group_cache = null;
        if ($admin_group_cache !== null) {
            return $admin_group_cache;
        }

        $ret = $this->_get_super_admin_groups();
        $admin_group_cache = $ret;
        return $ret;
    }

    /**
     * Get a list of the moderator usergroups.
     *
     * @return array The list of usergroups
     */
    public function get_moderator_groups()
    {
        static $moderator_group_cache = null;
        if ($moderator_group_cache !== null) {
            return $moderator_group_cache;
        }

        $ret = $this->_get_moderator_groups();
        $moderator_group_cache = $ret;
        return $ret;
    }

    /**
     * Get a map of forum usergroups (id=>name).
     *
     * @param  boolean $hide_hidden Whether to obscure the name of hidden usergroups
     * @param  boolean $only_permissive Whether to only grab permissive usergroups
     * @param  boolean $force_show_all Do not limit things even if there are huge numbers of usergroups
     * @param  array $force_find Usergroups that must be included in the results
     * @param  ?MEMBER $for_member Always return usergroups of this member (null: current member)
     * @param  boolean $skip_hidden Whether to completely skip hidden usergroups
     * @return array The map
     */
    public function get_usergroup_list($hide_hidden = false, $only_permissive = false, $force_show_all = false, $force_find = array(), $for_member = null, $skip_hidden = false)
    {
        static $usergroup_list_cache = null;
        if (($usergroup_list_cache !== null) && (isset($usergroup_list_cache[$hide_hidden][$only_permissive][$force_show_all][serialize($force_find)][$for_member][$skip_hidden]))) {
            return $usergroup_list_cache[$hide_hidden][$only_permissive][$force_show_all][serialize($force_find)][$for_member][$skip_hidden];
        }

        $ret = $this->_get_usergroup_list($hide_hidden, $only_permissive, $force_show_all, $force_find, $for_member, $skip_hidden);
        if ($usergroup_list_cache === null) {
            $usergroup_list_cache = array();
        }
        $usergroup_list_cache[$hide_hidden][$only_permissive][$force_show_all][serialize($force_find)][$for_member][$skip_hidden] = $ret;
        return $ret;
    }

    /**
     * Get a list of usergroups a member is in.
     *
     * @param  MEMBER $id The member
     * @param  boolean $skip_secret Whether to skip looking at secret usergroups, unless we have access
     * @param  boolean $handle_probation Whether to take probation into account
     * @return array The list of usergroups
     */
    public function get_members_groups($id, $skip_secret = false, $handle_probation = true)
    {
        if ((is_guest($id)) && (get_forum_type() == 'cns')) {
            static $ret = null;
            if ($ret === null) {
                $ret = array(db_get_first_id());
            }
            return $ret;
        }

        global $USERS_GROUPS_CACHE;
        if (isset($USERS_GROUPS_CACHE[$id][$skip_secret][$handle_probation])) {
            return $USERS_GROUPS_CACHE[$id][$skip_secret][$handle_probation];
        }

        $ret = $this->_get_members_groups($id, $skip_secret, $handle_probation);
        $USERS_GROUPS_CACHE[$id][$skip_secret][$handle_probation] = array_unique($ret);
        return $ret;
    }

    /**
     * Get the current member's theme identifier.
     *
     * @param  ?ID_TEXT $zone_for The zone we are getting the theme for (null: current zone)
     * @param  ?MEMBER $member_id The member we are getting the theme for (null: current user)
     * @return ID_TEXT The theme identifier
     */
    public function get_theme($zone_for = null, $member_id = null)
    {
        global $SITE_INFO, $ZONE, $USER_THEME_CACHE, $IN_MINIKERNEL_VERSION;

        if (($member_id === null) || ($IN_MINIKERNEL_VERSION)) {
            $member_id = $IN_MINIKERNEL_VERSION ? $this->get_guest_id() : get_member();
            $is_current_member = true;
        } else {
            $is_current_member = (get_member() == $member_id);
        }

        if ($zone_for === null) {
            if (($USER_THEME_CACHE !== null) && ($is_current_member)) {
                return $USER_THEME_CACHE;
            }

            $zone_for = get_zone_name();
            $current_zone_requested = true;
        } else {
            $current_zone_requested = (get_zone_name() == $zone_for);
        }

        if (($IN_MINIKERNEL_VERSION) || (in_safe_mode())) {
            return ($zone_for === 'adminzone' || $zone_for === 'cms') ? 'admin' : 'default';
        }

        // Try hardcoded in URL
        $theme = $is_current_member ? filter_naughty(get_param_string('keep_theme', get_param_string('utheme', '-1'))) : '-1';
        if ($theme != '-1') {
            if ((!is_dir(get_file_base() . '/themes/' . $theme)) && (!is_dir(get_custom_file_base() . '/themes/' . $theme))) { // Sanity check
                require_code('site');
                attach_message(do_lang_tempcode('NO_SUCH_THEME', escape_html($theme)), 'warn');
            } else {
                $zone_theme = ($ZONE === null || !$current_zone_requested) ? $GLOBALS['SITE_DB']->query_select_value_if_there('zones', 'zone_theme', array('zone_name' => $zone_for)) : $ZONE['zone_theme'];

                require_code('permissions');
                if (($theme == 'default') || ($theme == $zone_theme) || (has_category_access($member_id, 'theme', $theme))) { // Permissions check (but only if it's not what the zone setting says it should be anyway)
                    if (($current_zone_requested) && ($is_current_member)) {
                        $USER_THEME_CACHE = $theme;
                    }
                    return $theme;
                } else {
                    if (running_script('index')) {
                        require_code('site');
                        attach_message(do_lang_tempcode('NO_THEME_PERMISSION', escape_html($theme)), 'warn');
                    }
                }
            }
        }

        // Try hardcoded in Composr zone settings
        $zone_theme = ($ZONE === null || !$current_zone_requested) ? $GLOBALS['SITE_DB']->query_select_value_if_there('zones', 'zone_theme', array('zone_name' => $zone_for)) : $ZONE['zone_theme'];
        $default_theme = ((get_page_name() == 'login') && (get_option('root_zone_login_theme') == '1') && ($zone_for != '')) ? $GLOBALS['SITE_DB']->query_select_value('zones', 'zone_theme', array('zone_name' => '')) : $zone_theme;
        if (empty($default_theme)) { // Cleanup bad data
            $default_theme = '-1';
        }
        if ($default_theme != '-1') { // Sanity check
            if ((!isset($SITE_INFO['no_disk_sanity_checks'])) || ($SITE_INFO['no_disk_sanity_checks'] != '1')) {
                if (!is_dir(get_custom_file_base() . '/themes/' . $default_theme)) {
                    $default_theme = '-1';
                }
            }
        }
        if ($default_theme != '-1') {
            $theme = $default_theme;
            if (($current_zone_requested) && ($is_current_member)) {
                $USER_THEME_CACHE = $theme;
            }
            return $theme;
        }

        // Get from member setting
        require_code('permissions');
        $theme = filter_naughty($this->_get_theme(false, $member_id));
        if (empty($theme)) { // Cleanup bad data
            $theme = '-1';
        }
        if ( // Sanity/permissions check
            ($theme == '-1') ||
            (($theme != 'default') && (!is_dir(get_custom_file_base() . '/themes/' . $theme))) ||
            (!has_category_access($member_id, 'theme', $theme))
        ) {
            if (!is_guest($member_id)) {
                // Load what a guest would see if there's something broken about a member's choice
                return $this->get_theme(null, $GLOBALS['FORUM_DRIVER']->get_guest_id());
            }

            // If even the guest setting is broken (and if nothing is hardcoded into Composr zone settings), return the software default
            $theme = 'default';
        }
        if (($current_zone_requested) && ($is_current_member)) {
            $USER_THEME_CACHE = $theme;
        }
        return $theme;
    }

    /**
     * Get the number of new forum posts on the system in the last 24 hours.
     *
     * @return integer Number of forum posts
     */
    public function get_num_new_forum_posts()
    {
        $value = strval($this->_get_num_new_forum_posts());
        return intval($value);
    }

    /**
     * Find whether a forum is threaded.
     *
     * @param  integer $topic_id The topic ID
     * @return boolean Whether it is
     */
    public function topic_is_threaded($topic_id)
    {
        return false;
    }

    /**
     * Load extra details for a list of posts. Does not need to return anything if forum driver doesn't support partial post loading (which is only useful for threaded topic partial-display).
     *
     * @param  AUTO_LINK $topic_id Topic the posts come from
     * @param  array $post_ids List of post IDs
     * @return array Extra details
     */
    public function get_post_remaining_details($topic_id, $post_ids)
    {
        return array();
    }
}
