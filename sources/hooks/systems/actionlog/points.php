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
 * @package    points
 */

/**
 * Hook class.
 */
class Hook_actionlog_points extends Hook_actionlog
{
    /**
     * Get details of action log entry types handled by this hook.
     *
     * @return array Map of handler data in standard format
     */
    public function get_handlers()
    {
        if (!addon_installed('points')) {
            return array();
        }

        require_lang('points');

        return array(
            'UNDO_CHARGE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'member',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW_PROFILE' => array('FORUM_DRIVER__PROFILE_URL', '{ID}'),
                    'GIFT_TRANSACTIONS' => '_SEARCH:admin_points',
                ),
            ),
        );
    }
}
