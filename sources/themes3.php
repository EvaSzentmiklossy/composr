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
 * @package    core_themeing
 */

/**
 * Add a theme.
 *
 * @param  ID_TEXT $name The theme name
 */
function actual_add_theme($name)
{
    $GLOBALS['NO_QUERY_LIMIT'] = true;

    if ((file_exists(get_custom_file_base() . '/themes/' . $name)) || ($name == 'default')) {
        warn_exit(do_lang_tempcode('ALREADY_EXISTS', escape_html($name)));
    }

    require_code('abstract_file_manager');
    force_have_afm_details();

    // Create directories
    $dir_list = array(
        '',
        'images',
        'images/logo',
        'images_custom',
        'templates',
        'templates_custom',
        'javascript',
        'javascript_custom',
        'xml',
        'xml_custom',
        'text',
        'text_custom',
        'templates_cached',
        'css',
        'css_custom',
    );
    $langs = find_all_langs(true);
    foreach (array_keys($langs) as $lang) {
        $dir_list[] = 'templates_cached/' . $lang;
    }
    $dir_list_access = array('', 'images', 'images_custom', 'css');
    foreach ($dir_list as $dir) {
        $path = 'themes/' . $name . '/' . $dir;
        afm_make_directory($path, true);
        $path = 'themes/' . $name . '/' . (($dir == '') ? '' : ($dir . '/')) . 'index.html';
        if (file_exists(get_file_base() . '/themes/default/' . (($dir == '') ? '' : ($dir . '/')) . 'index.html')) {
            afm_copy('themes/default/' . (($dir == '') ? '' : ($dir . '/')) . 'index.html', $path, false);
        }
        $path = 'themes/' . $name . '/' . (($dir == '') ? '' : ($dir . '/')) . '.htaccess';
        if (file_exists(get_file_base() . '/themes/default/' . (($dir == '') ? '' : ($dir . '/')) . '.htaccess')) {
            afm_copy('themes/default/' . (($dir == '') ? '' : ($dir . '/')) . '.htaccess', $path, false);
        }
    }
    afm_copy('themes/default/theme.ini', 'themes/' . $name . '/theme.ini', true);

    // Copy image references from default
    $start = 0;
    do {
        $theme_images = $GLOBALS['SITE_DB']->query_select('theme_images', array('*'), array('theme' => 'default'), '', 100, $start);
        foreach ($theme_images as $theme_image) {
            $test = $GLOBALS['SITE_DB']->query_select_value_if_there('theme_images', 'id', array('theme' => $name, 'id' => $theme_image['id'], 'lang' => $theme_image['lang']));
            if (is_null($test)) {
                $GLOBALS['SITE_DB']->query_insert('theme_images', array('id' => $theme_image['id'], 'theme' => $name, 'path' => $theme_image['path'], 'lang' => $theme_image['lang']));
            }
        }
        $start += 100;
    } while (count($theme_images) == 100);

    Self_learning_cache::erase_smart_cache();

    log_it('ADD_THEME', $name);
}

/**
 * Rename a theme.
 *
 * @param  ID_TEXT $theme The original theme name
 * @param  ID_TEXT $to The new theme name
 */
function actual_rename_theme($theme, $to)
{
    if ($theme == 'default') {
        fatal_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    if ((file_exists(get_custom_file_base() . '/themes/' . $to)) || ($to == 'default')) {
        warn_exit(do_lang_tempcode('ALREADY_EXISTS', escape_html($to)));
    }

    global $USER_THEME_CACHE;
    if ((!is_null($USER_THEME_CACHE)) && ($USER_THEME_CACHE == $theme)) {
        $USER_THEME_CACHE = $to;
    }

    require_code('abstract_file_manager');
    force_have_afm_details();
    afm_move('themes/' . $theme, 'themes/' . $to);

    $GLOBALS['SITE_DB']->query_update('theme_images', array('theme' => $to), array('theme' => $theme));
    $theme_images = $GLOBALS['SITE_DB']->query('SELECT path FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'theme_images WHERE path LIKE \'themes/' . db_encode_like($theme) . '/%\'');
    foreach ($theme_images as $image) {
        $new_path = str_replace('themes/' . $theme . '/', 'themes/' . $to . '/', $image['path']);
        $GLOBALS['SITE_DB']->query_update('theme_images', array('path' => $new_path), array('path' => $image['path']), '', 1);
    }
    if (get_forum_type() == 'cns') {
        $GLOBALS['FORUM_DB']->query_update('f_members', array('m_theme' => $to), array('m_theme' => $theme));
    }
    $GLOBALS['SITE_DB']->query_update('zones', array('zone_theme' => $to), array('zone_theme' => $theme));
    log_it('RENAME_THEME', $theme, $to);
}

/**
 * Copy a theme.
 *
 * @param  ID_TEXT $theme The original theme name
 * @param  ID_TEXT $to The copy's theme name
 */
function actual_copy_theme($theme, $to)
{
    if ($theme == 'default') {
        fatal_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    if ((file_exists(get_custom_file_base() . '/themes/' . $to)) || ($to == 'default')) {
        warn_exit(do_lang_tempcode('ALREADY_EXISTS', escape_html($to)));
    }

    require_code('abstract_file_manager');
    require_code('files2');
    force_have_afm_details();
    $contents = get_directory_contents(get_custom_file_base() . '/themes/' . $theme, '', true);
    foreach ($contents as $c) {
        afm_make_directory(dirname('themes/' . $to . '/' . $c), true, true);
        afm_copy('themes/' . $theme . '/' . $c, 'themes/' . $to . '/' . $c, true);
    }
    $needed = array(
        'css',
        'css_custom',
        'images',
        'images_custom',
        'templates',
        'templates_cached/' . get_site_default_lang(),
        'templates_custom',
        'javascript_custom',
        'xml_custom',
        'text_custom',
    );
    foreach ($needed as $n) {
        afm_make_directory(dirname('themes/' . $to . '/' . $n), true, true);
    }

    $images = $GLOBALS['SITE_DB']->query_select('theme_images', array('*'), array('theme' => $theme));
    foreach ($images as $i) {
        $i['theme'] = $to;
        $i['path'] = str_replace('themes/' . $theme . '/', 'themes/' . $to . '/', $i['path']);
        $GLOBALS['SITE_DB']->query_insert('theme_images', $i, false, true);
    }

    Self_learning_cache::erase_smart_cache();

    log_it('COPY_THEME', $theme, $to);
}

/**
 * Delete a theme.
 *
 * @param  ID_TEXT $theme The theme name
 */
function actual_delete_theme($theme)
{
    if ($theme == 'default') {
        fatal_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    global $USER_THEME_CACHE;
    if ((!is_null($USER_THEME_CACHE)) && ($USER_THEME_CACHE == $theme)) {
        $USER_THEME_CACHE = 'default';
    }

    require_code('abstract_file_manager');
    force_have_afm_details();
    afm_delete_directory('themes/' . $theme, true);

    $GLOBALS['SITE_DB']->query_delete('theme_images', array('theme' => $theme));
    log_it('DELETE_THEME', $theme);
}

/**
 * AJAX script for rendering some Tempcode.
 */
function tempcode_tester_script()
{
    prepare_for_known_ajax_response();

    header('Content-type: text/plain; charset=' . get_charset());

    $tempcode = post_param_string('tempcode');

    $params = array();
    foreach ($_POST as $key => $val) {
        if ((substr($key, 0, 4) == 'key_') && ($val != '')) {
            $_key = str_replace('}', '', str_replace('{', '', post_param_string($key, '')));
            $_val = post_param_string('val_' . substr($key, 4), '');
            $params[$_key] = $_val;
        }
    }

    require_code('tempcode_compiler');
    $tpl = template_to_tempcode($tempcode);
    $bound = $tpl->bind($params, 'tempcode_tester');
    $out = $bound->evaluate();
    if (get_param_integer('comcode', 0) == 1) {
        echo static_evaluate_tempcode(comcode_to_tempcode($out));
    } else {
        echo $out;
    }
}

/**
 * Add a theme image.
 *
 * @param  ID_TEXT $theme The theme the theme image is in
 * @param  LANGUAGE_NAME $lang The language the theme image is for
 * @param  SHORT_TEXT $id The theme image ID
 * @param  URLPATH $path The URL to the theme image
 * @param  boolean $fail_ok Whether to allow failure without bombing out
 */
function actual_add_theme_image($theme, $lang, $id, $path, $fail_ok = false)
{
    $test = $GLOBALS['SITE_DB']->query_select_value_if_there('theme_images', 'id', array('id' => $id, 'theme' => $theme, 'lang' => $lang));
    if (!is_null($test)) {
        if ($fail_ok) {
            return;
        }
        warn_exit(do_lang_tempcode('ALREADY_EXISTS', escape_html($id)));
    }

    $GLOBALS['SITE_DB']->query_insert('theme_images', array('id' => $id, 'theme' => $theme, 'path' => $path, 'lang' => $lang));

    log_it('ADD_THEME_IMAGE', $id, $theme);

    Self_learning_cache::erase_smart_cache();

    if (addon_installed('!ssl')) {
        require_code('caches3');
        erase_cached_templates(false, null, TEMPLATE_DECACHE_WITH_THEME_IMAGE); // Paths may have been cached
    }
}

/**
 * Edit a theme image.
 *
 * @param  SHORT_TEXT $old_id The current theme image ID
 * @param  ID_TEXT $theme The theme the theme image is in
 * @param  LANGUAGE_NAME $lang The language the theme image is for (blank: all languages)
 * @param  SHORT_TEXT $id The new theme image ID
 * @param  URLPATH $path The URL to the theme image
 * @param  boolean $quick Whether to avoid cleanup, etc
 */
function actual_edit_theme_image($old_id, $theme, $lang, $id, $path, $quick = false)
{
    if ($old_id != $id) {
        $where_map = array('theme' => $theme, 'id' => $id);
        if ($lang != '') {
            $where_map['lang'] = $lang;
        }
        $test = $GLOBALS['SITE_DB']->query_select_value_if_there('theme_images', 'id', $where_map);
        if (!is_null($test)) {
            warn_exit(do_lang_tempcode('ALREADY_EXISTS', escape_html($id)));
        }
    }

    if (!$quick) {
        $old_url = find_theme_image($id, true, true, $theme, ($lang == '') ? null : $lang);

        if (($old_url != $path) && ($old_url != '')) {
            if (($theme == 'default') || (strpos($old_url, 'themes/default/') === false)) {
                $where_map = array('theme' => $theme, 'id' => $id);
                if ($lang != '') {
                    $where_map['lang'] = $lang;
                }
                $GLOBALS['SITE_DB']->query_delete('theme_images', $where_map);

                cleanup_theme_images($old_url);
            }
        }
    }

    if ($lang == '') {
        $langs = array_keys(find_all_langs());
    } else {
        $langs = array($lang);
    }

    $where_map = array('theme' => $theme, 'id' => $id);
    if ($lang != '') {
        $where_map['lang'] = $lang;
    }
    $GLOBALS['SITE_DB']->query_delete('theme_images', $where_map);

    foreach ($langs as $lang) {
        $GLOBALS['SITE_DB']->query_insert('theme_images', array('id' => $id, 'theme' => $theme, 'path' => $path, 'lang' => $lang), false, true);
    }

    if (!$quick) {
        Self_learning_cache::erase_smart_cache();

        if (addon_installed('!ssl')) {
            require_code('caches3');
            erase_cached_templates(false, null, TEMPLATE_DECACHE_WITH_THEME_IMAGE); // Paths may have been cached
        }

        log_it('EDIT_THEME_IMAGE', $id, $theme);
    }
}

/**
 * Delete a theme image.
 *
 * @param  SHORT_TEXT $id The theme image ID
 * @param  ?ID_TEXT $theme The theme to delete in (null: all themes)
 * @param  ?LANGUAGE_NAME $lang The language to delete in (null: all languages) (blank: all languages)
 */
function actual_delete_theme_image($id, $theme = null, $lang = null)
{
    if (!is_null($theme)) {
        $old_url = find_theme_image($id, true, true, $theme, $lang);

        $where_map = array('theme' => $theme, 'id' => $id);
        if (($lang != '') && (!is_null($lang))) {
            $where_map['lang'] = $lang;
        }
        $test = $GLOBALS['SITE_DB']->query_select_value_if_there('theme_images', 'path', $where_map);
        if (!is_null($test)) {
            $GLOBALS['SITE_DB']->query_delete('theme_images', array('id' => $id, 'path' => $test));
        }
    } else {
        $old_url = find_theme_image($id, true, true);

        $GLOBALS['SITE_DB']->query_delete('theme_images', array('id' => $id));
    }

    if ($old_url != '') {
        cleanup_theme_images($old_url);
    }

    log_it('DELETE_THEME_IMAGE', $id);
}

/**
 * Export neatly named dump of all theme images for active theme.
 */
function export_theme_images()
{
    header('Content-type: text/csv; charset=' . get_charset());
    header('Content-Disposition: attachment; filename="theme_images.tar"');

    require_code('tar');
    require_code('files');
    $my_tar = tar_open(null, 'wb');
    $theme_images = $GLOBALS['SITE_DB']->query_select('theme_images', array('DISTINCT id'));
    foreach ($theme_images as $theme_image) {
        $path = rawurldecode(find_theme_image($theme_image['id'], true, true));
        if (($path != '') && (substr($path, 0, strlen('themes/default/images/')) != 'themes/default/images/')) {
            tar_add_file($my_tar, $theme_image['id'] . '.' . get_file_extension($path), $path, 0644, null, true);
        }
    }
    tar_close($my_tar);
}

/**
 * Regenerate all the theme image paths in the database.
 *
 * @param  ID_TEXT $theme The theme we're searching in.
 * @param  ?array $langs A map of languages (lang=>true) (null: find it in-function).
 * @param  ?ID_TEXT $target_theme The theme we're storing in (null: same as $theme).
 */
function regen_theme_images($theme, $langs = null, $target_theme = null)
{
    if (is_null($langs)) {
        $langs = find_all_langs(true);
    }
    if (is_null($target_theme)) {
        $target_theme = $theme;
    }

    $made_change = true;

    $images = array_merge(find_images_do_dir($theme, 'images/', $langs), find_images_do_dir($theme, 'images_custom/', $langs));

    foreach (array_keys($langs) as $lang) {
        $where = array('lang' => $lang, 'theme' => $target_theme);
        $existing = $GLOBALS['SITE_DB']->query_select('theme_images', array('id', 'path'), $where);

        // Cleanup broken references
        foreach ($existing as $e) {
            if ((!file_exists(get_custom_file_base() . '/' . rawurldecode($e['path']))) && (!file_exists(get_file_base() . '/' . rawurldecode($e['path'])))) {
                $GLOBALS['SITE_DB']->query_delete('theme_images', $e + $where, '', 1);
            }
        }

        // Add theme images for anything on disk but not currently having a reference
        foreach ($images as $id => $path) {
            $found = false;
            foreach ($existing as $e) {
                if (($e['path'] == $path) || ($e['id'] == $id)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $nql_backup = $GLOBALS['NO_QUERY_LIMIT'];
                $GLOBALS['NO_QUERY_LIMIT'] = true;
                $correct_path = find_theme_image($id, false, true, $theme, $lang);
                $GLOBALS['SITE_DB']->query_insert('theme_images', array('id' => $id, 'lang' => $lang, 'theme' => $target_theme, 'path' => $correct_path), false, true); // race conditions
                $GLOBALS['NO_QUERY_LIMIT'] = $nql_backup;

                $made_change = false;
            }
        }
    }

    if ($made_change) {
        // Reset this so they can all load in in one go
        global $THEME_IMAGES_CACHE, $THEME_IMAGES_SMART_CACHE_LOAD;
        $THEME_IMAGES_CACHE = array();
        $THEME_IMAGES_SMART_CACHE_LOAD = 1;
    }

    Self_learning_cache::erase_smart_cache();
}

/**
 * Delete uploaded theme image if not tied into anything.
 *
 * @param  URLPATH $old_url The URL to the theme image being deleted
 */
function cleanup_theme_images($old_url)
{
    $files_referenced = collapse_1d_complexity('path', $GLOBALS['SITE_DB']->query_select('theme_images', array('DISTINCT path')));

    $themes = find_all_themes();
    foreach (array_keys($themes) as $theme) {
        $files_existing = get_image_paths(get_custom_base_url() . '/themes/' . rawurlencode($theme) . '/images_custom/', get_custom_file_base() . '/themes/' . $theme . '/images_custom/');

        foreach (array_keys($files_existing) as $path) {
            $path = str_replace(get_custom_file_base() . '/', '', filter_naughty($path));
            $encoded_path = substr($path, 0, strrpos($path, '/') + 1) . rawurlencode(substr($path, strrpos($path, '/') + 1));
            if ((!in_array($path, $files_referenced)) && (!in_array($encoded_path, $files_referenced)) && (($old_url == $path) || ($old_url == $encoded_path))) {
                @unlink(get_custom_file_base() . '/' . $path);
                sync_file($path);
            }
        }
    }
}
