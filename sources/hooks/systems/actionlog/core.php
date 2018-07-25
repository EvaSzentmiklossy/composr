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
 * @package    core
 */

/**
 * Hook class.
 */
class Hook_actionlog_core extends Hook_actionlog
{
    /**
     * Get details of action log entry types handled by this hook. For internal use, although may be used by the base class.
     *
     * @return array Map of handler data in standard format
     */
    protected function get_handlers()
    {
        require_lang('zones');
        require_lang('addons');
        require_lang('staff_checklist');
        require_lang('cleanup');
        require_lang('config');
        require_lang('lang');
        require_lang('menus');
        require_lang('notifications');
        require_lang('permissions');
        require_lang('themes');

        return array(
            'ADD_ZONE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'zone',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => '{ID}:',
                    'EDIT_THIS_ZONE' => '_SEARCH:admin_zones:_edit:{ID}',
                    'ZONE_EDITOR' => '_SEARCH:admin_zones:_editor:{ID}',
                    'ADD_ZONE' => '_SEARCH:admin_zones:add',
                ),
            ),
            'EDIT_ZONE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'zone',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => '{ID}:',
                    'EDIT_THIS_ZONE' => '_SEARCH:admin_zones:_edit:{ID}',
                    'ZONE_EDITOR' => '_SEARCH:admin_zones:_editor:{ID}',
                    'ADD_ZONE' => '_SEARCH:admin_zones:add',
                ),
            ),
            'DELETE_ZONE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'zone',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_ZONE' => '_SEARCH:admin_zones:add',
                ),
            ),
            'COMCODE_PAGE_EDIT' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'comcode_page',
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'VIEW' => '{1}:{0}',
                    'COMCODE_PAGE_EDIT_THIS' => '_SEARCH:cms_comcode_pages:_edit:page_link={1}%3A{0}',
                    'COMCODE_PAGE_MANAGEMENT' => '_SEARCH:cms_comcode_pages',
                ),
            ),
            'MOVE_PAGES' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                ),
            ),
            'DELETE_PAGES' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'COMCODE_PAGE_MANAGEMENT' => '_SEARCH:cms_comcode_pages',
                ),
            ),
            'EXPORT_ADDON' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 1,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'ADDONS' => '_SEARCH:admin_addons',
                ),
            ),
            'INSTALL_ADDON' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 1,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'ADDONS' => '_SEARCH:admin_addons',
                ),
            ),
            'UNINSTALL_ADDON' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 1,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'ADDONS' => '_SEARCH:admin_addons',
                ),
            ),
            'CHECK_LIST_ADD' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'DASHBOARD' => 'adminzone:',
                ),
            ),
            'CHECK_LIST_DELETE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'DASHBOARD' => 'adminzone:',
                ),
            ),
            'CHECK_LIST_MARK_DONE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'DASHBOARD' => 'adminzone:',
                ),
            ),
            'CHECK_LIST_MARK_UNDONE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'DASHBOARD' => 'adminzone:',
                ),
            ),
            'STAFF_LINKS' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'DASHBOARD' => 'adminzone:',
                ),
            ),
            'SITE_WATCHLIST' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'DASHBOARD' => 'adminzone:',
                ),
            ),
            'NOTES' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'DASHBOARD' => 'adminzone:',
                ),
            ),
            'CLEANUP_TOOLS' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'CLEANUP_TOOLS' => '_SEARCH:admin_cleanup',
                ),
            ),
            'CONFIGURATION' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'OPTION_CATEGORY' => '_SEARCH:admin_config:category:{ID}',
                    'CONFIGURATION' => '_SEARCH:admin_config',
                ),
            ),
            'TRANSLATE_CODE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'TRANSLATE_CODE' => '_SEARCH:admin_lang',
                ),
            ),
            'TRANSLATE_CONTENT' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'TRANSLATE_CONTENT' => multi_lang_content() ? '_SEARCH:admin_lang:content' : null,
                ),
            ),
            'ADD_MENU' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'menu',
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'EDIT_MENU' => '_SEARCH:admin_menus:_edit:{ID}',
                ),
            ),
            'EDIT_MENU' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'menu',
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'EDIT_MENU' => '_SEARCH:admin_menus:_edit:{ID}',
                ),
            ),
            'DELETE_MENU' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'menu',
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'MENU_MANAGEMENT' => '_SEARCH:admin_menus',
                ),
            ),
            'ADD_MENU_ITEM' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'menu_item',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'EDIT_MENU' => 'TODO',
                ),
            ),
            'EDIT_MENU_ITEM' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'menu_item',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'EDIT_MENU' => 'TODO',
                ),
            ),
            'DELETE_MENU_ITEM' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'menu_item',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'EDIT_MENU' => 'TODO',
                ),
            ),
            'NOTIFICATIONS_LOCKDOWN' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'NOTIFICATIONS_LOCKDOWN' => '_SEARCH:admin_notifications',
                ),
            ),
            'PRIVILEGES' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'PRIVILEGES' => '_SEARCH:admin_permissions:privileges',
                    'PERMISSIONS_TREE' => '_SEARCH:admin_permissions',
                ),
            ),
            'PAGE_ACCESS' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'PAGE_ACCESS' => '_SEARCH:admin_permissions:page',
                    'PERMISSIONS_TREE' => '_SEARCH:admin_permissions',
                ),
            ),
            'ADD_THEME' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'EDIT_THEME' => '_SEARCH:admin_themes:_edit_theme:{ID}',
                    'THEMEWIZARD' => '_SEARCH:admin_themewizard',
                    'MANAGE_THEMES' => '_SEARCH:admin_themes',
                ),
            ),
            'EDIT_THEME' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'EDIT_THEME' => '_SEARCH:admin_themes:_edit_theme:{ID}',
                    'MANAGE_THEMES' => '_SEARCH:admin_themes',
                ),
            ),
            'DELETE_THEME' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'MANAGE_THEMES' => '_SEARCH:admin_themes',
                ),
            ),
            'COPY_THEME' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 1,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'EDIT_THEME' => '_SEARCH:admin_themes:_edit_theme:{ID}',
                    'MANAGE_THEMES' => '_SEARCH:admin_themes',
                ),
            ),
            'RENAME_THEME' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 1,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'EDIT_THEME' => '_SEARCH:admin_themes:_edit_theme:{ID}',
                    'MANAGE_THEMES' => '_SEARCH:admin_themes',
                ),
            ),
            'EDIT_CSS' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'EDIT_CSS' => 'TODO',
                    'MANAGE_THEMES' => '_SEARCH:admin_themes',
                ),
            ),
            'EDIT_TEMPLATE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'EDIT_TEMPLATE' => 'TODO',
                    'MANAGE_THEMES' => '_SEARCH:admin_themes',
                ),
            ),
            'ADD_THEME_IMAGE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'EDIT_THEME_IMAGE' => '_SEARCH:admin_themes:_edit_image:{ID}:theme={1}',
                    'ADD_THEME_IMAGE' => '_SEARCH:admin_themes:add_image:theme={1}',
                ),
            ),
            'EDIT_THEME_IMAGE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'EDIT_THEME_IMAGE' => '_SEARCH:admin_themes:_edit_image:{ID}:theme={1}',
                    'ADD_THEME_IMAGE' => '_SEARCH:admin_themes:add_image:theme={1}',
                ),
            ),
            'DELETE_THEME_IMAGE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'ADD_THEME_IMAGE' => TODO ? '_SEARCH:admin_themes:add_image:theme={1}' : null,
                ),
            ),
            'CLEANUP_TOOLS' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'CLEANUP_TOOLS' => '_SEARCH:admin_cleanup',
                ),
            ),
            'DELETE_TRACKBACKS' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'MANAGE_TRACKBACKS' => '_SEARCH:admin_trackbacks',
                ),
            ),
            'GROUP_MEMBER_TIMEOUTS' => array)
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'GROUP_MEMBER_TIMEOUTS' => '_SEARCH:admin_group_member_timeouts',
                ),
            ),
            'FU_OPEN_SITE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'FU_UPGRADER_TITLE' => '_SEARCH:admin_config:upgrader',
                ),
            ),
            'FU_CLOSE_SITE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'FU_UPGRADER_TITLE' => '_SEARCH:admin_config:upgrader',
                ),
            ),
            'FU_DOWNLOAD' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'FU_UPGRADER_TITLE' => '_SEARCH:admin_config:upgrader',
                ),
            ),
            'FU_DATABASE_UPGRADE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'FU_UPGRADER_TITLE' => '_SEARCH:admin_config:upgrader',
                ),
            ),
        );
    }
}
