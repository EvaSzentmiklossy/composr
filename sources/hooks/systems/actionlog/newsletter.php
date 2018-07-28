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
 * @package    newsletter
 */

/**
 * Hook class.
 */
class Hook_actionlog_newsletter extends Hook_actionlog
{
    /**
     * Get details of action log entry types handled by this hook.
     *
     * @return array Map of handler data in standard format
     */
    public function get_handlers()
    {
        if (!addon_installed('newsletter')) {
            return array();
        }

        require_lang('newsletter');

        return array(
            'ADD_NEWSLETTER' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'newsletter',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'EDIT_THIS_NEWSLETTER' => '_SEARCH:admin_newsletter:_edit:{ID}',
                    'ADD_NEWSLETTER' => '_SEARCH:admin_newsletter:add',
                ),
            ),
            'EDIT_NEWSLETTER' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'newsletter',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'EDIT_THIS_NEWSLETTER' => '_SEARCH:admin_newsletter:_edit:{ID}',
                    'ADD_NEWSLETTER' => '_SEARCH:admin_newsletter:add',
                ),
            ),
            'DELETE_NEWSLETTER' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'newsletter',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_NEWSLETTER' => '_SEARCH:admin_newsletter:add',
                ),
            ),
            'ADD_PERIODIC_NEWSLETTER' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'periodic_newsletter',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_PERIODIC_NEWSLETTER' => '_SEARCH:admin_newsletter:whatsnew',
                ),
            ),
            'EDIT_PERIODIC_NEWSLETTER' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'periodic_newsletter',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_PERIODIC_NEWSLETTER' => '_SEARCH:admin_newsletter:whatsnew',
                ),
            ),
            'DELETE_PERIODIC_NEWSLETTER' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'periodic_newsletter',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_PERIODIC_NEWSLETTER' => '_SEARCH:admin_newsletter:whatsnew',
                ),
            ),
            'IMPORT_NEWSLETTER_SUBSCRIBERS' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'MANAGE_NEWSLETTER' => '_SEARCH:admin_newsletter',
                ),
            ),
            'NEWSLETTER_SEND' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'MANAGE_NEWSLETTER' => '_SEARCH:admin_newsletter',
                ),
            ),
        );
    }
}