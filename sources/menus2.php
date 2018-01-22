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
 * @package    core_menus
 */

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__menus2()
{
    global $ADD_MENU_COUNTER;
    $ADD_MENU_COUNTER = 10;
}

/**
 * Export a menu structure to a CSV file.
 *
 * @param  ?PATH $file_path The path to the CSV file (null: uploads/website_specific/cms_menu_items.csv).
 */
function export_menu_csv($file_path = null)
{
    if (is_null($file_path)) {
        $file_path = get_custom_file_base() . '/uploads/website_specific/cms_menu_items.csv';
    }

    $sql = 'SELECT m.id, i_menu, i_order, i_parent, i_url, i_check_permissions, i_expanded, i_new_window, i_page_only, i_theme_img_code, i_caption, i_caption_long, i_include_sitemap FROM ' . get_table_prefix() . 'menu_items m';

    $data = $GLOBALS['SITE_DB']->query($sql, null, null, false, true);

    foreach ($data as &$d) {
        $d['i_caption'] = get_translated_text($d['i_caption']);
        $d['i_caption_long'] = get_translated_text($d['i_caption_long']);
    }

    require_code('files');
    require_code('files2');
    $csv = make_csv($data, 'data.csv', false, false);
    cms_file_put_contents_safe($file_path, $csv, FILE_WRITE_FIX_PERMISSIONS | FILE_WRITE_SYNC_FILE);
}

/**
 * Import a CSV menu structure, after ERASING whole current menu structure.
 * This function is intended for programmers, writing upgrade scripts for a custom site (dev>staging>live).
 * Assumes CSV was generated with export_menu_csv.
 *
 * @param  ?PATH $file_path The path to the CSV file (null: uploads/website_specific/cms_menu_items.csv).
 */
function import_menu_csv($file_path = null)
{
    $old_menu_items = $GLOBALS['SITE_DB']->query_select('menu_items', array('i_caption', 'i_caption_long'));
    foreach ($old_menu_items as $old_menu_item) {
        delete_lang($old_menu_item['i_caption']);
        delete_lang($old_menu_item['i_caption_long']);
    }
    $GLOBALS['SITE_DB']->query_delete('menu_items');

    if (is_null($file_path)) {
        $file_path = get_custom_file_base() . '/uploads/website_specific/cms_menu_items.csv';
    }
    $myfile = fopen($file_path, 'rt');
    while (($record = fgetcsv($myfile, 8192)) !== false) {
        if (!isset($record[12])) {
            continue;
        }
        if ($record[0] == 'id') {
            continue;
        }

        $id = ($record[0] == '' || $record[0] == 'NULL') ? null : intval($record[0]);
        $menu = $record[1];
        $order = intval($record[2]);
        $parent = ($record[3] == '' || $record[3] == 'NULL') ? null : intval($record[3]);
        $caption = $record[10];
        $url = $record[4];
        $check_permissions = intval($record[5]);
        $page_only = $record[8];
        $expanded = intval($record[6]);
        $new_window = intval($record[7]);
        $caption_long = $record[11];
        $theme_image_code = $record[9];
        $include_sitemap = intval($record[12]);

        add_menu_item($menu, $order, $parent, $caption, $url, $check_permissions, $page_only, $expanded, $new_window, $caption_long, $theme_image_code, $include_sitemap, $id);
    }
    fclose($myfile);

    decache('menu');
}

/**
 * Move a menu branch.
 */
function menu_management_script()
{
    $id = get_param_integer('id');
    $to_menu = get_param_string('menu');
    $changes = array('i_menu' => $to_menu);

    $rows = $GLOBALS['SITE_DB']->query_select('menu_items', array('*'), array('id' => $id), '', 1);
    if (array_key_exists(0, $rows)) {
        $row = $rows[0];
    } else {
        $row = null;
    }

    $test = false;

    foreach (array_keys($test ? $_GET : $_POST) as $key) {
        $val = $test ? get_param_string($key) : post_param_string($key);
        $key = preg_replace('#\_\d+$#', '', $key);
        if (($key == 'caption') || ($key == 'caption_long')) {
            if (is_null($row)) {
                $changes += insert_lang('i_' . $key, $val, 2);
            } else {
                $changes += lang_remap('i_' . $key, $row['i_' . $key], $val);
            }
        } elseif (($key == 'url') || ($key == 'theme_img_code')) {
            $changes['i_' . $key] = $val;
        } elseif ($key == 'page_only') {
            $changes['i_page_only'] = $val;
        }
    }
    $changes['i_order'] = post_param_integer('order_' . strval($id), 0);
    $changes['i_new_window'] = post_param_integer('new_window_' . strval($id), 0);
    $changes['i_check_permissions'] = post_param_integer('check_perms_' . strval($id), 0);
    $changes['i_include_sitemap'] = post_param_integer('include_sitemap_' . strval($id), 0);
    $changes['i_expanded'] = 0;
    $changes['i_parent'] = null;

    if (is_null($row)) {
        $GLOBALS['SITE_DB']->query_insert('menu_items', $changes);
    } else {
        $GLOBALS['SITE_DB']->query_update('menu_items', $changes, array('id' => $id), '', 1);
    }
}

/**
 * Add a menu item, without giving tedious/unnecessary detail.
 *
 * @param  SHORT_TEXT $menu The name of the menu to add the item to.
 * @param  ?mixed $parent The menu item ID of the parent branch of the menu item (AUTO_LINK) / the URL of something else on the same menu (URLPATH) (null: is on root).
 * @param  SHORT_TEXT $caption The caption.
 * @param  SHORT_TEXT $url The URL (in entry point form).
 * @param  BINARY $expanded Whether it is an expanded branch.
 * @param  BINARY $check_permissions Whether people who may not view the entry point do not see the link.
 * @param  boolean $dereference_caption Whether the caption is a language string.
 * @param  SHORT_TEXT $caption_long The tooltip (blank: none).
 * @param  BINARY $new_window Whether the link will open in a new window.
 * @param  ID_TEXT $theme_image_code The theme image code.
 * @param  SHORT_INTEGER $include_sitemap An INCLUDE_SITEMAP_* constant
 * @param  ?integer $order Order to use (null: automatic, after the ones that have it specified).
 * @return AUTO_LINK The ID of the newly added menu item.
 */
function add_menu_item_simple($menu, $parent, $caption, $url = '', $expanded = 0, $check_permissions = 0, $dereference_caption = true, $caption_long = '', $new_window = 0, $theme_image_code = '', $include_sitemap = 0, $order = null)
{
    global $ADD_MENU_COUNTER;

    $id = $GLOBALS['SITE_DB']->query_select_value_if_there('menu_items', 'id', array('i_url' => $url, 'i_menu' => $menu));
    if (!is_null($id)) {
        return $id; // Already exists
    }
    if (is_string($parent)) {
        $parent = $GLOBALS['SITE_DB']->query_select_value_if_there('menu_items', 'i_parent', array('i_url' => $parent));
    }

    $_caption = (strpos($caption, ':') === false) ? do_lang($caption, null, null, null, null, false) : null;
    if (is_null($_caption)) {
        $_caption = $caption;
    }
    $id = add_menu_item($menu, $ADD_MENU_COUNTER, $parent, $dereference_caption ? $_caption : $caption, $url, $check_permissions, '', $expanded, $new_window, $caption_long, $theme_image_code, $include_sitemap);

    $ADD_MENU_COUNTER++;

    return $id;
}

/**
 * Delete a menu item, without giving tedious/unnecessary detail.
 *
 * @param  SHORT_TEXT $url The URL (in entry point form), or a caption.
 */
function delete_menu_item_simple($url)
{
    $_id = $GLOBALS['SITE_DB']->query_select('menu_items', array('id'), array('i_url' => $url));
    foreach ($_id as $id) {
        delete_menu_item($id['id']);
    }

    $_id = $GLOBALS['SITE_DB']->query_select('menu_items', array('id'), array($GLOBALS['SITE_DB']->translate_field_ref('i_caption') => $url));
    foreach ($_id as $id) {
        delete_menu_item($id['id']);
    }
}

/**
 * Add a menu item.
 *
 * @param  SHORT_TEXT $menu The name of the menu to add the item to.
 * @param  integer $order The relative order of this item on the menu.
 * @param  ?AUTO_LINK $parent The menu item ID of the parent branch of the menu item (null: is on root).
 * @param  SHORT_TEXT $caption The caption.
 * @param  SHORT_TEXT $url The URL (in entry point form).
 * @param  BINARY $check_permissions Whether people who may not view the entry point do not see the link.
 * @param  SHORT_TEXT $page_only Match-keys to identify what pages the item is shown on.
 * @param  BINARY $expanded Whether it is an expanded branch.
 * @param  BINARY $new_window Whether the link will open in a new window.
 * @param  SHORT_TEXT $caption_long The tooltip (blank: none).
 * @param  ID_TEXT $theme_image_code The theme image code.
 * @param  SHORT_INTEGER $include_sitemap An INCLUDE_SITEMAP_* constant
 * @param  ?AUTO_LINK $id The ID (null: auto-increment)
 * @return AUTO_LINK The ID of the newly added menu item.
 */
function add_menu_item($menu, $order, $parent, $caption, $url, $check_permissions, $page_only, $expanded, $new_window, $caption_long, $theme_image_code = '', $include_sitemap = 0, $id = null)
{
    $map = array(
        'i_menu' => $menu,
        'i_order' => $order,
        'i_parent' => $parent,
        'i_url' => $url,
        'i_check_permissions' => $check_permissions,
        'i_page_only' => $page_only,
        'i_include_sitemap' => $include_sitemap,
        'i_expanded' => $expanded,
        'i_new_window' => $new_window,
        'i_theme_img_code' => $theme_image_code,
    );
    $map += insert_lang_comcode('i_caption', $caption, 1);
    $map += insert_lang_comcode('i_caption_long', $caption_long, 1);
    if (!is_null($id)) {
        $map['id'] = $id;
    }
    $id = $GLOBALS['SITE_DB']->query_insert('menu_items', $map, true);

    log_it('ADD_MENU_ITEM', strval($id), $caption);

    if ((addon_installed('commandr')) && (!running_script('install'))) {
        require_code('resource_fs');
        generate_resource_fs_moniker('menu_item', strval($id), null, null, true);
    }

    return $id;
}

/**
 * Edit a menu item.
 *
 * @param  AUTO_LINK $id The ID of the menu item to edit.
 * @param  SHORT_TEXT $menu The name of the menu to add the item to.
 * @param  integer $order The relative order of this item on the menu.
 * @param  ?AUTO_LINK $parent The menu item ID of the parent branch of the menu item (null: is on root).
 * @param  SHORT_TEXT $caption The caption.
 * @param  SHORT_TEXT $url The URL (in entry point form).
 * @param  BINARY $check_permissions Whether people who may not view the entry point do not see the link.
 * @param  SHORT_TEXT $page_only Match-keys to identify what pages the item is shown on.
 * @param  BINARY $expanded Whether it is an expanded branch.
 * @param  BINARY $new_window Whether the link will open in a new window.
 * @param  SHORT_TEXT $caption_long The tooltip (blank: none).
 * @param  ID_TEXT $theme_image_code The theme image code.
 * @param  SHORT_INTEGER $include_sitemap An INCLUDE_SITEMAP_* constant
 */
function edit_menu_item($id, $menu, $order, $parent, $caption, $url, $check_permissions, $page_only, $expanded, $new_window, $caption_long, $theme_image_code, $include_sitemap)
{
    $_caption = $GLOBALS['SITE_DB']->query_select_value('menu_items', 'i_caption', array('id' => $id));
    $_caption_long = $GLOBALS['SITE_DB']->query_select_value('menu_items', 'i_caption_long', array('id' => $id));

    $map = array(
        'i_menu' => $menu,
        'i_order' => $order,
        'i_parent' => $parent,
        'i_url' => $url,
        'i_check_permissions' => $check_permissions,
        'i_page_only' => $page_only,
        'i_expanded' => $expanded,
        'i_new_window' => $new_window,
        'i_include_sitemap' => $include_sitemap,
    );
    $map += lang_remap_comcode('i_caption', $_caption, $caption);
    $map += lang_remap_comcode('i_caption_long', $_caption_long, $caption_long);
    $GLOBALS['SITE_DB']->query_update('menu_items', $map, array('id' => $id), '', 1);

    log_it('EDIT_MENU_ITEM', strval($id), $caption);

    if ((addon_installed('commandr')) && (!running_script('install'))) {
        require_code('resource_fs');
        generate_resource_fs_moniker('menu_item', strval($id));
    }
}

/**
 * Delete a menu item.
 *
 * @param  AUTO_LINK $id The ID of the menu item to delete.
 */
function delete_menu_item($id)
{
    $rows = $GLOBALS['SITE_DB']->query_select('menu_items', array('i_caption', 'i_caption_long'), array('id' => $id), '', 1);
    if (!array_key_exists(0, $rows)) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
    }
    $_caption = $rows[0]['i_caption'];
    $_caption_long = $rows[0]['i_caption_long'];

    $GLOBALS['SITE_DB']->query_delete('menu_items', array('id' => $id), '', 1);
    $caption = get_translated_text($_caption);
    delete_lang($_caption);
    delete_lang($_caption_long);

    log_it('DELETE_MENU_ITEM', strval($id), $caption);

    if ((addon_installed('commandr')) && (!running_script('install'))) {
        require_code('resource_fs');
        expunge_resource_fs_moniker('menu_item', strval($id));
    }
}

/**
 * Delete a menu.
 *
 * @param  ID_TEXT $menu_id The ID of the menu.
 */
function delete_menu($menu_id)
{
    // Get language strings currently used
    $old_menu_bits = list_to_map('id', $GLOBALS['SITE_DB']->query_select('menu_items', array('id', 'i_caption', 'i_caption_long'), array('i_menu' => $menu_id)));

    // Erase old stuff
    foreach ($old_menu_bits as $menu_item_id => $lang_code) {
        $GLOBALS['SITE_DB']->query_delete('menu_items', array('id' => $menu_item_id), '', 1);
        delete_lang($lang_code['i_caption']);
        delete_lang($lang_code['i_caption_long']);
    }

    if (get_option('header_menu_call_string') == $menu_id || get_option('header_menu_call_string') == '') {
        // Reset option to default, for auto-managed menus
        $GLOBALS['SITE_DB']->query_delete('config', array('c_name' => 'header_menu_call_string'), '', 1);

        // Clear caches
        require_code('caches3');
        if (function_exists('persistent_cache_delete')) {
            persistent_cache_delete('OPTIONS');
        }
        Self_learning_cache::erase_smart_cache();
        erase_cached_templates(false, array('GLOBAL_HTML_WRAP')); // Config option saves into templates
    }

    decache('menu');
    persistent_cache_delete(array('MENU', $menu_id));

    if ((addon_installed('commandr')) && (!running_script('install'))) {
        require_code('resource_fs');
        expunge_resource_fs_moniker('menu', $menu_id);
    }
}

/**
 * Copy a part of the Sitemap to a new menu.
 *
 * @param  ID_TEXT $target_menu The ID of the menu to save into.
 * @param  SHORT_TEXT $source Sitemap details.
 */
function copy_from_sitemap_to_new_menu($target_menu, $source)
{
    require_code('comcode_from_html');
    require_code('menus');

    $is_sitemap_menu = (preg_match('#^[' . URL_CONTENT_REGEXP . ']+$#', $source) == 0);

    if (!$is_sitemap_menu) {
        $test = $GLOBALS['SITE_DB']->query_select_value('menu_items', 'COUNT(*)', array('i_menu' => $source));
        if ($test == 0) {
            return; // Nothing to copy
        }
    }

    $root = _build_sitemap_menu($source);
    $order = 0;
    _copy_from_sitemap_to_new_menu($target_menu, $root, $order);
}

/**
 * Copy a Sitemap node's children into a new menu.
 *
 * @param  ID_TEXT $target_menu The ID of the menu to save into.
 * @param  array $node Sitemap node, containing children.
 * @param  integer $order Sequence order to save with.
 * @param  ?AUTO_LINK $parent Menu parent ID (null: root).
 *
 * @ignore
 */
function _copy_from_sitemap_to_new_menu($target_menu, $node, &$order, $parent = null)
{
    if (isset($node['children'])) {
        foreach ($node['children'] as $child) {
            $theme_image_code = mixed();
            if (!is_null($child['extra_meta']['image'])) {
                $_theme_image_code = $child['extra_meta']['image'];
                if (substr($_theme_image_code, 0, strlen(get_custom_base_url() . '/')) == get_custom_base_url() . '/') {
                    $_theme_image_code = substr($_theme_image_code, strlen(get_custom_base_url() . '/'));
                    $theme_image_code = $GLOBALS['SITE_DB']->query_select_value_if_there('theme_images', 'id', array('path' => $_theme_image_code));
                }
            }

            $branch_id = add_menu_item(
                $target_menu,
                $order,
                $parent,
                semihtml_to_comcode($child['title']->evaluate(), true),
                is_null($child['page_link']) ? '' : $child['page_link'],
                1,
                '',
                1,
                0,
                is_null($child['extra_meta']['description']) ? '' : semihtml_to_comcode($child['extra_meta']['description']->evaluate(), true),
                is_null($theme_image_code) ? '' : $theme_image_code,
                0
            );

            $order++;

            _copy_from_sitemap_to_new_menu($target_menu, $child, $order, $branch_id);
        }
    }
}
