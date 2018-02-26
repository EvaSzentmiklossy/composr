<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licencing information.


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
 * Allow all usergroups to access a category.
 *
 * @param  string $module The module
 * @param  mixed $category The category (integer or string)
 */
function set_global_category_access($module, $category)
{
    if (is_integer($category)) {
        $category = strval($category);
    }

    $admin_groups = $GLOBALS['FORUM_DRIVER']->get_super_admin_groups();
    $groups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list(false, true, true);

    $db = $GLOBALS[(($module == 'forums') && (get_forum_type() == 'cns')) ? 'FORUM_DB' : 'SITE_DB'];

    $db->query_delete('group_category_access', array('module_the_name' => $module, 'category_name' => $category));

    foreach (array_keys($groups) as $group_id) {
        if (in_array($group_id, $admin_groups)) {
            continue;
        }

        $db->query_insert('group_category_access', array('module_the_name' => $module, 'category_name' => $category, 'group_id' => $group_id));
    }
}

/**
 * Define page permissions programmatically.
 * Assumes Conversr.
 * This function is intended for programmers, writing upgrade scripts for a custom site (dev>staging>live).
 *
 * @param  array $no_guest_permissions Simple list of pages that only logged in users can see
 * @param  array $only_admin_permissions Simple list of pages that only administrators can see
 * @param  string $zone The zone to do this in
 * @param  boolean $overwrite_all Whether to flush out all existing data
 */
function mass_set_page_access($no_guest_permissions, $only_admin_permissions, $zone, $overwrite_all = false)
{
    if ($overwrite_all) {
        $GLOBALS['SITE_DB']->query_delete('group_page_access');

        foreach ($no_guest_permissions as $page) {
            $GLOBALS['SITE_DB']->query_delete('group_page_access', array('page_name' => $page, 'zone_name' => $zone));

            $GLOBALS['SITE_DB']->query_insert('group_page_access', array('page_name' => $page, 'zone_name' => $zone, 'group_id' => db_get_first_id()));
        }

        $usergroups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list(false, true);
        $admin_groups = $GLOBALS['FORUM_DRIVER']->get_super_admin_groups();

        foreach ($only_admin_permissions as $page) {
            $GLOBALS['SITE_DB']->query_delete('group_page_access', array('page_name' => $page, 'zone_name' => $zone));

            foreach (array_keys($usergroups) as $id) {
                if (in_array($id, $admin_groups)) {
                    continue;
                }

                $GLOBALS['SITE_DB']->query_insert('group_page_access', array('page_name' => $page, 'zone_name' => $zone, 'group_id' => $id));
            }
        }
    }
}

/**
 * Log permission checks to the permission_checks.log file.
 *
 * @param  MEMBER $member_id The user checking against
 * @param  ID_TEXT $op The function that was called to check a permission
 * @param  array $params Parameters to this permission-checking function
 * @param  boolean $result Whether the permission was held
 *
 * @ignore
 */
function _handle_permission_check_logging($member_id, $op, $params, $result)
{
    global $PERMISSION_CHECK_LOGGER;

    if ($op == 'has_privilege') {
        require_all_lang();
        $params[0] = $params[0] . ' ("' . do_lang('PRIVILEGE_' . $params[0]) . '")';
    }

    $str = $op;
    if (count($params) != 0) {
        $str .= ': ';
        foreach ($params as $i => $p) {
            if ($i != 0) {
                $str .= ',';
            }

            $str .= is_string($p) ? $p : (($p === null) ? '' : strval($p));
        }
    }

    $show_all = (get_value('permission_log_success_too') === '1');
    if (($PERMISSION_CHECK_LOGGER !== false) && (($show_all) || (!$result))) {
        fwrite($PERMISSION_CHECK_LOGGER, "\t" . ($show_all ? '' : '! ') . $str);
        $username = $GLOBALS['FORUM_DRIVER']->get_username($member_id);
        if ($member_id != get_member()) {
            fwrite($PERMISSION_CHECK_LOGGER, ' -- ' . $username);
        }
        if ($show_all) {
            fwrite($PERMISSION_CHECK_LOGGER, ' --> ' . ($result ? do_lang('YES') : do_lang('NO')) . "\n");
        }
        fwrite($PERMISSION_CHECK_LOGGER, "\n");
        sync_file(get_custom_file_base() . '/data_custom/permission_checks.log');
    }

    if ((function_exists('fb')) && (get_param_integer('keep_firephp', 0) == 1) && (!headers_sent())) {
        fb('Permission check ' . ($result ? 'PASSED' : 'FAILED') . ': ' . $str);
    }
}

/**
 * Find if a group has a specified privilege.
 *
 * @param  GROUP $group_id The being checked whether to have the privilege
 * @param  ID_TEXT $privilege The ID code for the privilege being checked for
 * @param  ?ID_TEXT $page The ID code for the page being checked (null: current page)
 * @param  ?array $cats A list of cat details to require access to (c-type-1,c-id-1,c-type-2,c-d-2,...) (null: N/A)
 * @return boolean Whether the member has the privilege
 */
function has_privilege_group($group_id, $privilege, $page = null, $cats = null)
{
    if ($page === null) {
        $page = get_page_name();
    }

    global $GROUP_PRIVILEGE_CACHE;
    if (array_key_exists($group_id, $GROUP_PRIVILEGE_CACHE)) {
        if ($cats !== null) {
            for ($i = 0; $i < intval(floor(count($cats) / 2)); $i++) {
                if ($cats[$i * 2] === null) {
                    continue;
                }
                if (isset($GROUP_PRIVILEGE_CACHE[$group_id][$privilege][''][$cats[$i * 2 + 0]][$cats[$i * 2 + 1]])) {
                    return $GROUP_PRIVILEGE_CACHE[$group_id][$privilege][''][$cats[$i * 2 + 0]][$cats[$i * 2 + 1]];
                }
            }
        }
        if ($page != '') {
            if (isset($GROUP_PRIVILEGE_CACHE[$group_id][$privilege][$page][''][''])) {
                return $GROUP_PRIVILEGE_CACHE[$group_id][$privilege][$page][''][''];
            }
        }
        if (isset($GROUP_PRIVILEGE_CACHE[$group_id][$privilege][''][''][''])) {
            return $GROUP_PRIVILEGE_CACHE[$group_id][$privilege][''][''][''];
        }
        return false;
    }

    $perhaps = $GLOBALS['SITE_DB']->query_select('group_privileges', array('*'), array('group_id' => $group_id));
    if (is_on_multi_site_network() && (get_forum_type() == 'cns')) {
        $perhaps = array_merge($perhaps, $GLOBALS['FORUM_DB']->query_select('group_privileges', array('*'), array('group_id' => $group_id, 'module_the_name' => 'forums')));
    }
    $GROUP_PRIVILEGE_CACHE[$group_id] = array();
    foreach ($perhaps as $p) {
        if (!@$GROUP_PRIVILEGE_CACHE[$group_id][$p['privilege']][$p['the_page']][$p['module_the_name']][$p['category_name']]) {
            $GROUP_PRIVILEGE_CACHE[$group_id][$p['privilege']][$p['the_page']][$p['module_the_name']][$p['category_name']] = ($p['the_value'] == 1);
        }
    }

    return has_privilege_group($group_id, $privilege, $page, $cats);
}

/**
 * Get hidden fields for setting category access permissions as on.
 *
 * @return Tempcode Hidden fields
 */
function get_category_permissions_hidden_on()
{
    $hidden = new Tempcode();
    $all_groups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list(true);
    foreach (array_keys($all_groups) as $id) {
        $hidden->attach(form_input_hidden('access_' . strval($id), '1'));
    }
    return $hidden;
}

/**
 * Gather the permissions for the specified category as a form field input matrix.
 *
 * @param  ID_TEXT $module The ID code for the module being checked for category access
 * @param  ID_TEXT $category The ID code for the category being checked for access (often, a number cast to a string)
 * @param  ?ID_TEXT $page The page this is for (null: current page)
 * @param  ?Tempcode $help Extra help to show in interface (null: none)
 * @param  boolean $new_category Whether this is a new category (don't load permissions, default to on)
 * @param  ?Tempcode $pinterface_view Label for view permissions (null: default)
 * @return Tempcode The form field matrix
 */
function get_category_permissions_for_environment($module, $category, $page = null, $help = null, $new_category = false, $pinterface_view = null)
{
    if ($page === null) {
        $page = get_page_name();
    }
    if ($category == '-1') {
        $category = null;
    }
    if ($category == '') {
        $category = null;
    }

    $server_id = get_module_zone($page, 'modules', null, 'php', true, false) . ':' . $page; // $category is not of interest to us because we use this to find our inheritance settings

    $db = $GLOBALS[(($module == 'forums') && (get_forum_type() == 'cns')) ? 'FORUM_DB' : 'SITE_DB'];

    $admin_groups = $GLOBALS['FORUM_DRIVER']->get_super_admin_groups();
    $groups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list(true, true);

    // View access
    $access = array();
    foreach (array_keys($groups) as $id) {
        $access[$id] = $new_category ? 1 : 0;
    }
    if (!$new_category) {
        $access_rows = $db->query_select('group_category_access', array('group_id'), array('module_the_name' => $module, 'category_name' => $category));
        foreach ($access_rows as $row) {
            $access[$row['group_id']] = 1;
        }
    }

    // Privileges
    $privileges = array();
    $access_rows = $db->query_select('group_privileges', array('group_id', 'privilege', 'the_value'), array('module_the_name' => $module, 'category_name' => $category));
    foreach ($access_rows as $row) {
        $privileges[$row['privilege']][$row['group_id']] = strval($row['the_value']);
    }

    // Heading
    require_code('zones2');
    $_overridables = extract_module_functions_page(get_module_zone($page, 'modules', null, 'php', true, false), $page, array('get_privilege_overrides'));
    $out = new Tempcode;
    if ($_overridables[0] === null) {
        $temp = do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => '6789cb454688a1bc811af1b4011ede35', 'TITLE' => do_lang_tempcode('PERMISSIONS'), 'HELP' => $help, 'SECTION_HIDDEN' => true));
        $overridables = array();
    } else {
        require_lang('permissions');
        $temp = do_template('FORM_SCREEN_FIELD_SPACER', array(
            '_GUID' => 'd4659e64eaeb8e9f4c09255a8d3c9f33',
            'TITLE' => do_lang_tempcode('PERMISSIONS'),
            'HELP' => do_lang_tempcode('PINTERACE_HELP'),
            'SECTION_HIDDEN' => true,
        ));
        $overridables = is_array($_overridables[0]) ? call_user_func_array($_overridables[0][0], $_overridables[0][1]) : eval($_overridables[0]);
    }
    $out->attach($temp);

    // Find out inherited permissions
    $default_access = array();
    $all_groups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list(true);
    foreach (array_keys($access) as $id) {
        if ((!array_key_exists($id, $groups)) && (array_key_exists($id, $all_groups))) {
            $groups[$id] = $all_groups[$id];
        }
    }
    foreach ($groups as $id => $group_name) {
        $default_access[$id] = array();
        if (!in_array($id, $admin_groups)) {
            foreach ($overridables as $override => $cat_support) {
                if (is_array($cat_support)) {
                    $cat_support = $cat_support[0];
                }

                $default_access[$id][$override] = array();
                if ($cat_support == 0) {
                    continue;
                }
                $default_access[$id][$override] = has_privilege_group($id, $override, $page) ? '1' : '0';
            }
        }
    }

    // Render actual permissions matrix
    $out->attach(get_permissions_matrix($server_id, $access, $overridables, $privileges, $default_access, true, $pinterface_view));

    return $out;
}

/**
 * Create a form field input matrix for permission setting.
 *
 * @param  ID_TEXT $server_id Permission ID (page_link style) for the resource being set
 * @param  array $access An inverted list showing what view permissions are set for what we're setting permissions for
 * @param  array $overridables List of overridable privilege codes for what we're setting permissions for
 * @param  array $privileges List of privilege settings relating to what we're setting permissions for, from the database
 * @param  array $default_access Multi-dimensional array showing what the inherited defaults for this permission would be
 * @param  boolean $include_outer Whether to include the stuff to make it fit alongside other form fields in a normal form table
 * @param  ?Tempcode $pinterface_view Label for view permissions (null: default)
 * @return Tempcode The form field matrix
 */
function get_permissions_matrix($server_id, $access, $overridables, $privileges, $default_access, $include_outer = true, $pinterface_view = null)
{
    require_lang('permissions');
    require_javascript('core_permission_management');

    $admin_groups = $GLOBALS['FORUM_DRIVER']->get_super_admin_groups();
    $groups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list(true, true);

    if ($pinterface_view === null) {
        $pinterface_view = do_lang_tempcode('PINTERFACE_VIEW');
    }

    // Permission rows for matrix
    require_code('form_templates');
    $permission_rows = new Tempcode();
    $all_groups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list(true);
    foreach (array_keys($access) as $id) {
        if ((!array_key_exists($id, $groups)) && (array_key_exists($id, $all_groups))) {
            $groups[$id] = $all_groups[$id];
        }
    }
    foreach ($groups as $id => $group_name) {
        if (!in_array($id, $admin_groups)) {
            $perhaps = (count($access) == 0) ? 1 : $access[$id];
            $view_access = $perhaps == 1;
            $tabindex = get_form_field_tabindex(null);

            $overrides = new Tempcode();
            $all_global = true;
            foreach (array_keys($overridables) as $override) {
                if (isset($privileges[$override][$id])) {
                    $all_global = false;
                }
            }
            foreach ($overridables as $override => $cat_support) {
                $lang_string = do_lang_tempcode('PRIVILEGE_' . $override);
                if (is_array($cat_support)) {
                    $lang_string = do_lang_tempcode($cat_support[1]);
                }
                if (is_array($cat_support)) {
                    $cat_support = $cat_support[0];
                }
                if ($cat_support == 0) {
                    continue;
                }

                $overrides->attach(do_template('FORM_SCREEN_INPUT_PERMISSION_OVERRIDE', array(
                    '_GUID' => '115fbf91873be9016c5e192f5a5e090b',
                    'FORCE_PRESETS' => !$include_outer,
                    'GROUP_NAME' => $group_name,
                    'VIEW_ACCESS' => $view_access,
                    'TABINDEX' => strval($tabindex),
                    'GROUP_ID' => strval($id),
                    'PRIVILEGE' => $override,
                    'ALL_GLOBAL' => $all_global,
                    'TITLE' => $lang_string,
                    'DEFAULT_ACCESS' => $default_access[$id][$override],
                    'CODE' => isset($privileges[$override][$id]) ? $privileges[$override][$id] : '-1',
                )));

                check_suhosin_request_quantity(1, strlen('access_' . strval($id) . '_privilege_' . $override));
            }
            $permission_rows->attach(do_template('FORM_SCREEN_INPUT_PERMISSION', array(
                '_GUID' => 'e2c4459ae995d33376c07e498f1d973a',
                'FORCE_PRESETS' => !$include_outer,
                'GROUP_NAME' => $group_name,
                'OVERRIDES' => $overrides->evaluate()/*FUDGE*/,
                'ALL_GLOBAL' => $all_global,
                'VIEW_ACCESS' => $view_access,
                'TABINDEX' => strval($tabindex),
                'GROUP_ID' => strval($id),
                'PINTERFACE_VIEW' => $pinterface_view,
            )));

            check_suhosin_request_quantity(2, strlen('access_' . strval($id)));
        } else {
            $overridables_filtered = array();
            foreach ($overridables as $override => $cat_support) {
                if (is_array($cat_support)) {
                    $cat_support = $cat_support[0];
                }
                if ($cat_support == 1) {
                    $overridables_filtered[$override] = 1;
                }
            }
            $permission_rows->attach(do_template('FORM_SCREEN_INPUT_PERMISSION_ADMIN', array(
                '_GUID' => '59fafa2fa66ec6eb0fe2432b1d747636',
                'FORCE_PRESETS' => !$include_outer,
                'OVERRIDES' => $overridables_filtered,
                'GROUP_NAME' => $group_name,
                'GROUP_ID' => strval($id),
                'PINTERFACE_VIEW' => $pinterface_view,
            )));
        }
    }
    if ((count($overridables) == 0) && ($include_outer)) {
        return $permission_rows;
    }

    // Find out colour for our vertical text image headings (CSS can't rotate text), using the CSS as a basis
    $css_path = get_custom_file_base() . '/themes/' . $GLOBALS['FORUM_DRIVER']->get_theme() . '/templates_cached/' . user_lang() . '/global.css';
    $color = 'FF00FF';
    if (file_exists($css_path)) {
        $tmp_file = cms_file_get_contents_safe($css_path);
        $matches = array();
        if (preg_match('#(\s|\})th[\s,][^\}]*(\s|\{)background-color:\s*\#([\dA-Fa-f]*);color:\s*\#([\dA-Fa-f]*);#sU', $tmp_file, $matches) != 0) {
            $color = $matches[3] . '&fg_color=' . urlencode($matches[4]);
        }
    }

    // For heading up the table matrix
    $overrides_array = array();
    foreach ($overridables as $override => $cat_support) {
        $lang_string = do_lang_tempcode('PRIVILEGE_' . $override);
        if (is_array($cat_support)) {
            $lang_string = do_lang_tempcode($cat_support[1]);
        }
        if (is_array($cat_support)) {
            $cat_support = $cat_support[0];
        }
        if ($cat_support == 0) {
            continue;
        }

        $overrides_array[$override] = array('TITLE' => $lang_string);
    }

    // Finish off the matrix and return
    $inner = do_template('FORM_SCREEN_INPUT_PERMISSION_MATRIX', array(
        '_GUID' => '0f019c7e60366fa04058097ee6f3829a',
        'SERVER_ID' => $server_id,
        'COLOR' => $color,
        'OVERRIDES' => $overrides_array,
        'PERMISSION_ROWS' => $permission_rows,
    ));

    if (!$include_outer) {
        return make_string_tempcode(static_evaluate_tempcode($inner));
    }
    return make_string_tempcode(static_evaluate_tempcode(do_template('FORM_SCREEN_INPUT_PERMISSION_MATRIX_OUTER', array('_GUID' => '2a2f9f78f3639185300c92cab50767c5', 'INNER' => $inner))));
}

/**
 * Assuming that permission details are POSTed, set the permissions for the specified category, in the current page.
 *
 * @param  ID_TEXT $module The ID code for the module being checked for category access
 * @param  ID_TEXT $category The ID code for the category being checked for access (often, a number cast to a string)
 * @param  ?ID_TEXT $page The page this is for (null: current page)
 */
function set_category_permissions_from_environment($module, $category, $page = null)
{
    if ($page === null) {
        $page = get_page_name();
    }

    require_code('zones2');

    $admin_groups = $GLOBALS['FORUM_DRIVER']->get_super_admin_groups();
    $groups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list(false, true);

    $db = $GLOBALS[(($module == 'forums') && (get_forum_type() == 'cns')) ? 'FORUM_DB' : 'SITE_DB'];

    // Based on old access settings, we may need to look at additional groups (clubs) that have permissions here
    $access = array();
    $access_rows = $db->query_select('group_category_access', array('group_id'), array('module_the_name' => $module, 'category_name' => $category));
    foreach ($access_rows as $row) {
        $access[$row['group_id']] = 1;
    }
    $all_groups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list(true);
    foreach (array_keys($access) as $id) {
        if ((!array_key_exists($id, $groups)) && (array_key_exists($id, $all_groups))) {
            $groups[$id] = $all_groups[$id];
        }
    }

    foreach (array_keys($groups) as $group_id) { // Only delete PERMISSIVE groups, so not to effect clubs
        if (in_array($group_id, $admin_groups)) {
            continue;
        }

        $db->query_delete('group_category_access', array('module_the_name' => $module, 'category_name' => $category, 'group_id' => $group_id));
    }

    $_overridables = extract_module_functions_page(get_module_zone($page, 'modules', null, 'php', true, false), $page, array('get_privilege_overrides'));
    if ($_overridables[0] === null) {
        $overridables = array();
    } else {
        $overridables = is_array($_overridables[0]) ? call_user_func_array($_overridables[0][0], $_overridables[0][1]) : eval($_overridables[0]);
    }

    foreach ($overridables as $override => $cat_support) {
        if (is_array($cat_support)) {
            $cat_support = $cat_support[0];
        }
        $db->query_delete('group_privileges', array('privilege' => $override, 'module_the_name' => $module, 'category_name' => $category));
    }
    foreach (array_keys($groups) as $group_id) {
        if (in_array($group_id, $admin_groups)) {
            continue;
        }

        $value = post_param_integer('access_' . strval($group_id), 0);
        if ($value == 1) {
            $db->query_insert('group_category_access', array('module_the_name' => $module, 'category_name' => $category, 'group_id' => $group_id), false, true); // Race/corruption condition
        }
        foreach ($overridables as $override => $cat_support) {
            if (is_array($cat_support)) {
                $cat_support = $cat_support[0];
            }
            if ($cat_support == 0) {
                continue;
            }

            $value = post_param_integer('access_' . strval($group_id) . '_privilege_' . $override, -1);
            if ($value != -1) {
                $db->query_insert('group_privileges', array('privilege' => $override, 'group_id' => $group_id, 'module_the_name' => $module, 'category_name' => $category, 'the_page' => '', 'the_value' => $value));
            }
        }
    }

    delete_cache_entry('menu');
}

/**
 * Gather the permissions for the specified page as form field inputs.
 *
 * @param  ID_TEXT $zone The ID code for the zone
 * @param  ID_TEXT $page The ID code for the page
 * @param  ?Tempcode $help Extra help to show in interface (null: none)
 * @return Tempcode The form fields
 */
function get_page_permissions_for_environment($zone, $page, $help = null)
{
    require_lang('permissions');

    $admin_groups = $GLOBALS['FORUM_DRIVER']->get_super_admin_groups();
    $groups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list(true, true);

    // View access
    $access = array();
    foreach (array_keys($groups) as $id) {
        $access[$id] = 0;
    }
    $access_rows = $GLOBALS['SITE_DB']->query_select('group_page_access', array('group_id'), array('zone_name' => $zone, 'page_name' => $page));
    foreach ($access_rows as $row) {
        $access[$row['group_id']] = 1;
    }

    // Interface
    $fields = new Tempcode();
    $temp = do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => '3bf8415fd44bf48c6ab49dede3dbfea5', 'TITLE' => do_lang_tempcode('PERMISSIONS'), 'HELP' => $help, 'SECTION_HIDDEN' => true));
    $fields->attach($temp);
    foreach ($groups as $id => $group_name) {
        if (!in_array($id, $admin_groups)) {
            $perhaps = $access[$id];
            $overrides = array();
            $temp = form_input_tick(do_lang_tempcode('ACCESS_FOR', escape_html($group_name)), do_lang_tempcode('DESCRIPTION_ACCESS_FOR', escape_html($group_name)), 'access_' . strval($id), $perhaps == 0);
            $fields->attach($temp);
        }
    }

    return $fields;
}

/**
 * Assuming that permission details are POSTed, set the permissions for the specified category, in the current page.
 *
 * @param  ID_TEXT $zone The ID code for the zone
 * @param  ID_TEXT $page The ID code for the page
 */
function set_page_permissions_from_environment($zone, $page)
{
    if ($page === null) {
        $page = get_page_name();
    }

    $admin_groups = $GLOBALS['FORUM_DRIVER']->get_super_admin_groups();
    $groups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list(false, true);
    $GLOBALS['SITE_DB']->query_delete('group_page_access', array('zone_name' => $zone, 'page_name' => $page));

    foreach (array_keys($groups) as $group_id) {
        if (in_array($group_id, $admin_groups)) {
            continue;
        }

        $value = post_param_integer('access_' . strval($group_id), 0);
        if ($value == 0) {
            $GLOBALS['SITE_DB']->query_insert('group_page_access', array('zone_name' => $zone, 'page_name' => $page, 'group_id' => $group_id), false, true); // Race/corruption condition
        }
    }

    delete_cache_entry('menu');
    require_code('caches3');
    erase_block_cache();
    erase_persistent_cache();
}
