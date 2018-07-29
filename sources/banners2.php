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
 * @package    banners
 */

/**
 * Get Tempcode for a banner type 'feature box' for the given row.
 *
 * @param  array $row The database field row of it
 * @param  ID_TEXT $zone The zone to use
 * @param  boolean $give_context Whether to include context (i.e. say WHAT this is, not just show the actual content)
 * @param  ID_TEXT $guid Overridden GUID to send to templates (blank: none)
 * @return Tempcode A box for it, linking to the full page
 */
function render_banner_type_box($row, $zone = '_SEARCH', $give_context = true, $guid = '')
{
    if ($row === null) { // Should never happen, but we need to be defensive
        return new Tempcode();
    }

    require_lang('banners');

    $url = new Tempcode();

    $_title = $row['id'];
    if ($_title == '') {
        $_title = do_lang('_DEFAULT');
    }
    $title = $give_context ? do_lang('CONTENT_IS_OF_TYPE', do_lang('BANNER_TYPE'), $_title) : $_title;

    $num_entries = $GLOBALS['SITE_DB']->query_select_value('banners', 'COUNT(*)', array('validated' => 1));
    $entry_details = do_lang_tempcode('CATEGORY_SUBORDINATE_2', escape_html(integer_format($num_entries)));

    return do_template('SIMPLE_PREVIEW_BOX', array(
        '_GUID' => ($guid != '') ? $guid : 'ba1f8d9da6b65415483d0d235f29c3d4',
        'ID' => $row['id'],
        'TITLE' => $title,
        'SUMMARY' => '',
        'ENTRY_DETAILS' => $entry_details,
        'URL' => $url,
        'RESOURCE_TYPE' => 'banner_type',
    ));
}

/**
 * Get Tempcode for a banner 'feature box' for the given row.
 *
 * @param  array $row The database field row of it
 * @param  ID_TEXT $zone The zone to use
 * @param  boolean $give_context Whether to include context (i.e. say WHAT this is, not just show the actual content)
 * @param  ID_TEXT $guid Overridden GUID to send to templates (blank: none)
 * @return Tempcode A box for it, linking to the full page
 */
function render_banner_box($row, $zone = '_SEARCH', $give_context = true, $guid = '')
{
    if ($row === null) { // Should never happen, but we need to be defensive
        return new Tempcode();
    }

    require_lang('banners');
    require_code('banners');

    $just_banner_row = db_map_restrict($row, array('name', 'caption'));

    $url = new Tempcode();

    $_title = $row['name'];
    $title = $give_context ? do_lang('CONTENT_IS_OF_TYPE', do_lang('BANNER'), $_title) : $_title;

    $summary = show_banner($row['name'], $row['title_text'], get_translated_tempcode('banners', $just_banner_row, 'caption'), $row['direct_code'], $row['img_url'], '', $row['site_url'], $row['b_type'], $row['submitter']);

    return do_template('SIMPLE_PREVIEW_BOX', array(
        '_GUID' => ($guid != '') ? $guid : 'aaea5f7f64297ab46aa3b3182fb57c37',
        'ID' => $row['name'],
        'TITLE' => $title,
        'TITLE_PLAIN' => $_title,
        'SUMMARY' => $summary,
        'URL' => $url,
        'FRACTIONAL_EDIT_FIELD_NAME' => $give_context ? null : 'name',
        'FRACTIONAL_EDIT_FIELD_URL' => $give_context ? null : '_SEARCH:cms_banners:__edit:' . $row['name'],
        'RESOURCE_TYPE' => 'banner',
    ));
}

/**
 * Get a nice, formatted XHTML list to select a banner type.
 *
 * @param  ?mixed $it The currently selected banner type (null: none selected)
 * @return Tempcode The list of banner types
 */
function create_selection_list_banner_types($it = null)
{
    if (is_string($it)) {
        $it = array($it);
    }

    $list = new Tempcode();
    $rows = $GLOBALS['SITE_DB']->query_select('banner_types', array('id', 't_image_width', 't_image_height', 't_is_textual'), array(), 'ORDER BY id');
    foreach ($rows as $row) {
        $caption = ($row['id'] == '') ? do_lang('_DEFAULT') : $row['id'];

        if ($row['t_is_textual'] == 1) {
            $type_line = do_lang_tempcode('BANNER_TYPE_LINE_TEXTUAL', escape_html($caption));
        } else {
            $type_line = do_lang_tempcode('BANNER_TYPE_LINE', escape_html($caption), escape_html(strval($row['t_image_width'])), escape_html(strval($row['t_image_height'])));
        }

        $list->attach(form_input_list_entry($row['id'], in_array($row['id'], $it), $type_line));
    }
    return $list;
}

/**
 * Get a list of banners.
 *
 * @param  ?ID_TEXT $it The ID of the banner selected by default (null: no specific default)
 * @param  ?MEMBER $only_owned Only show banners owned by the member (null: no such restriction)
 * @return Tempcode The list
 */
function create_selection_list_banners($it = null, $only_owned = null)
{
    $where = ($only_owned === null) ? null : array('submitter' => $only_owned);
    $rows = $GLOBALS['SITE_DB']->query_select('banners', array('name'), $where, 'ORDER BY name', 150);
    if (count($rows) == 300) {
        $rows = $GLOBALS['SITE_DB']->query_select('banners', array('name'), $where, 'ORDER BY add_date DESC', 150);
    }
    $out = new Tempcode();
    foreach ($rows as $myrow) {
        $selected = ($myrow['name'] == $it);
        $out->attach(form_input_list_entry($myrow['name'], $selected));
    }

    return $out;
}
/**
 * Get the Tempcode for the form to add a banner, with the information passed along to it via the parameters already added in.
 *
 * @param  boolean $simplified Whether to simplify the banner interface (for the purchase process)
 * @param  ID_TEXT $name The name of the banner
 * @param  URLPATH $image_url The URL to the banner image
 * @param  URLPATH $site_url The URL to the site the banner leads to
 * @param  SHORT_TEXT $caption The caption of the banner
 * @param  LONG_TEXT $direct_code Complete HTML/PHP for the banner
 * @param  LONG_TEXT $notes Any notes associated with the banner
 * @param  integer $display_likelihood The banner's "Display likelihood"
 * @range  1 max
 * @param  ?integer $campaign_remaining The number of hits the banner may have (null: not applicable for this banner type)
 * @range  0 max
 * @param  SHORT_INTEGER $deployment_agreement The type of banner (0=permanent, 1=campaign, 2=fallback)
 * @set 0 1 2
 * @param  ?TIME $expiry_date The banner expiry date (null: never expires)
 * @param  ?MEMBER $submitter The banners submitter (null: current member)
 * @param  BINARY $validated Whether the banner has been validated
 * @param  ID_TEXT $b_type The banner type (can be anything, where blank means 'normal')
 * @param  array $b_types The secondary banner types (empty: no secondary banner types)
 * @param  array $regions The regions (empty: not region-limited)
 * @param  SHORT_TEXT $title_text The title text for the banner (only used for text banners, and functions as the 'trigger text' if the banner type is shown inline)
 * @return array A pair: The input field Tempcode, JavaScript code
 */
function get_banner_form_fields($simplified = false, $name = '', $image_url = '', $site_url = '', $caption = '', $direct_code = '', $notes = '', $display_likelihood = 3, $campaign_remaining = 50, $deployment_agreement = 1, $expiry_date = null, $submitter = null, $validated = 1, $b_type = '', $b_types = array(), $regions = array(), $title_text = '')
{
    require_code('images');

    $fields = new Tempcode();

    require_code('form_templates');

    $fields->attach(form_input_codename(do_lang_tempcode('CODENAME'), do_lang_tempcode('DESCRIPTION_BANNER_NAME'), 'name', $name, true));

    $fields->attach(form_input_line(do_lang_tempcode('DESTINATION_URL'), do_lang_tempcode('DESCRIPTION_BANNER_URL'), 'site_url', $site_url, false)); // Blank implies iframe or direct code

    if (!$simplified) {
        $types = create_selection_list_banner_types($b_type);
        if ($types->is_empty()) {
            warn_exit(do_lang_tempcode('NO_CATEGORIES', 'banner_type'));
        }
        $fields->attach(form_input_list(do_lang_tempcode('BANNER_TYPE'), do_lang_tempcode('_DESCRIPTION_BANNER_TYPE'), 'b_type', $types, null, false, false));

        $image_banner = false; // Not known
    } else {
        $fields->attach(form_input_hidden('b_type', $b_type));

        $image_banner = $GLOBALS['SITE_DB']->query_select_value_if_there('banner_types', 't_is_textual', array('id' => $b_type)) === 0;
    }

    if (get_option('enable_staff_notes') == '1') {
        $fields->attach(form_input_text(do_lang_tempcode('NOTES'), do_lang_tempcode('DESCRIPTION_NOTES'), 'notes', $notes, false));
    }

    if (has_privilege(get_member(), 'bypass_validation_midrange_content', 'cms_banners')) {
        if ($validated == 0) {
            $validated = get_param_integer('validated', 0);
            if (($validated == 1) && (addon_installed('unvalidated'))) {
                attach_message(do_lang_tempcode('WILL_BE_VALIDATED_WHEN_SAVING'));
            }
        }
        if (addon_installed('unvalidated')) {
            $fields->attach(form_input_tick(do_lang_tempcode('VALIDATED'), do_lang_tempcode($GLOBALS['FORUM_DRIVER']->is_super_admin(get_member()) ? 'DESCRIPTION_VALIDATED_SIMPLE' : 'DESCRIPTION_VALIDATED', 'banner'), 'validated', $validated == 1));
        }
    }

    $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => 'b110d585eea7d6e29dab4870c5a15c4a', 'TITLE' => do_lang_tempcode('SOURCE_MEDIA'))));

    $set_name = 'media';
    $required = false;
    $set_title = do_lang_tempcode('MEDIA');
    $field_set = alternate_fields_set__start($set_name);

    $field_set->attach(form_input_upload(do_lang_tempcode('UPLOAD'), do_lang_tempcode('DESCRIPTION_UPLOAD'), 'file', false, null, null, true, get_allowed_image_file_types()));

    $field_set->attach(form_input_line(do_lang_tempcode('IMAGE_URL'), do_lang_tempcode('DESCRIPTION_URL_BANNER'), 'image_url', $image_url, false));

    if (!$image_banner) {
        $field_set->attach(form_input_line_comcode(do_lang_tempcode('BANNER_TITLE_TEXT'), do_lang_tempcode('DESCRIPTION_BANNER_TITLE_TEXT'), 'title_text', $title_text, false));
    }

    if (has_privilege(get_member(), 'use_html_banner')) {
        $field_set->attach(form_input_text(do_lang_tempcode('BANNER_DIRECT_CODE'), do_lang_tempcode('DESCRIPTION_BANNER_DIRECT_CODE'), 'direct_code', $direct_code, false));
    }

    $fields->attach(alternate_fields_set__end($set_name, $set_title, '', $field_set, $required));

    $fields->attach(form_input_line_comcode(do_lang_tempcode('DESCRIPTION'), do_lang_tempcode($image_banner ? 'DESCRIPTION_BANNER_DESCRIPTION_SIMPLE' : 'DESCRIPTION_BANNER_DESCRIPTION'), 'caption', $caption, false));

    if (!$simplified) {
        $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => '1184532268cd8a58adea01c3637dc4c5', 'TITLE' => do_lang_tempcode('DEPLOYMENT_DETERMINATION'))));

        if (has_privilege(get_member(), 'full_banner_setup')) {
            $radios = new Tempcode();
            $radios->attach(form_input_radio_entry('deployment_agreement', strval(BANNER_PERMANENT), ($deployment_agreement == BANNER_PERMANENT), do_lang_tempcode('BANNER_PERMANENT')));
            $radios->attach(form_input_radio_entry('deployment_agreement', strval(BANNER_CAMPAIGN), ($deployment_agreement == BANNER_CAMPAIGN), do_lang_tempcode('BANNER_CAMPAIGN')));
            $radios->attach(form_input_radio_entry('deployment_agreement', strval(BANNER_FALLBACK), ($deployment_agreement == BANNER_FALLBACK), do_lang_tempcode('BANNER_FALLBACK')));
            $fields->attach(form_input_radio(do_lang_tempcode('DEPLOYMENT_AGREEMENT'), do_lang_tempcode('DESCRIPTION_BANNER_TYPE'), 'deployment_agreement', $radios));
            $fields->attach(form_input_integer(do_lang_tempcode('HITS_ALLOCATED'), do_lang_tempcode('DESCRIPTION_HITS_ALLOCATED'), 'campaign_remaining', $campaign_remaining, false));
            $total_importance = $GLOBALS['SITE_DB']->query_value_if_there('SELECT SUM(display_likelihood) FROM ' . get_table_prefix() . 'banners WHERE ' . db_string_not_equal_to('name', $name));
            $fields->attach(form_input_integer(do_lang_tempcode('DISPLAY_LIKELIHOOD'), do_lang_tempcode('DESCRIPTION_DISPLAY_LIKELIHOOD', strval($total_importance), strval($display_likelihood)), 'display_likelihood', $display_likelihood, true));
        }

        $fields->attach(form_input_date(do_lang_tempcode('EXPIRY_DATE'), do_lang_tempcode('DESCRIPTION_EXPIRY_DATE'), 'expiry_date', false, ($expiry_date === null), true, $expiry_date, 2));

        $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => '6df9d181757c40237d9459b06075de97', 'TITLE' => do_lang_tempcode('ADVANCED'), 'SECTION_HIDDEN' => empty($b_types) && empty($regions))));

        $fields->attach(form_input_multi_list(do_lang_tempcode('SECONDARY_CATEGORIES'), '', 'b_types', create_selection_list_banner_types($b_types)));

        if (get_option('filter_regions') == '1') {
            require_code('locations');
            $fields->attach(form_input_regions($regions));
        }
    }

    require_javascript('banners');
    $js_function_calls = array('getBannerFormFields');

    return array($fields, $js_function_calls);
}

/**
 * Check the uploaded banner is valid.
 *
 * @param  SHORT_TEXT $title_text The title text for the banner (only used for text banners, and functions as the 'trigger text' if the banner type is shown inline)
 * @param  LONG_TEXT $direct_code Complete HTML/PHP for the banner
 * @param  ID_TEXT $b_type The banner type (can be anything, where blank means 'normal')
 * @param  array $b_types The secondary banner types (empty: no secondary banner types)
 * @param  string $url_param_name Param name for possible URL field
 * @param  string $file_param_name Param name for possible upload field
 * @return array A pair: The URL, and the title text
 */
function check_banner($title_text = '', $direct_code = '', $b_type = '', $b_types = array(), $url_param_name = 'image_url', $file_param_name = 'file')
{
    require_code('uploads');
    $is_upload = (is_plupload()) || (array_key_exists($file_param_name, $_FILES)) && (array_key_exists('tmp_name', $_FILES[$file_param_name]) && (is_uploaded_file($_FILES[$file_param_name]['tmp_name'])));

    $url = '';

    // Find banner type details
    $_banner_type_rows = $GLOBALS['SITE_DB']->query_select('banner_types', array('*'), array('id' => $b_type), '', 1);
    if (!array_key_exists(0, $_banner_type_rows)) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'banner_type'));
    }
    $banner_type_row = $_banner_type_rows[0];

    // Check banner types all match up
    if (!empty($b_types)) {
        $sql = 'SELECT * FROM ' . get_table_prefix() . 'banner_types WHERE id IN (\'' . implode('\',\'', $b_types) . '\')';
        $sql .= ' AND (t_image_width<>' . strval($banner_type_row['t_image_width']) . ' OR t_image_height<>' . strval($banner_type_row['t_image_height']) . ' OR t_is_textual<>' . strval($banner_type_row['t_is_textual']) . ')';
        $test = $GLOBALS['SITE_DB']->query($sql);
        if (!empty($test)) {
            warn_exit(do_lang_tempcode('INCONSISTENT_BANNER_SET'));
        }
    }

    // Check according to banner type
    if ($banner_type_row['t_is_textual'] == 0) {
        if ($direct_code == '') {
            $urls = get_url($url_param_name, $file_param_name, 'uploads/banners', 0, $is_upload ? CMS_UPLOAD_IMAGE : CMS_UPLOAD_ANYTHING);
            $url = fixup_protocolless_urls($urls[0]);
            if ($url == '') {
                warn_exit(do_lang_tempcode('IMPROPERLY_FILLED_IN_UPLOAD_BANNERS'));
            }

            // Check width, height, size
            $test_url = $url;
            if (url_is_local($test_url)) {
                $data = file_get_contents(get_custom_file_base() . '/' . rawurldecode($test_url));
                $test_url = get_custom_base_url() . '/' . $test_url;
            } else {
                $data = http_get_contents($test_url);
            }
            if (strlen($data) > $banner_type_row['t_max_file_size'] * 1024) {
                if (url_is_local($test_url)) {
                    @unlink(get_custom_file_base() . '/' . rawurldecode($test_url));
                    sync_file(rawurldecode($test_url));
                }
                warn_exit(do_lang_tempcode('BANNER_TOO_LARGE', escape_html(integer_format(intval(ceil(strlen($data) / 1024)))), escape_html(integer_format($banner_type_row['t_max_file_size']))));
            }
            if (function_exists('imagetypes')) {
                require_code('images');
                if (is_image($test_url, IMAGE_CRITERIA_GD_READ)) {
                    $test = cms_getimagesize($test_url, get_file_extension($test_url));

                    if ($test === false) {
                        if (url_is_local($url)) {
                            @unlink(get_custom_file_base() . '/' . rawurldecode($url));
                            sync_file(rawurldecode($url));
                        }
                        warn_exit(do_lang_tempcode('CORRUPT_FILE', escape_html($url)));
                    }

                    list($sx, $sy) = $test;

                    if ((get_option('banner_autosize') != '1') && (($sx != $banner_type_row['t_image_width']) || ($sy != $banner_type_row['t_image_height']))) {
                        if (url_is_local($test_url)) {
                            @unlink(get_custom_file_base() . '/' . rawurldecode($test_url));
                            sync_file(rawurldecode($test_url));
                        }
                        warn_exit(do_lang_tempcode('BANNER_RES_BAD', escape_html(integer_format($banner_type_row['t_image_width'])), escape_html(integer_format($banner_type_row['t_image_height']))));
                    }
                }
            }
        } else {
            check_privilege('use_html_banner');
            if (strpos($direct_code, '<?') !== false) {
                check_privilege('use_php_banner');
                if ($GLOBALS['CURRENT_SHARE_USER'] !== null) {
                    warn_exit(do_lang_tempcode('SHARED_INSTALL_PROHIBIT'));
                }
            }
        }
    } else {
        if ($title_text == '') {
            warn_exit(do_lang_tempcode('IMPROPERLY_FILLED_IN_BANNERS'));
        }

        if (strlen($title_text) > $banner_type_row['t_max_file_size']) {
            warn_exit(do_lang_tempcode('BANNER_TOO_LARGE_2', escape_html(integer_format(strlen($title_text))), escape_html(integer_format($banner_type_row['t_max_file_size']))));
        }
    }

    return array($url, $title_text);
}

/**
 * Add a banner to the database, and return the new ID of that banner in the database.
 *
 * @param  ID_TEXT $name The name of the banner
 * @param  URLPATH $imgurl The URL to the banner image
 * @param  SHORT_TEXT $title_text The title text for the banner (only used for text banners, and functions as the 'trigger text' if the banner type is shown inline)
 * @param  SHORT_TEXT $caption The caption of the banner
 * @param  LONG_TEXT $direct_code Complete HTML/PHP for the banner
 * @param  ?integer $campaign_remaining The number of hits the banner may have (null: not applicable for this banner type)
 * @range  0 max
 * @param  URLPATH $site_url The URL to the site the banner leads to
 * @param  integer $display_likelihood The banner's "Display likelihood"
 * @range  1 max
 * @param  LONG_TEXT $notes Any notes associated with the banner
 * @param  SHORT_INTEGER $deployment_agreement The type of banner (a BANNER_* constant)
 * @set 0 1 2
 * @param  ?TIME $expiry_date The banner expiry date (null: never)
 * @param  ?MEMBER $submitter The banners submitter (null: current member)
 * @param  BINARY $validated Whether the banner has been validated
 * @param  ID_TEXT $b_type The banner type (can be anything, where blank means 'normal')
 * @param  array $b_types The secondary banner types (empty: no secondary banner types)
 * @param  array $regions The regions (empty: not region-limited)
 * @param  ?TIME $time The time the banner was added (null: now)
 * @param  integer $hits_from The number of return hits from this banners site
 * @param  integer $hits_to The number of banner hits to this banners site
 * @param  integer $views_from The number of return views from this banners site
 * @param  integer $views_to The number of banner views to this banners site
 * @param  ?TIME $edit_date The banner edit date (null: never)
 * @param  boolean $uniqify Whether to force the name as unique, if there's a conflict
 * @return ID_TEXT The name
 */
function add_banner($name, $imgurl, $title_text, $caption, $direct_code, $campaign_remaining, $site_url, $display_likelihood, $notes, $deployment_agreement, $expiry_date, $submitter, $validated = 0, $b_type = '', $b_types = array(), $regions = array(), $time = null, $hits_from = 0, $hits_to = 0, $views_from = 0, $views_to = 0, $edit_date = null, $uniqify = false)
{
    if ($campaign_remaining === null) {
        $campaign_remaining = 0;
    }

    if ($time === null) {
        $time = time();
    }
    if ($submitter === null) {
        $submitter = get_member();
    }

    $test = $GLOBALS['SITE_DB']->query_select_value_if_there('banners', 'name', array('name' => $name));
    if ($test !== null) {
        if ($uniqify) {
            $name .= '_' . uniqid('', false);
        } else {
            warn_exit(do_lang_tempcode('ALREADY_EXISTS', escape_html($name)));
        }
    }

    if (!addon_installed('unvalidated')) {
        $validated = 1;
    }
    $map = array(
        'name' => $name,
        'title_text' => $title_text,
        'direct_code' => $direct_code,
        'b_type' => $b_type,
        'edit_date' => $edit_date,
        'add_date' => $time,
        'expiry_date' => $expiry_date,
        'deployment_agreement' => $deployment_agreement,
        'submitter' => $submitter,
        'img_url' => $imgurl,
        'campaign_remaining' => $campaign_remaining,
        'site_url' => $site_url,
        'display_likelihood' => $display_likelihood,
        'notes' => $notes,
        'validated' => $validated,
        'hits_from' => $hits_from,
        'hits_to' => $hits_to,
        'views_from' => $views_from,
        'views_to' => $views_to,
    );
    $map += insert_lang_comcode('caption', $caption, 2);
    $GLOBALS['SITE_DB']->query_insert('banners', $map);

    foreach ($b_types as $b_type_sup) {
        $GLOBALS['SITE_DB']->query_insert('banners_types', array('name' => $name, 'b_type' => $b_type_sup));
    }

    foreach ($regions as $region) {
        $GLOBALS['SITE_DB']->query_insert('content_regions', array('content_type' => 'banner', 'content_id' => $name, 'region' => $region));
    }

    delete_cache_entry('main_banner_wave');
    delete_cache_entry('main_top_sites');

    reorganise_uploads__banners(array('name' => $name));

    log_it('ADD_BANNER', $name, $caption);

    if ((addon_installed('commandr')) && (!running_script('install'))) {
        require_code('resource_fs');
        generate_resource_fs_moniker('banner', $name, null, null, true);
    }

    require_code('member_mentions');
    dispatch_member_mention_notifications('banner', $name, $submitter);

    return $name;
}

/**
 * Edit a banner.
 *
 * @param  ID_TEXT $old_name The current name of the banner
 * @param  ID_TEXT $name The new name of the banner
 * @param  URLPATH $imgurl The URL to the banner image
 * @param  SHORT_TEXT $title_text The title text for the banner (only used for text banners, and functions as the 'trigger text' if the banner type is shown inline)
 * @param  SHORT_TEXT $caption The caption of the banner
 * @param  LONG_TEXT $direct_code Complete HTML/PHP for the banner
 * @param  ?integer $campaign_remaining The number of hits the banner may have (null: not applicable for this banner type)
 * @range  0 max
 * @param  URLPATH $site_url The URL to the site the banner leads to
 * @param  integer $display_likelihood The banner's "Display likelihood"
 * @range  1 max
 * @param  LONG_TEXT $notes Any notes associated with the banner
 * @param  SHORT_INTEGER $deployment_agreement The type of banner (a BANNER_* constant)
 * @set 0 1 2
 * @param  ?TIME $expiry_date The banner expiry date (null: never)
 * @param  ?MEMBER $submitter The banners submitter (null: leave unchanged)
 * @param  BINARY $validated Whether the banner has been validated
 * @param  ID_TEXT $b_type The banner type (can be anything, where blank means 'normal')
 * @param  array $b_types The secondary banner types (empty: no secondary banner types)
 * @param  array $regions The regions (empty: not region-limited)
 * @param  ?TIME $edit_time Edit time (null: either means current time, or if $null_is_literal, means reset to to null)
 * @param  ?TIME $add_time Add time (null: do not change)
 * @param  boolean $null_is_literal Determines whether some nulls passed mean 'use a default' or literally mean 'set to null'
 * @param  boolean $uniqify Whether to force the name as unique, if there's a conflict
 * @return ID_TEXT The name
 */
function edit_banner($old_name, $name, $imgurl, $title_text, $caption, $direct_code, $campaign_remaining, $site_url, $display_likelihood, $notes, $deployment_agreement, $expiry_date, $submitter, $validated, $b_type, $b_types = array(), $regions = array(), $edit_time = null, $add_time = null, $null_is_literal = false, $uniqify = false)
{
    $_caption = $GLOBALS['SITE_DB']->query_select_value_if_there('banners', 'caption', array('name' => $old_name));
    if ($_caption === null) {
        $_caption(do_lang_tempcode('MISSING_RESOURCE', 'banner'));
    }

    if ($old_name != $name) {
        $test = $GLOBALS['SITE_DB']->query_select_value_if_there('banners', 'name', array('name' => $name));
        if ($test !== null) {
            if ($uniqify) {
                $name .= '_' . uniqid('', false);
            } else {
                warn_exit(do_lang_tempcode('ALREADY_EXISTS', escape_html($name)));
            }
        }

        if (addon_installed('catalogues')) {
            update_catalogue_content_ref('banner', $old_name, $name);
        }

        $GLOBALS['SITE_DB']->query_update('banner_clicks', array('c_banner_id' => $name), array('c_banner_id' => $old_name));
    }

    $GLOBALS['SITE_DB']->query_delete('banners_types', array('name' => $old_name));

    if ($edit_time === null) {
        $edit_time = $null_is_literal ? null : time();
    }

    require_code('files2');
    delete_upload('uploads/banners', 'banners', 'img_url', 'name', $old_name, $imgurl);

    delete_cache_entry('main_banner_wave');
    delete_cache_entry('main_top_sites');

    if (!addon_installed('unvalidated')) {
        $validated = 1;
    }

    require_code('submit');
    $just_validated = (!content_validated('banner', $name)) && ($validated == 1);
    if ($just_validated) {
        send_content_validated_notification('banner', $name);
    }

    $update_map = array(
        'title_text' => $title_text,
        'direct_code' => $direct_code,
        'expiry_date' => $expiry_date,
        'deployment_agreement' => $deployment_agreement,
        'name' => $name,
        'img_url' => $imgurl,
        'campaign_remaining' => $campaign_remaining,
        'site_url' => $site_url,
        'display_likelihood' => $display_likelihood,
        'notes' => $notes,
        'validated' => $validated,
        'b_type' => $b_type,
    );
    $update_map += lang_remap_comcode('caption', $_caption, $caption);

    if ($submitter !== null) {
        $update_map['submitter'] = $submitter;
    }
    $update_map['edit_date'] = $edit_time;
    if ($add_time !== null) {
        $update_map['add_date'] = $add_time;
    }

    $GLOBALS['SITE_DB']->query_update('banners', $update_map, array('name' => $old_name), '', 1);

    foreach ($b_types as $b_type_sup) {
        $GLOBALS['SITE_DB']->query_insert('banners_types', array('name' => $name, 'b_type' => $b_type_sup));
    }

    $GLOBALS['SITE_DB']->query_delete('content_regions', array('content_type' => 'banner', 'content_id' => $old_name));
    foreach ($regions as $region) {
        $GLOBALS['SITE_DB']->query_insert('content_regions', array('content_type' => 'banner', 'content_id' => $name, 'region' => $region));
    }

    reorganise_uploads__banners(array('name' => $name));

    log_it('EDIT_BANNER', $name, $caption);

    if ((addon_installed('commandr')) && (!running_script('install'))) {
        require_code('resource_fs');
        generate_resource_fs_moniker('banner', $name);
    }

    return $name;
}

/**
 * Delete a banner.
 *
 * @param  ID_TEXT $name The name of the banner
 */
function delete_banner($name)
{
    $caption = $GLOBALS['SITE_DB']->query_select_value_if_there('banners', 'caption', array('name' => $name));
    if ($caption === null) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'banner'));
    }

    if (addon_installed('catalogues')) {
        update_catalogue_content_ref('banner', $name, '');
    }

    $GLOBALS['SITE_DB']->query_delete('banner_clicks', array('c_banner_id' => $name));

    delete_lang($caption);

    require_code('files2');
    delete_upload('uploads/banners', 'banners', 'img_url', 'name', $name);

    delete_cache_entry('main_banner_wave');
    delete_cache_entry('main_top_sites');

    $GLOBALS['SITE_DB']->query_delete('banners', array('name' => $name), '', 1);

    $GLOBALS['SITE_DB']->query_delete('banners_types', array('name' => $name));
    $GLOBALS['SITE_DB']->query_delete('content_regions', array('content_type' => 'banner', 'content_id' => $name));

    require_code('uploads2');
    clean_empty_upload_directories('uploads/banners');

    log_it('DELETE_BANNER', $name, get_translated_text($caption));

    if ((addon_installed('commandr')) && (!running_script('install'))) {
        require_code('resource_fs');
        expunge_resource_fs_moniker('banner', $name);
    }
}

/**
 * Add a banner type.
 *
 * @param  ID_TEXT $id The ID of the banner type
 * @param  BINARY $is_textual Whether this is a textual banner
 * @param  integer $image_width The image width (ignored for textual banners)
 * @param  integer $image_height The image height (ignored for textual banners)
 * @param  integer $max_file_size The maximum file size for the banners in Kilobytes (this is a string length for textual banners)
 * @param  BINARY $comcode_inline Whether the banner will be automatically shown via Comcode hot-text (this can only happen if banners of the title are given title-text)
 * @param  boolean $uniqify Whether to force the name as unique, if there's a conflict
 * @return ID_TEXT The name
 */
function add_banner_type($id, $is_textual, $image_width, $image_height, $max_file_size, $comcode_inline, $uniqify = false)
{
    $test = $GLOBALS['SITE_DB']->query_select_value_if_there('banner_types', 'id', array('id' => $id));
    if ($test !== null) {
        if ($uniqify) {
            $id .= '_' . uniqid('', false);
        } else {
            warn_exit(do_lang_tempcode('ALREADY_EXISTS', escape_html($id)));
        }
    }

    $GLOBALS['SITE_DB']->query_insert('banner_types', array(
        'id' => $id,
        't_is_textual' => $is_textual,
        't_image_width' => $image_width,
        't_image_height' => $image_height,
        't_max_file_size' => $max_file_size,
        't_comcode_inline' => $comcode_inline,
    ));

    log_it('ADD_BANNER_TYPE', $id);

    if ((addon_installed('commandr')) && (!running_script('install'))) {
        require_code('resource_fs');
        generate_resource_fs_moniker('banner_type', $id);
    }

    require_code('member_mentions');
    dispatch_member_mention_notifications('banner_type', $id);

    return $id;
}

/**
 * Edit a banner type.
 *
 * @param  ID_TEXT $old_id The original ID of the banner type
 * @param  ID_TEXT $id The ID of the banner type
 * @param  BINARY $is_textual Whether this is a textual banner
 * @param  integer $image_width The image width (ignored for textual banners)
 * @param  integer $image_height The image height (ignored for textual banners)
 * @param  integer $max_file_size The maximum file size for the banners in Kilobytes (this is a string length for textual banners)
 * @param  BINARY $comcode_inline Whether the banner will be automatically shown via Comcode hot-text (this can only happen if banners of the title are given title-text)
 * @param  boolean $uniqify Whether to force the name as unique, if there's a conflict
 * @return ID_TEXT The name
 */
function edit_banner_type($old_id, $id, $is_textual, $image_width, $image_height, $max_file_size, $comcode_inline, $uniqify = false)
{
    $rows = $GLOBALS['SITE_DB']->query_select('banner_types', array('id'), array('id' => $id), '', 1);
    if (!array_key_exists(0, $rows)) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'banner_type'));
    }

    if ($old_id != $id) {
        $test = $GLOBALS['SITE_DB']->query_select_value_if_there('banner_types', 'id', array('id' => $id));
        if ($test !== null) {
            if ($uniqify) {
                $id .= '_' . uniqid('', false);
            } else {
                warn_exit(do_lang_tempcode('ALREADY_EXISTS', escape_html($id)));
            }
        }
        $GLOBALS['SITE_DB']->query_update('banners', array('b_type' => $id), array('b_type' => $old_id));

        if (addon_installed('catalogues')) {
            update_catalogue_content_ref('banner_type', $old_id, $id);
        }
    }

    $GLOBALS['SITE_DB']->query_update('banner_types', array(
        'id' => $id,
        't_is_textual' => $is_textual,
        't_image_width' => $image_width,
        't_image_height' => $image_height,
        't_max_file_size' => $max_file_size,
        't_comcode_inline' => $comcode_inline,
    ), array('id' => $old_id), '', 1);

    log_it('EDIT_BANNER_TYPE', $old_id, $id);

    if ((addon_installed('commandr')) && (!running_script('install'))) {
        require_code('resource_fs');
        generate_resource_fs_moniker('banner_type', $id);
    }

    return $id;
}

/**
 * Delete a banner type.
 *
 * @param  ID_TEXT $id The ID of the banner type
 */
function delete_banner_type($id)
{
    $rows = $GLOBALS['SITE_DB']->query_select('banner_types', array('id'), array('id' => $id), '', 1);
    if (!array_key_exists(0, $rows)) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'banner_type'));
    }

    $GLOBALS['SITE_DB']->query_update('banners', array('b_type' => ''), array('b_type' => $id));

    $GLOBALS['SITE_DB']->query_delete('banner_types', array('id' => $id), '', 1);

    if (addon_installed('catalogues')) {
        update_catalogue_content_ref('banner_type', strval($id), '');
    }

    log_it('DELETE_BANNER_TYPE', $id);

    if ((addon_installed('commandr')) && (!running_script('install'))) {
        require_code('resource_fs');
        expunge_resource_fs_moniker('banner_type', $id);
    }
}

/**
 * Reorganise the banner uploads.
 *
 * @param  array $where Limit reorganisation to rows matching this WHERE map
 * @param  boolean $tolerate_errors Whether to tolerate missing files (false = give an error)
 */
function reorganise_uploads__banners($where = array(), $tolerate_errors = false)
{
    require_code('uploads2');
    reorganise_uploads('banner', 'uploads/banners', 'img_url', $where, null, false, $tolerate_errors);
}
