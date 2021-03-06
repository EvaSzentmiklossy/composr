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
 * @package    core_database_drivers
 */

/**
 * Hook class.
 */
class Hook_addon_registry_core_database_drivers
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
        return 'The code layer that binds the software to one of various different kinds of database software.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_webhosting',
            'tut_install',
            'tut_adv_install',
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
        return 'themes/default/images/icons/48x48/menu/_generic_admin/component.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'sources/hooks/systems/addon_registry/core_database_drivers.php',
            'sources/database/shared/.htaccess',
            'sources/database/shared/index.html',
            'sources/database/.htaccess',
            'sources/database/access.php',
            'sources/database/database.ini',
            'sources/database/ibm.php',
            'sources/database/index.html',
            'sources/database/mysql.php',
            'sources/database/mysqli.php',
            'sources/database/mysql_pdo.php',
            'sources/database/mysql_dbx.php',
            'sources/database/oracle.php',
            'sources/database/postgresql.php',
            'sources/database/xml.php',
            'sources/database/shared/mysql.php',
            'sources/database/sqlite.php',
            'sources/database/shared/sqlserver.php',
            'sources/database/sqlserver.php',
            'sources/database/sqlserver_odbc.php',
            'sources/hooks/systems/cron/oracle.php',
        );
    }
}
