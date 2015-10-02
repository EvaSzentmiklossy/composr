<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2015

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core_cleanup_tools
 */

/**
 * Hook class.
 */
class Hook_addon_registry_core_cleanup_tools
{
    /**
     * Get a list of file permissions to set
     *
     * @return array File permissions to set
     */
    public function get_chmod_array()
    {
        return array();
    }

    /**
     * Get the version of Composr this addon is for
     *
     * @return float Version number
     */
    public function get_version()
    {
        return cms_version_number();
    }

    /**
     * Get the description of the addon
     *
     * @return string Description of the addon
     */
    public function get_description()
    {
        return 'Behind-the-scenes maintenance tasks.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_cleanup',
        );
    }

    /**
     * Get a mapping of dependency types
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
     * Explicitly say which icon should be used
     *
     * @return URLPATH Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/48x48/menu/adminzone/tools/cleanup.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/24x24/menu/adminzone/tools/cleanup.png',
            'themes/default/images/icons/48x48/menu/adminzone/tools/cleanup.png',
            'sources/hooks/systems/config/is_on_block_cache.php',
            'sources/hooks/systems/config/is_on_comcode_page_cache.php',
            'sources/hooks/systems/config/is_on_lang_cache.php',
            'sources/hooks/systems/config/is_on_template_cache.php',
            'data/modules/admin_cleanup/.htaccess',
            'data/modules/admin_cleanup/index.html',
            'sources/hooks/systems/addon_registry/core_cleanup_tools.php',
            'themes/default/templates/CLEANUP_ORPHANED_UPLOADS.tpl',
            'themes/default/templates/CLEANUP_COMPLETED_SCREEN.tpl',
            'themes/default/templates/CLEANUP_PAGE_STATS.tpl',
            'adminzone/pages/modules/admin_cleanup.php',
            'sources/hooks/systems/cleanup/comcode.php',
            'lang/EN/cleanup.ini',
            'sources/hooks/systems/cleanup/.htaccess',
            'sources/hooks/systems/cleanup/admin_theme_images.php',
            'sources/hooks/systems/cleanup/blocks.php',
            'sources/hooks/systems/cleanup/broken_urls.php',
            'sources/hooks/systems/cleanup/image_thumbs.php',
            'sources/hooks/systems/cleanup/index.html',
            'sources/hooks/systems/cleanup/language.php',
            'sources/hooks/systems/cleanup/mysql.php',
            'sources/hooks/systems/cleanup/orphaned_lang_strings.php',
            'sources/hooks/systems/cleanup/orphaned_uploads.php',
            'sources/hooks/systems/cleanup/templates.php',
            'sources/hooks/systems/cleanup/criticise_mysql_fields.php',
            'sources/hooks/systems/cleanup/page_backups.php',
            'sources/hooks/systems/cleanup/tags.php',
            'sources/hooks/systems/cleanup/urls.php',
            'sources/hooks/systems/cleanup/self_learning.php',
            'sources/hooks/systems/tasks/find_broken_urls.php',
            'sources/hooks/systems/tasks/find_orphaned_lang_strings.php',
            'sources/hooks/systems/tasks/find_orphaned_uploads.php',
        );
    }

    /**
     * Get mapping between template names and the method of this class that can render a preview of them
     *
     * @return array The mapping
     */
    public function tpl_previews()
    {
        return array(
            'templates/CLEANUP_COMPLETED_SCREEN.tpl' => 'administrative__cleanup_completed_screen',
            'templates/CLEANUP_ORPHANED_UPLOADS.tpl' => 'administrative__cleanup_completed_screen',
            'templates/CLEANUP_PAGE_STATS.tpl' => 'administrative__cleanup_completed_screen'
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__cleanup_completed_screen()
    {
        require_lang('stats');
        $url = array();
        foreach (placeholder_array() as $v) {
            $url[] = array(
                'URL' => placeholder_url(),
            );
        }

        $message = do_lorem_template('CLEANUP_ORPHANED_UPLOADS', array(
            'FOUND' => $url,
        ));
        $message->attach(do_lorem_template('CLEANUP_PAGE_STATS', array(
            'STATS_BACKUP_URL' => placeholder_url(),
        )));
        return array(
            lorem_globalise(do_lorem_template('CLEANUP_COMPLETED_SCREEN', array(
                'TITLE' => lorem_title(),
                'MESSAGES' => $message,
            )), null, '', true)
        );
    }
}
