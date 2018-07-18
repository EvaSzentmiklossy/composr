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
 * @package    banners
 */

/**
 * Hook class.
 */
class Hook_actionlog_banners extends Hook_actionlog
{
    /**
     * Get details of action log entry types handled by this hook. For internal use, although may be used by the base class.
     *
     * @return array Map of handler data in standard format
     */
    protected function get_handlers()
    {
        if (!addon_installed('banners')) {
            return array();
        }

        require_lang('banners');

        return array(
            'ADD_BANNER_TYPE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'banner_type',
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'EDIT_THIS_BANNER_TYPE' => 'TODO',
                    'ADD_BANNER' => 'TODO',
                ),
            ),
            'EDIT_BANNER_TYPE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'banner_type',
                'identifier_index' => 1,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'EDIT_THIS_BANNER_TYPE' => 'TODO',
                    'ADD_BANNER' => 'TODO',
                ),
            ),
            'DELETE_BANNER_TYPE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'banner_type',
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'ADD_BANNER_TYPE' => 'TODO',
                ),
            ),
            'ADD_BANNER' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'banner',
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'VIEW' => 'TODO',
                    'EDIT_THIS_BANNER' => 'TODO',
                    'ADD_BANNER' => 'TODO',
                ),
            ),
            'EDIT_BANNER' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'banner',
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'VIEW' => 'TODO',
                    'EDIT_THIS_BANNER' => 'TODO',
                    'ADD_BANNER' => 'TODO',
                ),
            ),
            'DELETE_BANNER' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'banner',
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'ADD_BANNER' => 'TODO',
                ),
            ),
        );
    }
}
