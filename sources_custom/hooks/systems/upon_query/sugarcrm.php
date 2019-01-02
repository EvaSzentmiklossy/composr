<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    sugarcrm
 */

class Hook_upon_query_sugarcrm
{
    public function run_post($ob, $query, $max, $start, $fail_ok, $get_insert_id, $ret)
    {
        if (!addon_installed('sugarcrm')) {
            return;
        }

        if (!function_exists('curl_init')) {
            return;
        }

        if ($query[0] == 'S') {
            return;
        }

        if (!isset($GLOBALS['FORUM_DB'])) {
            return;
        }

        if (running_script('install')) {
            return;
        }

        if (strpos($query, 'f_member') === false) {
            return;
        }

        if (get_mass_import_mode()) {
            return;
        }

        $prefix = preg_quote($GLOBALS['FORUM_DB']->get_table_prefix(), '#');

        $matches = array();
        if (preg_match('#^INSERT INTO ' . $prefix . 'f_member_custom_fields .*\((\d+),#U', $query, $matches) != 0) {
            require_code('sugarcrm');

            if (!sugarcrm_configured()) {
                return;
            }

            require_code('tasks');
            $_title = do_lang('SUGARCRM_MEMBER_SYNC');
            call_user_func_array__long_task($_title, null, 'sugarcrm_sync_member', array(intval($matches[1]), $_GET, $_POST), false, false, false);

            return;
        }
    }
}
