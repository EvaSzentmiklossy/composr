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
 * @package    core_abstract_interfaces
 */

/**
 * Get the Tempcode for a do next manager. A do next manager is a series of linked icons that are presented after performing an action. Modules that do not use do-next pages, usually use REFRESH_PAGE's.
 *
 * @param  ID_TEXT $title The title of what we are doing (a language string)
 * @param  ?mixed $text The language string ID for the docs of the hook defined do-next manager that we're creating OR Tempcode for it (null: none)
 * @param  ID_TEXT $type The menu 'type' we are doing (filters out any icons that don't match it)
 * @param  ?string $main_title The title to use for the main links (a language string) (null: same as title)
 * @return Tempcode The do next manager
 */
function do_next_manager_hooked($title, $text, $type, $main_title = null)
{
    $links = array();

    if ($main_title === null) {
        $main_title = $title;
    }

    require_lang('menus');

    $hooks = find_all_hook_obs('systems', 'page_groupings', 'Hook_page_groupings_');
    foreach ($hooks as $object) {
        $_links = $object->run(null, true);
        foreach ($_links as $link) {
            if ($link === null) {
                continue;
            }

            if (($link[0] == $type) && (is_array($link[2]))) {
                if ($type == '') {
                    // Skip front-end ones, which are never listed like this
                    if (!isset($link[2][1]['type'])) {
                        continue;
                    }
                    if (in_array($link[2][1]['type'], array('site_meta', 'pages', 'social', 'rich_content'))) {
                        continue;
                    }
                }

                array_shift($link);
                $links[] = $link;
            }
        }
    }

    sort_maps_by($links, 2);

    if ($text !== null) {
        if (strpos($text, ' ') === false) {
            $_text = comcode_lang_string($text);
        } else {
            $_text = make_string_tempcode($text);
        }
    } else {
        $_text = new Tempcode();
    }

    set_helper_panel_text(comcode_lang_string('menus:DOC_HELPER_PANEL'));

    return do_next_manager(($text === null) ? null : get_screen_title($title), $_text, $links, do_lang($main_title));
}

/**
 * Get the Tempcode for a do next manager. A do next manager is a series of linked icons that are presented after performing an action. Modules that do not use do-next pages, usually use REFRESH_PAGE's.
 *
 * @param  ?Tempcode $title The title of what we just did (should have been passed through get_screen_title already) (null: don't do full page)
 * @param  Tempcode $text The 'text' (actually, a full XHTML lump) to show on the page
 * @param  array $main An array of entry types, with each array entry being -- an array consisting of the type codename and a URL array as per following parameters
 * @param  ?string $main_title The title to use for the main links (null: none)
 * @param  ?array $url_add_one The URL used to 'add-one' (null: impossible) (format: array of page, param, zone[, custom label])
 * @param  ?array $url_edit_this The URL used to 'edit-this' (null: impossible) (format: array of page, param, zone[, custom label])
 * @param  ?array $url_edit_one The URL used to 'edit-one' (null: impossible) (format: array of page, param, zone[, custom label])
 * @param  ?array $url_view_this The URL used to 'view-this' (null: impossible) (format: array of page, param, zone[, custom label])
 * @param  ?array $url_view_archive The URL used to 'view-archive' (null: impossible) (format: array of page, param, zone[, custom label])
 * @param  ?array $url_add_one_category The URL used to 'add-one-category' (null: impossible) (format: array of page, param, zone[, custom label])
 * @param  ?array $url_edit_one_category The URL used to 'edit-one-category' (null: impossible) (format: array of page, param, zone[, custom label])
 * @param  ?array $url_edit_this_category The URL used to 'edit-this-category' (null: impossible) (format: array of page, param, zone[, custom label])
 * @param  ?array $url_view_this_category The URL used to 'view-this-category' (null: impossible) (format: array of page, param, zone[, custom label])
 * @param  array $entry_extras An array of additional entry types, with each array entry being -- an array of type codename and a URL array as before
 * @param  array $category_extras As before, but with category types
 * @param  array $additional_extras As before, but for an 'extra types' box of do next actions
 * @param  ?mixed $additional_title The title to use for the extra types (null: none)
 * @param  ?Tempcode $intro Introductory text (null: none)
 * @param  ?mixed $entries_title Entries section title (null: default, Entries)
 * @param  ?mixed $categories_title Categories section title (null: default, Categories)
 * @param  ?string $entry_content_type Entry content type (null: unknown)
 * @param  ?string $category_content_type Category content type (null: unknown)
 * @return Tempcode The do next manager
 */
function do_next_manager($title, $text, $main = array(), $main_title = null, $url_add_one = null, $url_edit_this = null, $url_edit_one = null, $url_view_this = null, $url_view_archive = null, $url_add_one_category = null, $url_edit_one_category = null, $url_edit_this_category = null, $url_view_this_category = null, $entry_extras = array(), $category_extras = array(), $additional_extras = array(), $additional_title = null, $intro = null, $entries_title = null, $categories_title = null, $entry_content_type = null, $category_content_type = null)
{
    if ($intro === null) {
        $intro = new Tempcode();
    }

    require_code('failure');
    $_text = _look_for_match_key_message(is_object($text) ? $text->evaluate() : $text, false, true);
    if ($_text !== null) {
        $text = $_text;
    }

    require_lang('do_next');
    require_css('do_next');

    $keep_simplified_donext = get_param_integer('keep_simplified_donext', null);
    $simplified = ((($keep_simplified_donext !== 0) && (get_option('simplified_donext') == '1')) || ($keep_simplified_donext == 1));

    $sections = new Tempcode();

    // Main section stuff (the "Main" section is not always shown - it is shown when the do-next screen is being used as a traditional menu, not as a followup-action screen)
    if ($main_title !== null) {
        $sections->attach(_do_next_section($main, make_string_tempcode($main_title), $entry_content_type, $category_content_type));
    }

    $current_page_type = get_param_string('type', '');

    // Entry stuff
    $entry_passed = array(
        'admin/add',
        'admin/edit_this',
        'admin/edit',
        'admin/view_this',
        'admin/view_archive',
    );
    $entry_passed_2 = array();
    foreach ($entry_passed as $option) {
        $x = null;
        $auto_add = null;
        switch ($option) {
            case 'admin/add':
                $x = $url_add_one;
                if (($current_page_type == '_add') || ($current_page_type == '_add_entry')) {
                    if (get_param_integer('auto__add_one', 0) == 1) {
                        $x[1]['auto__add_one'] = '1';
                        $_url_redirect = build_url(array_merge(array('page' => $x[0]), $x[1]), $x[2]);
                        return redirect_screen($title, $_url_redirect, $text);
                    }
                    $auto_add = 'auto__add_one';
                }
                break;
            case 'admin/edit_this':
                $x = $url_edit_this;
                break;
            case 'admin/edit':
                $x = $url_edit_one;
                break;
            case 'admin/view_this':
                $x = $url_view_this;
                if ($x !== null) {
                    if ($simplified) {
                        $_url_redirect = build_url(array_merge(array('page' => $x[0]), $x[1]), $x[2]);
                        return redirect_screen($title, $_url_redirect, $text);
                    }
                }
                break;
            case 'admin/view_archive':
                $x = $url_view_archive;
                break;
        }
        if ($x !== null) {
            if (array_key_exists(3, $x)) {
                $map = array($option, array($x[0], $x[1], $x[2]), $x[3]);
            } else {
                $map = array($option, $x);
            }
            if ($auto_add !== null) {
                $map[5] = $auto_add;
            }
            $entry_passed_2[] = $map;
        }
    }
    $sections->attach(_do_next_section($entry_passed_2, ($entries_title === null) ? do_lang_tempcode('ENTRIES') : (is_object($entries_title) ? $entries_title : make_string_tempcode($entries_title)), $entry_content_type, $category_content_type));

    // Category stuff
    $category_passed = array(
        'admin/add_one_category',
        'admin/edit_one_category',
        'admin/edit_this_category',
        'admin/view_this_category',
    );
    $category_passed_2 = array();
    foreach ($category_passed as $option) {
        $x = null;
        $auto_add = null;
        switch ($option) {
            case 'admin/add_one_category':
                $x = $url_add_one_category;
                if (($current_page_type == '_add_category') || ($current_page_type == '_add_category')) {
                    if (get_param_integer('auto__add_one_category', 0) == 1) {
                        $x[1]['auto__add_one_category'] = '1';
                        $_url_redirect = build_url(array_merge(array('page' => $x[0]), $x[1]), $x[2]);
                        return redirect_screen($title, $_url_redirect, $text);
                    }
                    $auto_add = 'auto__add_one_category';
                }
                break;
            case 'admin/edit_one_category':
                $x = $url_edit_one_category;
                break;
            case 'admin/edit_this_category':
                $x = $url_edit_this_category;
                break;
            case 'admin/view_this_category':
                $x = $url_view_this_category;
                break;
        }
        if ($x !== null) {
            if ($option == 'view_this' || $option == 'view_archive') {
                if ($simplified) {
                    $_url_redirect = build_url(array_merge(array('page' => $x[0]), $x[1]), $x[2]);
                    return redirect_screen($title, $_url_redirect, $text);
                }
            }

            if (array_key_exists(3, $x)) {
                $map = array($option, array($x[0], $x[1], $x[2]), $x[3]);
            } else {
                $map = array($option, $x);
            }
            if ($auto_add !== null) {
                $map[5] = $auto_add;
            }
            $category_passed_2[] = $map;
        }
    }
    $category_passed_2 = array_merge($category_passed_2, $category_extras);
    $sections->attach(_do_next_section($category_passed_2, ($categories_title === null) ? do_lang_tempcode('CATEGORIES') : (is_object($categories_title) ? $categories_title : make_string_tempcode($categories_title)), $entry_content_type, $category_content_type));

    // Additional section stuff
    if ($additional_title !== null) {
        $sections->attach(_do_next_section($additional_extras, is_object($additional_title) ? $additional_title : make_string_tempcode($additional_title), $entry_content_type, $category_content_type));
    }

    if (($main === null) && (get_option('global_donext_icons') == '1')) { // What-next
        // These go on a new row
        $disjunct_items = array(
            array('menu/' . DEFAULT_ZONE_PAGE_NAME, array(null, array(), '')),
            array('menu/cms/cms', array(null, array(), 'cms')),
            array('menu/adminzone/adminzone', array(null, array(), 'adminzone')),
        );
        $sections->attach(_do_next_section($disjunct_items, do_lang_tempcode('GLOBAL_NAVIGATION'), $entry_content_type, $category_content_type));
        $question = do_lang_tempcode('WHERE_NEXT');
    } else { // Where-next
        $question = do_lang_tempcode('WHAT_NEXT');
    }

    if ($simplified && count($entry_passed_2) != 0) {
        $_url_redirect = build_url(array('page' => ''), 'site');
        require_code('templates_redirect_screen');
        return redirect_screen($title, $_url_redirect, $text);
    }

    if ($text->evaluate() == do_lang('SUCCESS')) {
        attach_message($text, 'inform');
        $text = null;
    }

    if ($title === null) {
        return $sections;
    }

    return do_template('DO_NEXT_SCREEN', array(
        '_GUID' => 'a00e89bece6b7ce870ad5096930d5a94',
        'INTRO' => $intro,
        'TEXT' => $text,
        'QUESTION' => $question,
        'TITLE' => $title,
        'SECTIONS' => $sections,
    ));
}

/**
 * Get the Tempcode for a do next manager. A do next manager is a series of linked icons that are presented after performing an action. Modules that do not use do-next pages, usually use REFRESH_PAGE's.
 *
 * @param  array $list A list of items (each item is a pair or a triple: <option,url[,field name=do_lang(option)]> ; url is a pair or a triple or a quarto also: <page,map[,zone[,warning]]>)
 * @param  Tempcode $title The title for the section
 * @param  ?string $entry_content_type Entry content type (null: unknown)
 * @param  ?string $category_content_type Category content type (null: unknown)
 * @return Tempcode The do next manager section
 *
 * @ignore
 */
function _do_next_section($list, $title, $entry_content_type = null, $category_content_type = null)
{
    if (count($list) == 0) {
        return new Tempcode();
    }

    $next_items = new Tempcode();

    $num_siblings = 0;
    foreach ($list as $i => $_option) {
        $url = $_option[1];
        if ($url !== null) {
            $zone = array_key_exists(2, $url) ? $url[2] : '';
            $page = $url[0];
            if ($page == '_SELF') {
                $page = get_page_name();
            }
            if ((($page === null) && (has_zone_access(get_member(), $zone))) || (($page !== null) && (has_actual_page_access(get_member(), $page, $zone)))) {
                $num_siblings++;
            } else {
                $list[$i] = null;
            }
        } else {
            $list[$i] = null;
        }
    }
    $i = 0;
    foreach ($list as $_option) {
        if ($_option === null) {
            continue;
        }

        $option = $_option[0];
        $url_map = $_option[1];
        $zone = array_key_exists(2, $url_map) ? $url_map[2] : '';
        $page = $url_map[0];
        if ($page == '_SELF') {
            $page = get_page_name();
        }

        if (array_key_exists(2, $_option) && ($_option[2] !== null)) {
            $description = $_option[2];
        } else {
            $description = do_lang_tempcode('NEXT_ITEM_' . basename($option), (strpos($option, 'category') !== false) ? $category_content_type : $entry_content_type);
        }
        $url = ($page === null) ? build_url(array_merge($url_map[1], array('page' => '')), $zone) : build_url(array_merge(array('page' => $page), $url_map[1]), $zone);
        $doc = array_key_exists(3, $_option) ? $_option[3] : '';
        if ((is_string($doc)) && ($doc != '')) {
            if (preg_match('#^[:\w]+$#', $doc) == 0) {
                $doc = comcode_to_tempcode($doc, null, true);
            } else {
                $doc = comcode_lang_string($doc);
            }
        }
        $target = array_key_exists(4, $_option) ? $_option[4] : null;
        $auto_add = array_key_exists(5, $_option) ? $_option[5] : null;
        if (get_value('disable_bulkhelper') === '1') {
            $auto_add = null;
        }

        $next_items->attach(do_template('DO_NEXT_ITEM', array(
            '_GUID' => 'f39b6055d1127edb452595e7eeaf2f01',
            'AUTO_ADD' => $auto_add,
            'I' => strval($i),
            'NUM_SIBLINGS' => strval($num_siblings),
            'TARGET' => $target,
            'PICTURE' => $option,
            'DESCRIPTION' => $description,
            'URL' => $url,
            'DOC' => $doc,
            'WARNING' => array_key_exists(3, $url) ? $url[3] : '',
        )));
        $i++;
    }

    if ($next_items->is_empty()) {
        return new Tempcode();
    }

    return do_template('DO_NEXT_SECTION', array(
        '_GUID' => '18589e9e8ec1971f692cb76d71f33ec1',
        'I' => strval($i),
        'TITLE' => $title,
        'CONTENT' => $next_items,
    ));
}
