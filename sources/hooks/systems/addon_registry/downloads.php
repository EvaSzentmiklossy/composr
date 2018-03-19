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
 * @package    downloads
 */

/**
 * Hook class.
 */
class Hook_addon_registry_downloads
{
    /**
     * Get a list of file permissions to set.
     *
     * @param  boolean $runtime Whether to include wildcards represented runtime-created chmoddable files
     * @return array File permissions to set
     */
    public function get_chmod_array($runtime = false)
    {
        return array();
    }

    /**
     * Get the version of Composr this addon is for.
     *
     * @return float Version number
     */
    public function get_version()
    {
        return cms_version_number();
    }

    /**
     * Get the description of the addon.
     *
     * @return string Description of the addon
     */
    public function get_description()
    {
        return 'Host a downloads directory.';
    }

    /**
     * Get a list of tutorials that apply to this addon.
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_downloads',
            'tut_adv_downloads',
            'tut_information',
        );
    }

    /**
     * Get a mapping of dependency types.
     *
     * @return array File permissions to set
     */
    public function get_dependencies()
    {
        return array(
            'requires' => array(),
            'recommends' => array(),
            'conflicts_with' => array(),
        );
    }

    /**
     * Explicitly say which icon should be used.
     *
     * @return URLPATH Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/menu/rich_content/downloads.svg';
    }

    /**
     * Get a list of files that belong to this addon.
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/menu/rich_content/downloads.svg',
            'themes/default/images/icons/menu/cms/downloads/add_one_licence.svg',
            'themes/default/images/icons/menu/cms/downloads/edit_one_licence.svg',
            'themes/default/images/icons/menu/cms/downloads/index.html',
            'sources/hooks/systems/resource_meta_aware/download_licence.php',
            'sources/hooks/systems/commandr_fs/download_licences.php',
            'sources/hooks/systems/preview/download.php',
            'sources/hooks/modules/admin_import/downloads.php',
            'sources/hooks/systems/notifications/download.php',
            'sources/hooks/systems/config/download_gallery_root.php',
            'sources/hooks/systems/config/downloads_show_stats_count_archive.php',
            'sources/hooks/systems/config/downloads_show_stats_count_bandwidth.php',
            'sources/hooks/systems/config/downloads_show_stats_count_downloads.php',
            'sources/hooks/systems/config/downloads_show_stats_count_total.php',
            'sources/hooks/systems/config/immediate_downloads.php',
            'sources/hooks/systems/config/maximum_download.php',
            'sources/hooks/systems/config/points_ADD_DOWNLOAD.php',
            'sources/hooks/systems/content_meta_aware/download.php',
            'sources/hooks/systems/content_meta_aware/download_category.php',
            'sources/hooks/systems/commandr_fs/downloads.php',
            'sources/hooks/systems/disposable_values/archive_size.php',
            'sources/hooks/modules/admin_import_types/downloads.php',
            'sources/hooks/modules/admin_setupwizard/downloads.php',
            'sources/hooks/modules/admin_stats/downloads.php',
            'sources/hooks/systems/addon_registry/downloads.php',
            'sources/hooks/systems/disposable_values/download_bandwidth.php',
            'sources/hooks/systems/disposable_values/num_archive_downloads.php',
            'sources/hooks/systems/disposable_values/num_downloads_downloaded.php',
            'themes/default/templates/DOWNLOAD_GALLERY_IMAGE_CELL.tpl',
            'themes/default/templates/DOWNLOAD_GALLERY_ROW.tpl',
            'themes/default/templates/DOWNLOAD_CATEGORY_SCREEN.tpl',
            'themes/default/templates/DOWNLOAD_SCREEN_IMAGE.tpl',
            'themes/default/templates/DOWNLOAD_BOX.tpl',
            'themes/default/templates/DOWNLOAD_LIST_LINE.tpl',
            'themes/default/templates/DOWNLOAD_LIST_LINE_2.tpl',
            'themes/default/templates/DOWNLOAD_SCREEN.tpl',
            'themes/default/templates/DOWNLOAD_ALL_SCREEN.tpl',
            'themes/default/templates/DOWNLOAD_AND_IMAGES_SIMPLE_BOX.tpl',
            'uploads/downloads/.htaccess',
            'uploads/downloads/index.html',
            'themes/default/css/downloads.css',
            'cms/pages/modules/cms_downloads.php',
            'lang/EN/downloads.ini',
            'site/pages/modules/downloads.php',
            'sources/hooks/systems/sitemap/download.php',
            'sources/hooks/systems/sitemap/download_category.php',
            'sources/downloads.php',
            'sources/downloads2.php',
            'sources/downloads_stats.php',
            'sources/hooks/blocks/side_stats/downloads.php',
            'sources/hooks/modules/admin_newsletter/downloads.php',
            'sources/hooks/modules/admin_unvalidated/downloads.php',
            'sources/hooks/modules/galleries_users/downloads.php',
            'sources/hooks/modules/search/downloads.php',
            'sources/hooks/modules/search/download_categories.php',
            'sources/hooks/systems/page_groupings/downloads.php',
            'sources/hooks/systems/rss/downloads.php',
            'sources/hooks/systems/trackback/downloads.php',
            'sources/hooks/systems/ajax_tree/choose_download.php',
            'sources/hooks/systems/ajax_tree/choose_download_category.php',
            'site/dload.php',
            'site/download_licence.php',
            'sources/hooks/systems/config/dload_search_index.php',
            'sources/hooks/systems/config/download_entries_per_page.php',
            'sources/hooks/systems/config/download_subcats_per_page.php',
            'sources/hooks/systems/config/downloads_default_sort_order.php',
            'sources/hooks/systems/config/downloads_subcat_narrowin.php',
            'sources/hooks/systems/tasks/import_filesystem_downloads.php',
            'sources/hooks/systems/tasks/import_ftp_downloads.php',
            'sources/hooks/systems/tasks/index_download.php',
            'site/download_gateway.php',
            'themes/default/templates/DOWNLOAD_GATEWAY_SCREEN.tpl',
            'sources/hooks/systems/config/search_download_categories.php',
            'sources/hooks/systems/config/search_downloads.php',
            'themes/default/javascript/downloads.js',
            'sources/hooks/systems/config/download_cat_access_late.php',
        );
    }

    /**
     * Get mapping between template names and the method of this class that can render a preview of them.
     *
     * @return array The mapping
     */
    public function tpl_previews()
    {
        return array(
            'templates/DOWNLOAD_LIST_LINE.tpl' => 'download_list_line',
            'templates/DOWNLOAD_LIST_LINE_2.tpl' => 'download_list_line_2',
            'templates/DOWNLOAD_BOX.tpl' => 'download_category_screen',
            'templates/DOWNLOAD_AND_IMAGES_SIMPLE_BOX.tpl' => 'download_and_images_simple_box',
            'templates/DOWNLOAD_CATEGORY_SCREEN.tpl' => 'download_category_screen',
            'templates/DOWNLOAD_ALL_SCREEN.tpl' => 'download_all_screen',
            'templates/DOWNLOAD_SCREEN_IMAGE.tpl' => 'download_screen',
            'templates/DOWNLOAD_GALLERY_IMAGE_CELL.tpl' => 'download_screen',
            'templates/DOWNLOAD_GALLERY_ROW.tpl' => 'download_screen',
            'templates/DOWNLOAD_SCREEN.tpl' => 'download_screen',
            'templates/DOWNLOAD_GATEWAY_SCREEN.tpl' => 'download_gateway_screen',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__download_and_images_simple_box()
    {
        return array(
            lorem_globalise(do_lorem_template('DOWNLOAD_AND_IMAGES_SIMPLE_BOX', array(
                'DESCRIPTION' => lorem_paragraph_html(),
                'IMAGES' => lorem_phrase(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__download_list_line()
    {
        return array(
            lorem_globalise(do_lorem_template('DOWNLOAD_LIST_LINE', array(
                'BREADCRUMBS' => lorem_word(),
                'DOWNLOAD' => lorem_phrase(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__download_list_line_2()
    {
        return array(
            lorem_globalise(do_lorem_template('DOWNLOAD_LIST_LINE_2', array(
                'BREADCRUMBS' => lorem_phrase(),
                'FILECOUNT' => placeholder_number(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__download_category_screen()
    {
        $subcategories = lorem_paragraph_html();

        $downloads = new Tempcode();
        $map = array(
            'ORIGINAL_FILENAME' => lorem_phrase(),
            'AUTHOR' => lorem_phrase(),
            'ID' => placeholder_id(),
            'VIEWS' => placeholder_number(),
            'SUBMITTER' => placeholder_id(),
            'DESCRIPTION' => lorem_sentence(),
            'FILE_SIZE' => placeholder_number(),
            'DOWNLOADS' => placeholder_number(),
            'DATE_RAW' => placeholder_date_raw(),
            'DATE' => placeholder_date(),
            'EDIT_DATE_RAW' => '',
            'URL' => placeholder_url(),
            'NAME' => lorem_phrase(),
            'BREADCRUMBS' => placeholder_breadcrumbs(),
            'IMGCODE' => '',
            'GIVE_CONTEXT' => false,
            'MAY_DOWNLOAD' => true,
            'DOWNLOAD_URL' => placeholder_url(),
        );
        $tpl = do_lorem_template('DOWNLOAD_BOX', $map);
        $downloads->attach($tpl);

        return array(
            lorem_globalise(do_lorem_template('DOWNLOAD_CATEGORY_SCREEN', array(
                'TAGS' => lorem_word_html(),
                'TITLE' => lorem_title(),
                'WARNING_DETAILS' => '',
                'SUBMIT_URL' => placeholder_url(),
                'ADD_CAT_URL' => placeholder_url(),
                'ADD_CAT_TITLE' => do_lang_tempcode('ADD_DOWNLOAD_CATEGORY'),
                'EDIT_CAT_URL' => placeholder_url(),
                'DESCRIPTION' => lorem_paragraph_html(),
                'SUBCATEGORIES' => $subcategories,
                'DOWNLOADS' => $downloads,
                'SORTING' => lorem_phrase(),
                'ID' => placeholder_id(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__download_all_screen()
    {
        $subcats = array();
        foreach (placeholder_array() as $cat) {
            $downloads = new Tempcode();
            $map = array(
                'ORIGINAL_FILENAME' => lorem_phrase(),
                'AUTHOR' => lorem_phrase(),
                'ID' => placeholder_id(),
                'VIEWS' => placeholder_number(),
                'SUBMITTER' => placeholder_id(),
                'DESCRIPTION' => lorem_sentence(),
                'FILE_SIZE' => placeholder_number(),
                'DOWNLOADS' => placeholder_number(),
                'DATE_RAW' => placeholder_date_raw(),
                'DATE' => placeholder_date(),
                'EDIT_DATE_RAW' => '',
                'URL' => placeholder_url(),
                'NAME' => lorem_phrase(),
                'BREADCRUMBS' => placeholder_breadcrumbs(),
                'IMGCODE' => '',
                'GIVE_CONTEXT' => false,
                'MAY_DOWNLOAD' => true,
                'DOWNLOAD_URL' => placeholder_url(),
            );
            $tpl = do_lorem_template('DOWNLOAD_BOX', $map);
            $downloads->attach($tpl);

            $data = array('LETTER' => lorem_word(), 'DOWNLOADS' => $downloads);
            $subcats[] = $data;
        }

        return array(
            lorem_globalise(do_lorem_template('DOWNLOAD_ALL_SCREEN', array(
                'TITLE' => lorem_title(),
                'SUBMIT_URL' => placeholder_url(),
                'ADD_CAT_URL' => placeholder_url(),
                'ADD_CAT_TITLE' => do_lang_tempcode('ADD_DOWNLOAD_CATEGORY'),
                'EDIT_CAT_URL' => placeholder_url(),
                'SUB_CATEGORIES' => $subcats,
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__download_screen()
    {
        $images_details = new Tempcode();
        if (addon_installed('galleries')) {
            require_lang('galleries');
            foreach (placeholder_array() as $row) {
                $image = do_lorem_template('DOWNLOAD_SCREEN_IMAGE', array(
                    'ID' => placeholder_id(),
                    'VIEW_URL' => placeholder_url(),
                    'EDIT_URL' => placeholder_url(),
                    'THUMB' => placeholder_image(),
                    'DESCRIPTION' => lorem_phrase(),
                ));

                $cell = do_lorem_template('DOWNLOAD_GALLERY_IMAGE_CELL', array(
                    'CONTENT' => $image,
                ));

                $images_details->attach(do_lorem_template('DOWNLOAD_GALLERY_ROW', array(
                    'CELLS' => $cell,
                )));
            }
        }

        return array(
            lorem_globalise(do_lorem_template('DOWNLOAD_SCREEN', array(
                'ORIGINAL_FILENAME' => lorem_phrase(),
                'TAGS' => lorem_word_html(),
                'LICENCE' => lorem_phrase(),
                'LICENCE_TITLE' => lorem_phrase(),
                'LICENCE_HYPERLINK' => placeholder_link(),
                'SUBMITTER' => placeholder_id(),
                'EDIT_DATE' => placeholder_date(),
                'EDIT_DATE_RAW' => placeholder_date_raw(),
                'VIEWS' => lorem_phrase(),
                'DATE' => placeholder_date(),
                'DATE_RAW' => placeholder_date_raw(),
                'NUM_DOWNLOADS' => placeholder_number(),
                'TITLE' => lorem_title(),
                'NAME' => lorem_phrase(),
                'OUTMODE_URL' => placeholder_url(),
                'WARNING_DETAILS' => '',
                'EDIT_URL' => placeholder_url(),
                'ADD_IMG_URL' => placeholder_url(),
                'DESCRIPTION' => lorem_paragraph_html(),
                'ADDITIONAL_DETAILS' => lorem_sentence_html(),
                'IMAGES_DETAILS' => $images_details,
                'ID' => placeholder_id(),
                'FILE_SIZE' => placeholder_filesize(),
                'AUTHOR_URL' => placeholder_url(),
                'AUTHOR' => lorem_phrase(),
                'TRACKBACK_DETAILS' => lorem_sentence_html(),
                'RATING_DETAILS' => lorem_sentence_html(),
                'COMMENT_DETAILS' => lorem_sentence_html(),
                'MAY_DOWNLOAD' => true,
                'NUM_IMAGES' => '3',
                'CAT' => placeholder_id(),
                'DOWNLOAD_URL' => placeholder_url(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__download_gateway_screen()
    {
        return array(
            lorem_globalise(do_lorem_template('DOWNLOAD_GATEWAY_SCREEN', array(
                'TITLE' => lorem_title(),
                'NAME' => lorem_phrase(),
                'DOWNLOAD_URL' => placeholder_url(),
                'URL' => placeholder_url(),
            )), null, '', true)
        );
    }

    /**
     * Uninstall default content.
     */
    public function uninstall_test_content()
    {
        require_code('downloads2');

        $to_delete = $GLOBALS['SITE_DB']->query_select('download_downloads', array('id'), array($GLOBALS['SITE_DB']->translate_field_ref('name') => lorem_phrase()));
        foreach ($to_delete as $record) {
            delete_download($record['id']);
        }

        $to_delete = $GLOBALS['SITE_DB']->query_select('download_licences', array('id'), array('l_title' => lorem_phrase()));
        foreach ($to_delete as $record) {
            delete_download_licence($record['id']);
        }

        $to_delete = $GLOBALS['SITE_DB']->query_select('download_categories', array('id'), array($GLOBALS['SITE_DB']->translate_field_ref('category') => lorem_phrase()));
        foreach ($to_delete as $record) {
            delete_download_category($record['id']);
        }

        // NB: Comment Topic and Image will be removed via different hooks
    }

    /**
     * Install default content.
     */
    public function install_test_content()
    {
        require_code('downloads2');

        $category_id = add_download_category(lorem_phrase(), db_get_first_id(), lorem_paragraph(), '', placeholder_image_url());
        require_code('permissions2');
        set_global_category_access('download', $category_id);

        $licence_id = add_download_licence(lorem_phrase(), lorem_chunk());

        $download_id = add_download($category_id, lorem_phrase(), placeholder_image_url(), lorem_chunk(), $GLOBALS['FORUM_DRIVER']->get_username(get_member()), lorem_chunk(), null, 1, 1, 2/*reviews supported*/, 1, '', uniqid('', true) . '.jpg', 100, 110, 1, $licence_id, null, 0, 0, null, null, null, lorem_word() . ',' . lorem_phrase());

        if (addon_installed('galleries')) {
            require_code('galleries2');
            add_image(lorem_phrase(), 'download_' . strval($download_id), lorem_sentence(), placeholder_image_url(), '', 1, 1, 1, 1, '', null, null, null, 0, null, lorem_word() . ',' . lorem_phrase());
        }

        if (addon_installed('awards')) {
            if ($GLOBALS['SITE_DB']->query_select_value_if_there('award_types', 'a_content_type', array('id' => db_get_first_id())) === 'download') {
                require_code('awards');
                give_award(db_get_first_id(), strval($download_id));
            }
        }

        $content_id = strval($download_id);
        $content_url = build_url(array('page' => 'downloads', 'type' => 'entry', 'id' => $content_id), 'site');
        $GLOBALS['SITE_DB']->query_insert('trackbacks', array(
            'trackback_for_type' => 'downloads',
            'trackback_for_id' => $content_id,
            'trackback_ip' => '',
            'trackback_time' => time(),
            'trackback_url' => '',
            'trackback_title' => lorem_phrase(),
            'trackback_excerpt' => lorem_paragraph(),
            'trackback_name' => lorem_phrase(),
        ));
        $GLOBALS['SITE_DB']->query_insert('rating', array(
            'rating_for_type' => 'downloads',
            'rating_for_id' => $content_id,
            'rating_member' => get_member(),
            'rating_ip' => '',
            'rating_time' => time(),
            'rating' => 3,
        ));
        set_mass_import_mode(false); // Needed for $update_caching
        $GLOBALS['FORUM_DRIVER']->make_post_forum_topic(
            get_option('comments_forum_name'),
            'downloads_' . strval($content_id),
            get_member(),
            lorem_phrase(),
            lorem_paragraph(),
            lorem_phrase(),
            do_lang('COMMENT'),
            $content_url->evaluate(),
            null,
            null,
            1,
            1
        );
        set_mass_import_mode(true);
        $GLOBALS['SITE_DB']->query_insert('review_supplement', array(
            'r_rating' => 3,
            'r_rating_for_type' => 'downloads',
            'r_rating_for_id' => $content_id,
            'r_rating_type' => '',
            'r_topic_id' => $GLOBALS['LAST_TOPIC_ID'],
            'r_post_id' => $GLOBALS['LAST_POST_ID'],
        ));
    }
}
