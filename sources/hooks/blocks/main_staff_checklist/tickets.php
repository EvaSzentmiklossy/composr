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
 * @package    tickets
 */

/**
 * Hook class.
 */
class Hook_checklist_tickets
{
    /**
     * Find items to include on the staff checklist.
     *
     * @return array An array of tuples: The task row to show, the number of seconds until it is due (or null if not on a timer), the number of things to sort out (or null if not on a queue), The name of the config option that controls the schedule (or null if no option).
     */
    public function run()
    {
        if (!addon_installed('tickets')) {
            return array();
        }

        require_lang('tickets');
        require_code('tickets');
        require_code('tickets2');

        $outstanding = 0;

        $tickets = get_tickets(get_member(), null, false, true);
        if (!is_null($tickets)) {
            foreach ($tickets as $topic) {
                if ($topic['closed'] == 0) {
                    $outstanding++;
                }
            }
        }

        if ($outstanding > 0) {
            $status = do_template('BLOCK_MAIN_STAFF_CHECKLIST_ITEM_STATUS_0', array('_GUID' => 'g578142633c6f3d37776e82a869deb91'));
        } else {
            $status = do_template('BLOCK_MAIN_STAFF_CHECKLIST_ITEM_STATUS_1', array('_GUID' => 'h578142633c6f3d37776e82a869deb91'));
        }

        $url = build_url(array('page' => 'tickets', 'type' => 'browse'), get_module_zone('tickets'));

        $tpl = do_template('BLOCK_MAIN_STAFF_CHECKLIST_ITEM', array('_GUID' => '8202af47a2f1d24675acbe4c6d20c8b4', 'URL' => $url, 'STATUS' => $status, 'TASK' => do_lang_tempcode('SUPPORT_TICKETS'), 'INFO' => do_lang_tempcode('NUM_QUEUE', escape_html(integer_format($outstanding)))));
        return array(array($tpl, null, $outstanding, null));
    }
}
