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
 * @package    catalogues
 */

/**
 * Hook class.
 */
class Hook_actionlog_catalogues extends Hook_actionlog
{
    /**
     * Get details of action log entry types handled by this hook.
     *
     * @return array Map of handler data in standard format
     */
    public function get_handlers()
    {
        if (!addon_installed('catalogues')) {
            return array();
        }

        require_lang('catalogues');

        return array(
            'ADD_CATALOGUE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'catalogue',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => '_SEARCH:catalogues:index:{ID}',
                    'EDIT_THIS_CATALOGUE' => '_SEARCH:cms_catalogues:_edit_catalogue:{ID}',
                    'ADD_CATALOGUE_CATEGORY' => '_SEARCH:cms_catalogues:add_category:catalogue_name={ID}',
                    'ADD_CATALOGUE_ENTRY' => '_SEARCH:cms_catalogues:add_entry:catalogue_name={ID}',
                ),
            ),
            'EDIT_CATALOGUE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'catalogue',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => '_SEARCH:catalogues:index:{ID}',
                    'EDIT_THIS_CATALOGUE' => '_SEARCH:cms_catalogues:_edit_catalogue:{ID}',
                    'ADD_CATALOGUE_CATEGORY' => '_SEARCH:cms_catalogues:add_category:catalogue_name={ID}',
                    'ADD_CATALOGUE_ENTRY' => '_SEARCH:cms_catalogues:add_entry:catalogue_name={ID}',
                ),
            ),
            'DELETE_CATALOGUE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'catalogue',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_CATALOGUE_ENTRY' => '_SEARCH:cms_catalogues:add_catalogue',
                ),
            ),
            'ADD_CATALOGUE_CATEGORY' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'catalogue_category',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => '_SEARCH:catalogues:index:{ID}',
                    'EDIT_THIS_CATALOGUE_CATEGORY' => '_SEARCH:cms_catalogues:_edit_category:{ID}',
                    'ADD_CATALOGUE_CATEGORY' => '_SEARCH:cms_catalogues:add_category:catalogue_name={CATALOGUE_NAME}',
                    'ADD_CATALOGUE_ENTRY' => '_SEARCH:cms_catalogues:add_entry:category_id={ID}',
                ),
            ),
            'EDIT_CATALOGUE_CATEGORY' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'catalogue_category',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => '_SEARCH:catalogues:index:{ID}',
                    'EDIT_THIS_CATALOGUE_CATEGORY' => '_SEARCH:cms_catalogues:_edit_category:{ID}',
                    'ADD_CATALOGUE_CATEGORY' => '_SEARCH:cms_catalogues:add_category:catalogue_name={CATALOGUE_NAME}',
                    'ADD_CATALOGUE_ENTRY' => '_SEARCH:cms_catalogues:add_entry:category_id={ID}',
                ),
            ),
            'DELETE_CATALOGUE_CATEGORY' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'catalogue_category',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_CATALOGUE_CATEGORY' => '_SEARCH:cms_catalogues:add_category',
                ),
            ),
            'ADD_CATALOGUE_ENTRY' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'catalogue_entry',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => '_SEARCH:catalogues:entry:{ID}',
                    'EDIT_THIS_CATALOGUE_ENTRY' => '_SEARCH:cms_catalogues:_edit_entry:{ID}',
                    'ADD_CATALOGUE_ENTRY' => '_SEARCH:cms_catalogues:add_entry:category_id={CAT}',
                ),
            ),
            'EDIT_CATALOGUE_ENTRY' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'catalogue_entry',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => '_SEARCH:catalogues:entry:{ID}',
                    'EDIT_THIS_CATALOGUE_ENTRY' => '_SEARCH:cms_catalogues:_edit_entry:{ID}',
                    'ADD_CATALOGUE_ENTRY' => '_SEARCH:cms_catalogues:add_entry:category_id={CAT}',
                ),
            ),
            'DELETE_CATALOGUE_ENTRY' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'catalogue_entry',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_CATALOGUE_ENTRY' => '_SEARCH:cms_catalogues:add_entry',
                ),
            ),
            'IMPORT_CATALOGUE_ENTRIES' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'catalogue',
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'MANAGE_CATALOGUES' => '_SEARCH:cms_catalogues',
                    'VIEW' => '_SEARCH:catalogues:index:{ID}',
                ),
            ),
            'EXPORT_CATALOGUE_ENTRIES' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'catalogue',
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'MANAGE_CATALOGUES' => '_SEARCH:cms_catalogues',
                    'VIEW' => '_SEARCH:catalogues:index:{ID}',
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
            case 'ADD_CATALOGUE_CATEGORY':
            case 'EDIT_CATALOGUE_CATEGORY':
                $catalogue_name = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_categories', 'c_name', array('id' => intval($identifier)));
                if ($catalogue_name !== null) {
                    $bindings += array(
                        'CATALOGUE_NAME' => $catalogue_name,
                    );
                }
                break;

            case 'ADD_CATALOGUE_ENTRY':
            case 'EDIT_CATALOGUE_ENTRY':
                $cat = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_entries', 'cc_id', array('id' => intval($identifier)));
                if ($cat !== null) {
                    $bindings += array(
                        'CAT' => strval($cat),
                    );
                }
                break;
        }
    }
}
