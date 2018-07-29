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
 * @package    core
 */

/**
 * Hook class.
 */
class Hook_actionlog_core extends Hook_actionlog
{
    /**
     * Get details of action log entry types handled by this hook.
     *
     * @return array Map of handler data in standard format
     */
    public function get_handlers()
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
        require_lang('upgrade');
        require_lang('group_member_timeouts');
        require_lang('trackbacks');

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
                    'COMCODE_PAGE_EDIT_THIS' => '_SEARCH:cms_comcode_pages:_edit:page_link={1}%3A{0}:lang=' . get_site_default_lang(),
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
                    'EDIT_LINK' => '{CONFIG_URL}',
                    'OPTION_CATEGORIES' => '_SEARCH:admin_config',
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
                    'EDIT_MENU' => '_SEARCH:admin_menus:edit:{ID}',
                ),
            ),
            'EDIT_MENU' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'menu',
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'EDIT_MENU' => '_SEARCH:admin_menus:edit:{ID}',
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
                    'EDIT_MENU' => '_SEARCH:admin_menus:edit:{MENU}',
                ),
            ),
            'EDIT_MENU_ITEM' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'menu_item',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'EDIT_MENU' => '_SEARCH:admin_menus:edit:{MENU}',
                ),
            ),
            'DELETE_MENU_ITEM' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'menu_item',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'EDIT_MENU' => '_SEARCH:admin_menus',
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
                    'EDIT_THEME' => '_SEARCH:admin_themes:edit_theme:theme={ID}',
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
                    'EDIT_THEME' => '_SEARCH:admin_themes:edit_theme:theme={ID}',
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
                    'EDIT_THEME' => '_SEARCH:admin_themes:edit_theme:theme={ID}',
                    'MANAGE_THEMES' => '_SEARCH:admin_themes',
                ),
            ),
            'RENAME_THEME' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 1,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'EDIT_THEME' => '_SEARCH:admin_themes:edit_theme:theme={ID}',
                    'MANAGE_THEMES' => '_SEARCH:admin_themes',
                ),
            ),
            'EDIT_TEMPLATES' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'EDIT_TEMPLATE' => '_SEARCH:admin_themes:edit_template:f0file={0}:theme={1}',
                    'MANAGE_THEMES' => '_SEARCH:admin_themes',
                ),
            ),
            'ADD_THEME_IMAGE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'EDIT_THEME_IMAGE' => '_SEARCH:admin_themes:edit_image:{ID}:theme={1}:lang=' . get_site_default_lang(),
                    'ADD_THEME_IMAGE' => '_SEARCH:admin_themes:add_image:theme={1}',
                ),
            ),
            'EDIT_THEME_IMAGE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'EDIT_THEME_IMAGE' => '_SEARCH:admin_themes:edit_image:{ID}:theme={1}:lang=' . get_site_default_lang(),
                    'ADD_THEME_IMAGE' => '_SEARCH:admin_themes:add_image:theme={1}',
                ),
            ),
            'DELETE_THEME_IMAGE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'ADD_THEME_IMAGE' => '_SEARCH:admin_themes:add_image:theme={1}',
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
            'GROUP_MEMBER_TIMEOUTS' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'GROUP_MEMBER_TIMEOUTS' => '_SEARCH:admin_group_member_timeouts',
                ),
            ),
            'UPGRADER_OPEN_SITE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'UPGRADER_UPGRADER_TITLE' => '_SEARCH:admin_config:upgrader',
                ),
            ),
            'UPGRADER_CLOSE_SITE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'UPGRADER_UPGRADER_TITLE' => '_SEARCH:admin_config:upgrader',
                ),
            ),
            'UPGRADER_DOWNLOAD' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'UPGRADER_UPGRADER_TITLE' => '_SEARCH:admin_config:upgrader',
                ),
            ),
            'UPGRADER_DATABASE_UPGRADE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'UPGRADER_UPGRADER_TITLE' => '_SEARCH:admin_config:upgrader',
                ),
            ),
        );
    }

    /**
     * Get written context for an action log entry handled by this hook.
     *
     * @param  array $actionlog_row Action log row
     * @param  array $handler_data Handler data
     * @param  ?string $identifier Identifier (null: none)
     * @return string Written context
     */
    protected function get_written_context($actionlog_row, $handler_data, $identifier)
    {
        switch ($actionlog_row['the_type']) {
            case 'CONFIGURATION':
                $_hook = 'hooks/systems/config/' . $identifier;
                if ((!is_file(get_file_base() . '/sources/' . $_hook . '.php')) && (!is_file(get_file_base() . '/sources_custom/' . $_hook . '.php'))) {
                    return $identifier;
                }
                require_code('hooks/systems/config/' . filter_naughty_harsh($identifier));
                $ob = object_factory('Hook_config_' . filter_naughty_harsh($identifier), true);
                if ($ob === null) {
                    return $identifier;
                }
                $option = $ob->get_details();
                return do_lang($option['human_name']);

            case 'ADD_THEME':
            case 'DELETE_THEME':
                $theme = $actionlog_row['param_a'];
                $path = get_custom_file_base() . '/themes/' . $theme . '/theme.ini';
                if (!is_file($path)) {
                    $path = get_file_base() . '/themes/' . $theme . '/theme.ini';
                }
                if (is_file($path)) {
                    $details = better_parse_ini_file($path);
                    if (array_key_exists('title', $details)) {
                        return $details['title'];
                    }
                }
                return $theme;

            case 'COPY_THEME':
            case 'RENAME_THEME':
                $theme = $actionlog_row['param_b'];
                $path = get_custom_file_base() . '/themes/' . $theme . '/theme.ini';
                if (!is_file($path)) {
                    $path = get_file_base() . '/themes/' . $theme . '/theme.ini';
                }
                if (is_file($path)) {
                    $details = better_parse_ini_file($path);
                    if (array_key_exists('title', $details)) {
                        return $details['title'];
                    }
                }
                return $theme;

            case 'CLEANUP_TOOLS':
                $hook = $actionlog_row['param_a'];
                if ($hook != '') {
                    $_hook = 'hooks/systems/cleanup/' . $hook;
                    if ((!is_file(get_file_base() . '/sources/' . $_hook . '.php')) && (!is_file(get_file_base() . '/sources_custom/' . $_hook . '.php'))) {
                        return $hook;
                    }
                    require_code($_hook);
                    $ob = object_factory('Hook_cleanup_' . $hook, true);
                    if ($ob === null) {
                        return $hook;
                    }
                    $info = $ob->info();
                    return $info['title']->evaluate();
                }
                break;

            case 'COMCODE_PAGE_EDIT':
                $written_context = $actionlog_row['param_b'] . ':' . $actionlog_row['param_a'];
                return $written_context;

            case 'EDIT_CSS':
                $written_context = do_lang('SOMETHING_IN', $actionlog_row['param_b'], $actionlog_row['param_a']);
                return $written_context;

            case 'EDIT_TEMPLATES':
                $written_context = do_lang('SOMETHING_IN', $actionlog_row['param_a'], $actionlog_row['param_b']);
                return $written_context;

            case 'MOVE_PAGES':
                $written_context = do_lang('SOMETHING_TO', $actionlog_row['param_a'], $actionlog_row['param_b']);
                return $written_context;
        }

        return parent::get_written_context($actionlog_row, $handler_data, $identifier);
    }

    /**
     * Get details of action log entry types handled by this hook.
     *
     * @param  array $actionlog_row Action log row
     * @param  ?string $identifier The identifier associated with this action log entry (null: unknown / none)
     * @param  ?string $written_context The written context associated with this action log entry (null: unknown / none)
     * @param  array $bindings Default bindings
     */
    protected function get_extended_actionlog_bindings($actionlog_row, $identifier, $written_context, &$bindings)
    {
        switch ($actionlog_row['the_type']) {
            case 'CONFIGURATION':
                require_code('config2');
                $bindings += array(
                    'CONFIG_URL' => config_option_url($identifier),
                );
                break;

            case 'ADD_MENU_ITEM':
            case 'EDIT_MENU_ITEM':
                $menu = $GLOBALS['SITE_DB']->query_select_value_if_there('menu_items', 'i_menu', array('id' => intval($identifier)));
                if ($menu !== null) {
                    $bindings += array(
                        'MENU' => $menu,
                    );
                }
                break;
        }
    }
}
