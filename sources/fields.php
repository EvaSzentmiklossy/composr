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
 * @package    core_fields
 */

/**
 * Farm out the files for catalogue entry fields.
 */
function catalogue_file_script()
{
    // Closed site
    $site_closed = get_option('site_closed');
    if (($site_closed == '1') && (!has_privilege(get_member(), 'access_closed_site')) && (!$GLOBALS['IS_ACTUALLY_ADMIN'])) {
        header('Content-type: text/plain; charset=' . get_charset());
        @exit(get_option('closed'));
    }

    $file = filter_naughty(get_param_string('file', false, true));
    $_full = get_custom_file_base() . '/uploads/catalogues/' . filter_naughty(rawurldecode($file));
    if (!file_exists($_full)) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE', do_lang_tempcode('FILE')));
    }
    $size = filesize($_full);

    $original_filename = get_param_string('original_filename', null, true);

    // Security check; doesn't work for very old attachments (pre-v8)
    $table = get_param_string('table');
    if ($table != 'catalogue_efv_short' && $table != 'catalogue_efv_long' && $table != 'f_member_custom_fields') {
        access_denied('I_ERROR');
    }
    $entry_id = get_param_integer('id');
    $field_id = get_param_integer('field_id', null);
    $id_field = get_param_string('id_field');
    $field_id_field = get_param_string('field_id_field', null);
    $url_field = get_param_string('url_field');
    $ev = 'uploads/catalogues/' . $file;
    if ($original_filename !== null) {
        $ev .= '::' . $original_filename;
    } else {
        $original_filename = basename($file);
    }
    $where = array($id_field => $entry_id);
    if ($field_id_field !== null) {
        $where[$field_id_field] = $field_id;
    }
    $ev_check = $GLOBALS['SITE_DB']->query_select_value($table, $url_field, $where); // Has to return a result, will give a fatal error if not -- i.e. it implicitly checks the schema variables given
    if (!in_array($ev, explode("\n", preg_replace('#( |::).*$#m', '', $ev_check)))) {
        access_denied('I_ERROR'); // ID mismatch for the file requested, to give a security error
    }
    if (($table == 'catalogue_efv_short' || $table == 'catalogue_efv_long') && (get_ip_address() != cms_srv('SERVER_ADDR')/*We need to allow media renderer to get through*/)) { // Now check the match, if we support checking on it
        $c_name = $GLOBALS['SITE_DB']->query_select_value('catalogue_entries', 'c_name', array('id' => $entry_id));
        if (substr($c_name, 0, 1) != '_') { // Doesn't work on custom fields (this is documented)
            $cc_id = $GLOBALS['SITE_DB']->query_select_value('catalogue_entries', 'cc_id', array('id' => $entry_id));
            if (!has_category_access(get_member(), 'catalogues_catalogue', $c_name)) {
                access_denied('CATALOGUE_ACCESS');
            }
            if (!has_category_access(get_member(), 'catalogues_category', strval($cc_id))) {
                access_denied('CATEGORY_ACCESS');
            }
        }
        if (addon_installed('content_privacy')) {
            require_code('content_privacy');
            check_privacy('catalogue_entry', strval($entry_id));
        }
    }

    // Send header
    require_code('mime_types');
    header('Content-Type: ' . get_mime_type(get_file_extension($original_filename), false) . '; authoritative=true;');
    if ($original_filename !== null) {
        $original_filename = filter_naughty($original_filename);

        if ((strpos($original_filename, "\n") !== false) || (strpos($original_filename, "\r") !== false)) {
            log_hack_attack_and_exit('HEADER_SPLIT_HACK');
        }
        if (get_option('immediate_downloads', true) === '1' || get_param_integer('inline', 0) == 1) {
            require_code('mime_types');
            header('Content-Type: ' . get_mime_type(get_file_extension($original_filename), has_privilege($GLOBALS['SITE_DB']->query_select_value('catalogue_entries', 'ce_submitter', array('id' => $entry_id)), 'comcode_dangerous')) . '; authoritative=true;');
            header('Content-Disposition: inline; filename="' . escape_header($original_filename) . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . escape_header($original_filename) . '"');
        }
    } else {
        header('Content-Disposition: attachment');
    }
    header('Accept-Ranges: bytes');

    // Default to no resume
    $from = 0;
    $new_length = $size;

    safe_ini_set('zlib.output_compression', 'Off');

    // They're trying to resume (so update our range)
    $httprange = cms_srv('HTTP_RANGE');
    if (strlen($httprange) > 0) {
        $_range = explode('=', cms_srv('HTTP_RANGE'));
        if (count($_range) == 2) {
            if (strpos($_range[0], '-') === false) {
                $_range = array_reverse($_range);
            }
            $range = $_range[0];
            if (substr($range, 0, 1) == '-') {
                $range = strval($size - intval(substr($range, 1)) - 1) . $range;
            }
            if (substr($range, -1, 1) == '-') {
                $range .= strval($size - 1);
            }
            $bits = explode('-', $range);
            if (count($bits) == 2) {
                list($from, $to) = array_map('intval', $bits);
                if (($to - $from != 0) || ($from == 0)) { // Workaround to weird behaviour on Chrome
                    $new_length = $to - $from + 1;

                    header('HTTP/1.1 206 Partial Content');
                    header('Content-Range: bytes ' . $range . '/' . strval($size));
                } else {
                    $from = 0;
                }
            }
        }
    }
    header('Content-Length: ' . strval($new_length));
    if (php_function_allowed('set_time_limit')) {
        @set_time_limit(0);
    }
    error_reporting(0);

    if (cms_srv('REQUEST_METHOD') == 'HEAD') {
        return;
    }

    // Send actual data
    $myfile = fopen($_full, 'rb');
    fseek($myfile, $from);
    /*if ($size == $new_length)    Uses a lot of memory :S
    {
        fpassthru($myfile);
    } else*/
    {
        $i = 0;
        flush(); // Works around weird PHP bug that sends data before headers, on some PHP versions
        while ($i < $new_length) {
            $content = fread($myfile, min($new_length - $i, 1048576));
            echo $content;
            $len = strlen($content);
            if ($len == 0) {
                break;
            }
            $i += $len;
        }
        fclose($myfile);
    }
}

/**
 * Parse field options into a setting map and return a specific value from it.
 *
 * @param  array $field Field map
 * @param  string $name Field name
 * @param  string $default Field default value
 * @return string The value
 */
function option_value_from_field_array($field, $name, $default = '')
{
    if (empty($field['cf_options'])) {
        $options = array();
    } else {
        $options = parse_field_options($field['cf_options']);
    }
    if (empty($options[$name])) {
        return $default;
    }
    return $options[$name];
}

/**
 * Parse a field options string into a setting map.
 *
 * @param  string $__options Options string
 * @return array The setting map
 */
function parse_field_options($__options)
{
    $_options = ($__options == '') ? array() : explode(',', $__options);
    $options = array();
    foreach ($_options as $option) {
        if (trim($option) == '') {
            continue;
        }

        $parts = explode('=', trim($option), 2);
        if (!isset($parts[1])) {
            $parts[1] = 'on';
        }
        $options[$parts[0]] = $parts[1];
    }
    return $options;
}

/**
 * Ensure a catalogues fields are loaded up in a cache, and return them.
 *
 * @param  ?ID_TEXT $catalogue_name The name of the catalogue (null: all catalogues)
 * @return array The fields (empty array if the catalogue does not exist)
 */
function get_catalogue_fields($catalogue_name = null)
{
    global $CAT_FIELDS_CACHE;
    if (isset($CAT_FIELDS_CACHE[$catalogue_name])) {
        $fields = $CAT_FIELDS_CACHE[$catalogue_name];
    } else {
        $where = array();
        if ($catalogue_name !== null) {
            $where += array('c_name' => $catalogue_name);
        }
        $fields = $GLOBALS['SITE_DB']->query_select('catalogue_fields', array('*'), $where, 'ORDER BY cf_order,' . $GLOBALS['FORUM_DB']->translate_field_ref('cf_name'));
        $CAT_FIELDS_CACHE[$catalogue_name] = $fields;
    }
    return $fields;
}

/**
 * Get a fields hook, from a given codename.
 *
 * @param  ID_TEXT $type Codename
 * @return object Hook object
 */
function get_fields_hook($type)
{
    static $fields_hook_cache = array();
    if (isset($fields_hook_cache[$type])) {
        return $fields_hook_cache[$type];
    }

    $path = 'hooks/systems/fields/' . filter_naughty($type);
    if ((!/*common ones we know have hooks*/in_array($type, array('author', 'codename', 'color', 'content_link', 'date', 'email', 'float', 'guid', 'integer', 'just_date', 'just_time', 'list', 'long_text', 'long_trans', 'page_link', 'password', 'picture', 'video', 'posting_field', 'reference', 'short_text', 'short_trans', 'theme_image', 'tick', 'upload', 'url', 'member'))) && (!is_file(get_file_base() . '/sources/' . $path . '.php')) && (!is_file(get_file_base() . '/sources_custom/' . $path . '.php'))) {
        $hooks = find_all_hook_obs('systems', 'fields', 'Hook_fields_');
        foreach ($hooks as $ob) {
            if (method_exists($ob, 'get_field_types')) {
                if (array_key_exists($type, $ob->get_field_types($type))) {
                    $fields_hook_cache[$type] = $ob;
                    return $ob;
                }
            }
        }
    }
    require_code($path);
    $ob = object_factory('Hook_fields_' . filter_naughty($type));
    if ($ob === null) {
        return get_fields_hook('short_text');
    }
    $fields_hook_cache[$type] = $ob;
    return $ob;
}

/**
 * Get extra do-next icon for managing custom fields for a content type.
 *
 * @param  ID_TEXT $content_type Content type hook codename
 * @return array Extra do-next icon (single item array, or empty array if catalogues not installed)
 */
function manage_custom_fields_donext_link($content_type)
{
    if (addon_installed('catalogues')) {
        require_lang('fields');

        require_code('content');
        $ob = get_content_object($content_type);
        $info = $ob->info();

        if (($info['support_custom_fields']) && (has_privilege(get_member(), 'submit_cat_highrange_content', 'cms_catalogues')) && (has_privilege(get_member(), 'edit_cat_highrange_content', 'cms_catalogues'))) {
            $exists = ($GLOBALS['SITE_DB']->query_select_value_if_there('catalogues', 'c_name', array('c_name' => '_' . $content_type)) !== null);

            return array(
                array('menu/cms/catalogues/edit_one_catalogue', array('cms_catalogues', array('type' => $exists ? '_edit_catalogue' : 'add_catalogue', 'id' => '_' . $content_type, 'redirect' => get_self_url(true)), get_module_zone('cms_catalogues')), do_lang('EDIT_CUSTOM_FIELDS', do_lang($info['content_type_label']))),
            );
        }
    }

    return array();
}

/**
 * Get extra entry point data for managing custom fields for a content type.
 *
 * @param  ID_TEXT $content_type Content type hook codename
 * @return array Extra get_entry_points data
 */
function manage_custom_fields_entry_points($content_type)
{
    if (addon_installed('catalogues')) {
        require_lang('fields');

        require_code('content');
        $ob = get_content_object($content_type);
        $info = $ob->info();

        if (($info['support_custom_fields']) && (has_privilege(get_member(), 'submit_cat_highrange_content', 'cms_catalogues')) && (has_privilege(get_member(), 'edit_cat_highrange_content', 'cms_catalogues'))) {
            $count = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'COUNT(*)', array('c_name' => '_' . $content_type));
            $exists = ($count != 0);

            return array(
                '_SEARCH:cms_catalogues:' . ($exists ? '_edit_catalogue' : 'add_catalogue') . ':_' . $content_type => array(
                    do_lang_tempcode('ITEMS_HERE', do_lang_tempcode('EDIT_CUSTOM_FIELDS', do_lang($info['content_type_label'])), make_string_tempcode(escape_html(integer_format($count)))),
                    'menu/cms/catalogues/edit_one_catalogue'
                ),
            );
        }
    }

    return array();
}

/**
 * Find whether a content type has a tied catalogue.
 *
 * @param  ID_TEXT $content_type Content type hook codename
 * @return boolean Whether it has
 */
function has_tied_catalogue($content_type)
{
    if (addon_installed('catalogues')) {
        require_code('content');
        $ob = get_content_object($content_type);
        $info = $ob->info();
        if (($info !== null) && (array_key_exists('support_custom_fields', $info)) && ($info['support_custom_fields'])) {
            $exists = ($GLOBALS['SITE_DB']->query_select_value_if_there('catalogues', 'c_name', array('c_name' => '_' . $content_type)) !== null);
            if ($exists) {
                $first_cat = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_categories', 'MIN(id)', array('c_name' => '_' . $content_type));
                if ($first_cat === null) { // Repair needed, must have a category
                    require_code('catalogues2');
                    require_lang('catalogues');
                    actual_add_catalogue_category('_' . $content_type, do_lang('CUSTOM_FIELDS_FOR', do_lang($info['content_type_label'])), '', '', null);
                }

                return true;
            }
        }
    }
    return false;
}

/**
 * Get catalogue entry ID bound to a content entry.
 *
 * @param  ID_TEXT $content_type Content type hook codename
 * @param  ID_TEXT $id Content entry ID
 * @return ?AUTO_LINK Bound catalogue entry ID (null: none)
 */
function get_bound_content_entry($content_type, $id)
{
    if (!addon_installed('catalogues')) {
        return null;
    }

    // Optimisation: don't keep up looking custom field linkage if we have no custom fields
    static $content_type_has_custom_fields_cache = null;
    if ($content_type_has_custom_fields_cache === null) {
        $content_type_has_custom_fields_cache = persistent_cache_get('CONTENT_TYPE_HAS_CUSTOM_FIELDS_CACHE');
    }
    if ($content_type_has_custom_fields_cache === null) {
        $content_type_has_custom_fields_cache = array();
    }
    if (!array_key_exists($content_type, $content_type_has_custom_fields_cache)) {
        $content_type_has_custom_fields_cache[$content_type] = ($GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_fields', 'id', array(
            'c_name' => '_' . $content_type,
        )) !== null);
        persistent_cache_set('CONTENT_TYPE_HAS_CUSTOM_FIELDS_CACHE', $content_type_has_custom_fields_cache);
    }
    if (!$content_type_has_custom_fields_cache[$content_type]) {
        return;
    }
    return $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_entry_linkage', 'catalogue_entry_id', array(
        'content_type' => $content_type,
        'content_id' => $id,
    ));
}

/**
 * Append fields to content add/edit form for gathering custom fields.
 *
 * @param  ID_TEXT $content_type Content type hook codename
 * @param  ?ID_TEXT $id Content entry ID (null: new entry)
 * @param  Tempcode $fields Fields (passed by reference)
 * @param  Tempcode $hidden Hidden Fields (passed by reference)
 * @param  ?array $field_filter Limit fields to a set (null: no limit)
 * @param  boolean $field_filter_whitelist Whether $field_filter is a whitelist (if false, it is a blacklist)
 * @param  boolean $add_separate_header Whether to add a separate header above the fields, so long as not all the fields are already under some other header
 */
function append_form_custom_fields($content_type, $id, &$fields, &$hidden, $field_filter = null, $field_filter_whitelist = true, $add_separate_header = false)
{
    if (!addon_installed('catalogues')) {
        return;
    }

    require_code('catalogues');

    $catalogue_entry_id = get_bound_content_entry($content_type, $id);
    if ($catalogue_entry_id !== null) {
        $special_fields = get_catalogue_entry_field_values('_' . $content_type, $catalogue_entry_id);
    } else {
        $special_fields = $GLOBALS['SITE_DB']->query_select('catalogue_fields', array('*'), array('c_name' => '_' . $content_type), 'ORDER BY cf_order,' . $GLOBALS['SITE_DB']->translate_field_ref('cf_name'));
    }

    $field_groups = array();

    require_code('fields');
    foreach ($special_fields as $field_num => $field) {
        if (($field_filter !== null) && ($field_filter_whitelist) && (!in_array($field['id'], $field_filter))) {
            continue;
        }
        if (($field_filter !== null) && (!$field_filter_whitelist) && (in_array($field['id'], $field_filter))) {
            continue;
        }

        $ob = get_fields_hook($field['cf_type']);
        $default = get_param_string('field_' . strval($field['id']), array_key_exists('effective_value_pure', $field) ? $field['effective_value_pure'] : $field['cf_default']);

        $_cf_name = get_translated_text($field['cf_name']);
        $field_cat = '';
        $matches = array();
        if (strpos($_cf_name, ': ') !== false) {
            $field_cat = substr($_cf_name, 0, strpos($_cf_name, ': '));
            if ($field_cat . ': ' == $_cf_name) {
                $_cf_name = $field_cat; // Just been pulled out as heading, nothing after ": "
            } else {
                $_cf_name = substr($_cf_name, strpos($_cf_name, ': ') + 2);
            }
        }
        if (!array_key_exists($field_cat, $field_groups)) {
            $field_groups[$field_cat] = new Tempcode();
        }

        $_cf_description = escape_html(get_translated_text($field['cf_description']));

        $GLOBALS['NO_DEV_MODE_FULLSTOP_CHECK'] = true;
        $result = $ob->get_field_inputter($_cf_name, $_cf_description, $field, $default, true, !array_key_exists($field_num + 1, $special_fields));
        $GLOBALS['NO_DEV_MODE_FULLSTOP_CHECK'] = false;

        if ($result === null) {
            continue;
        }

        if (is_array($result)) {
            $field_groups[$field_cat]->attach($result[0]);
            $hidden->attach($result[1]);
        } else {
            $field_groups[$field_cat]->attach($result);
        }

        unset($result);
        unset($ob);
    }

    if (array_key_exists('', $field_groups)) { // Blank prefix must go first
        $field_groups_blank = $field_groups[''];
        unset($field_groups['']);
        $field_groups = array_merge(array($field_groups_blank), $field_groups);
    }

    if ($add_separate_header) {
        $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => '9ebf9c2c66923907b561364c37224728', 'TITLE' => do_lang_tempcode('MORE'))));
    }
    foreach ($field_groups as $field_group_title => $extra_fields) {
        if (is_integer($field_group_title)) {
            $field_group_title = ($field_group_title == 0) ? '' : strval($field_group_title);
        }

        if ($field_group_title != '') {
            $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => '58937f03882cc09276fa100933eb6041', 'TITLE' => $field_group_title)));
        }
        $fields->attach($extra_fields);
    }
}

/**
 * Save custom fields to a content item.
 *
 * @param  ID_TEXT $content_type Content type hook codename
 * @param  ID_TEXT $id Content entry ID
 * @param  ?ID_TEXT $old_id Content entry ID (prior to possible rename) (null: definitely unchanged)
 */
function save_form_custom_fields($content_type, $id, $old_id = null)
{
    if (!addon_installed('catalogues')) {
        return;
    }

    if (fractional_edit()) {
        return;
    }

    if ($old_id === null) {
        $old_id = $id;
    }

    $existing = get_bound_content_entry($content_type, $old_id);

    require_code('catalogues');

    // Get field values
    $fields = $GLOBALS['SITE_DB']->query_select('catalogue_fields', array('*'), array('c_name' => '_' . $content_type), 'ORDER BY cf_order,' . $GLOBALS['SITE_DB']->translate_field_ref('cf_name'));
    $map = array();
    require_code('fields');
    foreach ($fields as $field) {
        $ob = get_fields_hook($field['cf_type']);

        list(, , $storage_type) = $ob->get_field_value_row_bits($field);

        $value = $ob->inputted_to_field_value($existing !== null, $field, 'uploads/catalogues', ($existing === null) ? null : _get_catalogue_entry_field($field['id'], $existing, $storage_type));

        $map[$field['id']] = $value;
    }
    if (count($fields) == 0) {
        return;
    }

    $first_cat = $GLOBALS['SITE_DB']->query_select_value('catalogue_categories', 'MIN(id)', array('c_name' => '_' . $content_type));
    if ($first_cat === null) {
        require_code('catalogues2');
        $first_cat = actual_add_catalogue_category('_' . $content_type, do_lang('DEFAULT'), '', '', null);
    }

    require_code('catalogues2');

    if ($existing !== null) {
        actual_edit_catalogue_entry($existing, $first_cat, 1, '', 0, 0, 0, $map);
    } else {
        $catalogue_entry_id = actual_add_catalogue_entry($first_cat, 1, '', 0, 0, 0, $map);

        $GLOBALS['SITE_DB']->query_insert('catalogue_entry_linkage', array(
            'catalogue_entry_id' => $catalogue_entry_id,
            'content_type' => $content_type,
            'content_id' => $id,
        ));
    }
}

/**
 * Delete custom fields for content item.
 *
 * @param  ID_TEXT $content_type Content type hook codename
 * @param  ID_TEXT $id Content entry ID
 */
function delete_form_custom_fields($content_type, $id)
{
    if (!addon_installed('catalogues')) {
        return;
    }

    require_code('catalogues2');

    $existing = get_bound_content_entry($content_type, $id);
    if ($existing !== null) {
        actual_delete_catalogue_entry($existing);

        $GLOBALS['SITE_DB']->query_delete('catalogue_entry_linkage', array(
            'catalogue_entry_id' => $existing,
        ));
    }
}

/**
 * Get a list of all field types to choose from.
 *
 * @param  ID_TEXT $type Field type to select
 * @param  boolean $limit_to_storage_set Whether to only show options in the same storage set as $type
 * @return Tempcode List of field types
 */
function create_selection_list_field_type($type = '', $limit_to_storage_set = false)
{
    static $cache = array();
    $cache_sig = serialize(array($type, $limit_to_storage_set));
    if (isset($cache[$cache_sig])) {
        return $cache[$cache_sig];
    }

    $do_caching = has_caching_for('block');

    $ret = mixed();
    if ($do_caching) {
        $cache_identifier = $cache_sig;
        $ret = get_cache_entry('_field_type_selection', $cache_identifier, CACHE_AGAINST_NOTHING_SPECIAL, 10000);

        if ($ret !== null) {
            $cache[$cache_sig] = $ret;
            return $ret;
        }
    }

    require_lang('fields');

    $all_types = find_all_hooks('systems', 'fields');
    if ($limit_to_storage_set) { // Already set, so we need to do a search to see what we can limit our types to (things with the same backend DB storage)
        $ob = get_fields_hook($type);
        $types = array();
        list(, , $db_type) = $ob->get_field_value_row_bits(null);
        foreach ($all_types as $this_type => $hook_type) {
            $ob = get_fields_hook($this_type);
            list(, , $this_db_type) = $ob->get_field_value_row_bits(null);

            if ($this_db_type == $db_type) {
                $types[$this_type] = $hook_type;
            }
        }
    } else {
        $types = $all_types;
    }
    $orderings = array(
        do_lang_tempcode('FIELD_TYPES__TEXT'), 'short_trans', 'short_trans_multi', 'short_text', 'short_text_multi', 'long_trans', 'long_text', 'posting_field', 'codename', 'password', 'email',
        do_lang_tempcode('FIELD_TYPES__NUMBERS'), 'integer', 'float',
        do_lang_tempcode('FIELD_TYPES__CHOICES'), 'list', 'list_multi', 'tick',
        do_lang_tempcode('FIELD_TYPES__UPLOADSANDURLS'), 'upload', 'upload_multi', 'picture', 'picture_multi', 'video', 'video_multi', 'url', 'url_multi', 'page_link', 'theme_image',
        do_lang_tempcode('FIELD_TYPES__MAGIC'), 'guid',
        do_lang_tempcode('FIELD_TYPES__REFERENCES'), 'isbn', 'reference', 'reference_multi', 'content_link', 'content_link_multi', 'member', 'member_multi', 'author',
        //do_lang_tempcode('FIELD_TYPES__OTHER'), 'color', 'date', 'just_date', 'just_time', 'tel',       Will go under OTHER automatically
    );
    $_types = array();
    $done_one_in_section = true;
    foreach ($orderings as $o) {
        if (is_object($o)) {
            if (!$done_one_in_section) {
                array_pop($_types);
            }
            $_types[] = $o;
            $done_one_in_section = false;
        } else {
            if (isset($types[$o])) {
                $_types[] = $o;
                unset($types[$o]);
                $done_one_in_section = true;
            }
        }
    }
    if (!$done_one_in_section) {
        array_pop($_types);
    }
    if (count($types) != 0) {
        $types = array_merge($_types, array(do_lang_tempcode('FIELD_TYPES__OTHER')), array_keys($types));
    } else {
        $types = $_types;
    }
    $_type_list = '';
    $type_list = new Tempcode();
    $last_type = do_lang_tempcode('OTHER');
    foreach ($types as $_type) {
        if (is_object($_type)) {
            if ($_type_list !== '') {
                $type_list->attach(form_input_list_group($last_type, make_string_tempcode($_type_list)));
            }
            $_type_list = '';
            $last_type = $_type;
        } else {
            $ob = get_fields_hook($_type);
            if (method_exists($ob, 'get_field_types')) {
                $sub_types = $ob->get_field_types();
            } else {
                $sub_types = array($_type => do_lang_tempcode('FIELD_TYPE_' . $_type));
            }

            foreach ($sub_types as $__type => $_title) {
                //$_type_list->attach(form_input_list_entry($__type, ($__type == $type), $_title));
                $_type_list .= '<option value="' . escape_html($__type) . '"' . ($__type == $type ? ' selected="selected"' : '') . '>' . $_title->evaluate() . '</option>'; // XHTMLXHTML
            }
        }
    }
    if ($_type_list !== '') {
        $type_list->attach(form_input_list_group($last_type, make_string_tempcode($_type_list)));
    }

    $ret = make_string_tempcode($type_list->evaluate()); // XHTMLXHTML

    $cache[$cache_sig] = $ret;

    if ($do_caching) {
        require_code('caches2');

        $ret = apply_quick_caching($ret);

        put_into_cache('_field_type_selection', 60 * 60 * 24, $cache_identifier, null, null, '', null, '', $ret);
    }

    return $ret;
}
