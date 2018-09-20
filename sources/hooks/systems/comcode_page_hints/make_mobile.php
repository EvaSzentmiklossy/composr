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
 * @package    core_rich_media
 */

/**
 * Hook class.
 */
class Hook_comcode_page_hints_make_mobile
{
    /**
     * Get details describing the page hint.
     *
     * @return ?array Map of details (null: UI disabled for this hint)
     */
    public function get_details()
    {
        require_lang('comcode');
        return array(
            'label' => do_lang_tempcode('PAGE_HINT_MAKE_MOBILE_LABEL'),
            'description' => do_lang_tempcode('PAGE_HINT_MAKE_MOBILE_DESCRIPTION'),
            'inverted' => false,
        );
    }
}
