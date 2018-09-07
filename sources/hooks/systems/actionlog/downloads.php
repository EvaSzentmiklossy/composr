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
class Hook_actionlog_downloads extends Hook_actionlog
{
    /**
     * Get details of action log entry types handled by this hook.
     *
     * @return array Map of handler data in standard format
     */
    public function get_handlers()
    {
        if (!addon_installed('downloads')) {
            return array();
        }

        require_lang('downloads');

        return array(
            'ADD_DOWNLOAD_CATEGORY' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download_category',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => '_SEARCH:downloads:browse',
                    'EDIT_THIS_DOWNLOAD_CATEGORY' => '_SEARCH:cms_downloads:_edit_category:{ID}',
                    'ADD_DOWNLOAD_CATEGORY' => '_SEARCH:cms_downloads:add_category',
                    'ADD_DOWNLOAD' => '_SEARCH:cms_downloads:add:cat={ID}',
                ),
            ),
            'EDIT_DOWNLOAD_CATEGORY' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download_category',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => '_SEARCH:downloads:browse',
                    'EDIT_THIS_DOWNLOAD_CATEGORY' => '_SEARCH:cms_downloads:_edit_category:{ID}',
                    'ADD_DOWNLOAD_CATEGORY' => '_SEARCH:cms_downloads:add_category',
                    'ADD_DOWNLOAD' => '_SEARCH:cms_downloads:add:cat={ID}',
                ),
            ),
            'DELETE_DOWNLOAD_CATEGORY' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download_category',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_DOWNLOAD_CATEGORY' => '_SEARCH:cms_downloads:add_category',
                ),
            ),
            'ADD_DOWNLOAD_LICENCE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download_licence',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'EDIT_THIS_DOWNLOAD_LICENCE' => '_SEARCH:cms_downloads:_edit_other:{ID}',
                    'ADD_DOWNLOAD_LICENCE' => '_SEARCH:cms_downloads:add_other',
                ),
            ),
            'EDIT_DOWNLOAD_LICENCE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download_licence',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'EDIT_THIS_DOWNLOAD_LICENCE' => '_SEARCH:cms_downloads:_edit_other:{ID}',
                    'ADD_DOWNLOAD_LICENCE' => '_SEARCH:cms_downloads:add_other',
                ),
            ),
            'DELETE_DOWNLOAD_LICENCE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download_licence',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_DOWNLOAD_LICENCE' => '_SEARCH:cms_downloads:add_other',
                ),
            ),
            'ADD_DOWNLOAD' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => '_SEARCH:downloads:entry:{ID}',
                    'EDIT_THIS_DOWNLOAD' => '_SEARCH:cms_downloads:_edit:{ID}',
                    'ADD_DOWNLOAD' => '_SEARCH:cms_downloads:add:cat={CAT,OPTIONAL}',
                ),
            ),
            'EDIT_DOWNLOAD' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => '_SEARCH:downloads:entry:{ID}',
                    'EDIT_THIS_DOWNLOAD' => '_SEARCH:cms_downloads:_edit:{ID}',
                    'ADD_DOWNLOAD' => '_SEARCH:cms_downloads:add:cat={CAT,OPTIONAL}',
                ),
            ),
            'DELETE_DOWNLOAD' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_DOWNLOAD' => '_SEARCH:cms_downloads:add',
                ),
            ),
            'FILESYSTEM_DOWNLOADS' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'DOWNLOADS_HOME' => '_SEARCH:downloads',
                ),
            ),
            'FTP_DOWNLOADS' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'DOWNLOADS_HOME' => '_SEARCH:downloads',
                ),
            ),
        );
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
            case 'ADD_DOWNLOAD':
            case 'EDIT_DOWNLOAD':
                $category_id = $GLOBALS['SITE_DB']->query_select_value_if_there('download_downloads', 'category_id', array('id' => intval($identifier)));
                if ($category_id !== null) {
                    $bindings += array(
                        'CAT' => strval($category_id),
                    );
                }
                break;
        }
    }
}