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
 * @package    actionlog
 */

/**
 * Hook class.
 */
class Hook_addon_registry_actionlog
{
    /**
     * Get a list of file permissions to set
     *
     * @param  boolean $runtime Whether to include wildcards represented runtime-created chmoddable files
     * @return array File permissions to set
     */
    public function get_chmod_array($runtime = false)
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
        return 'Audit-trail functionality.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_censor',
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
            'previously_in_addon' => array('actionlog'),
        );
    }

    /**
     * Explicitly say which icon should be used
     *
     * @return URLPATH Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/48x48/menu/adminzone/audit/actionlog.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/24x24/menu/adminzone/audit/actionlog.png',
            'themes/default/images/icons/48x48/menu/adminzone/audit/actionlog.png',
            'sources/hooks/systems/notifications/actionlog.php',
            'sources/hooks/systems/realtime_rain/actionlog.php',
            'sources/hooks/systems/addon_registry/actionlog.php',
            'adminzone/pages/modules/admin_actionlog.php',
            'sources/hooks/systems/rss/admin_recent_actions.php',
            'lang/EN/actionlog.ini',

            // Revisions
            'sources/hooks/systems/config/store_revisions.php',
            'sources/revisions_engine_files.php',
            'sources/revisions_engine_database.php',
            'adminzone/pages/modules/admin_revisions.php',
            'themes/default/images/icons/24x24/buttons/revisions.png',
            'themes/default/images/icons/48x48/buttons/revisions.png',
            'themes/default/images/icons/24x24/buttons/undo.png',
            'themes/default/images/icons/48x48/buttons/undo.png',
            'themes/default/templates/REVISIONS_SCREEN.tpl',
            'themes/default/templates/REVISIONS_WRAP.tpl',
            'themes/default/templates/REVISIONS_DIFF_ICON.tpl',
            'themes/default/templates/REVISION_UNDO.tpl',
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
            // Revisions
            'templates/REVISIONS_WRAP.tpl' => 'administrative__show_revisions_wrap',
            'templates/REVISIONS_DIFF_ICON.tpl' => 'administrative__show_revision_diff_icon',
            'templates/REVISIONS_SCREEN.tpl' => 'revisions_screen',
            'templates/REVISION_UNDO.tpl' => 'revision_undo',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__show_revisions_wrap()
    {
        return array(
            lorem_globalise(do_lorem_template('REVISIONS_WRAP', array(
                'RESULTS' => placeholder_table(),
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
    public function tpl_preview__administrative__show_revision_diff_icon()
    {
        return array(
            lorem_globalise(do_lorem_template('REVISIONS_DIFF_ICON', array(
                'RENDERED_DIFF' => lorem_phrase(),
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
    public function tpl_preview__revisions_screen()
    {
        return array(
            lorem_globalise(do_lorem_template('REVISIONS_SCREEN', array(
                'TITLE' => lorem_title(),
                'RESULTS' => lorem_phrase(),
                'INCLUDE_FILTER_FORM' => true,
                'RESOURCE_TYPES' => array(lorem_phrase()),
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
    public function tpl_preview__revision_undo()
    {
        return array(
            lorem_globalise(do_lorem_template('REVISION_UNDO', array(
            )), null, '', true)
        );
    }
}
