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
 * @package    core_adminzone_dashboard
 */

/**
 * Block class.
 */
class Block_main_staff_checklist
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 4;
        $info['locked'] = false;
        $info['parameters'] = array();
        $info['update_require_upgrade'] = true;

        return $info;
    }

    /**
     * Find caching details for the block.
     *
     * @return ?array Map of cache details (cache_on and ttl) (null: block is disabled).
     */
    public function caching_environment()
    {
        $info = array();
        $info['cache_on'] = '(count($_POST)>0)?null:array()'; // No cache on POST as this is when we save text data
        $info['ttl'] = (get_value('no_block_timeout') === '1') ? 60 * 60 * 24 * 365 * 5/*5 year timeout*/ : 60 * 5;
        return $info;
    }

    /**
     * Install the block.
     *
     * @param  ?integer $upgrade_from What version we're upgrading from (null: new install)
     * @param  ?integer $upgrade_from_hack What hack version we're upgrading from (null: new-install/not-upgrading-from-a-hacked-version)
     */
    public function install($upgrade_from = null, $upgrade_from_hack = null)
    {
        if ((is_null($upgrade_from)) || ($upgrade_from < 4)) {
            $GLOBALS['SITE_DB']->create_table('staff_checklist_cus_tasks', array(
                'id' => '*AUTO',
                'task_title' => 'LONG_TEXT',
                'add_date' => 'TIME',
                'recur_interval' => 'INTEGER',
                'recur_every' => 'ID_TEXT',
                'task_is_done' => '?TIME'
            ));

            require_lang('staff_checklist');
            $tasks = array(
                do_lang('CHECKLIST_INITIAL_TASK_STRUCTURE'),
                do_lang('CHECKLIST_INITIAL_TASK_THEME'),
                '[page="adminzone:admin_themes:edit_image:favicon"]' . do_lang('CHECKLIST_INITIAL_TASK_FAVICON') . '[/page]',
                '[page="adminzone:admin_themes:edit_image:webclipicon"]' . do_lang('CHECKLIST_INITIAL_TASK_WEBCLIP') . '[/page]',
                do_lang('CHECKLIST_INITIAL_TASK_CONTENT'),
                '[page="adminzone:admin_themes:edit_image:logo/standalone_logo:theme=default"]' . do_lang('CHECKLIST_INITIAL_TASK_MAIL_LOGO') . '[/page]',
                '[page="adminzone:admin_themes:edit_templates:theme=default:f0file=templates/MAIL.tpl"]' . do_lang('CHECKLIST_INITIAL_TASK_MAIL') . '[/page]',
                '[page="adminzone:admin_themes:_edit_templates:theme=default:f0file=templates/MAIL.tpl"]' . do_lang('CHECKLIST_INITIAL_TASK_MAIL') . '[/page]',
                '[url="' . do_lang('CHECKLIST_INITIAL_TASK_GOOGLE_WEBMASTER_TOOLS') . '"]https://www.google.com/webmasters/tools/[/url]',
                '[url="' . do_lang('CHECKLIST_INITIAL_TASK_DMOZ') . '"]http://www.dmoz.org/add.html[/url]',
                '[url="' . do_lang('CHECKLIST_INITIAL_TASK_UPTIME_MONITOR') . '"]https://uptimerobot.com/[/url]',
                // NB: Google and Bing submission is automatic, via Sitemaps feature
                '[html]<p style="margin: 0">Facebook user? Like Composr on Facebook:</p><iframe src="https://compo.sr/uploads/website_specific/compo.sr/facebook.html" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:430px; height:20px;" allowTransparency="true"></iframe>[/html]',
                '[url="Consider helping out with the Composr project"]' . get_brand_page_url(array('page' => 'contributions'), 'site') . '[/url]',
            );
            foreach ($tasks as $task) {
                $GLOBALS['SITE_DB']->query_insert('staff_checklist_cus_tasks', array(
                    'task_title' => $task,
                    'add_date' => time(),
                    'recur_interval' => 0,
                    'recur_every' => '',
                    'task_is_done' => null,
                ));
            }
        }
    }

    /**
     * Uninstall the block.
     */
    public function uninstall()
    {
        $GLOBALS['SITE_DB']->drop_table_if_exists('staff_checklist_cus_tasks');
    }

    /**
     * Execute the block.
     *
     * @param  array $map A map of parameters.
     * @return Tempcode The result of execution.
     */
    public function run($map)
    {
        require_javascript('ajax');

        require_lang('dates');

        // Handle custom tasks
        $new_task = post_param_string('new_task', null);
        $recur_interval = post_param_integer('recur_interval', 0);
        $recur_every = post_param_string('recur_every', null);
        if ((!is_null($new_task)) && (!is_null($recur_interval)) && (!is_null($recur_every))) {
            $GLOBALS['SITE_DB']->query_insert('staff_checklist_cus_tasks', array('task_title' => $new_task, 'add_date' => time(), 'recur_interval' => $recur_interval, 'recur_every' => $recur_every, 'task_is_done' => null));
            decache('main_staff_checklist');
        }
        $custom_tasks = new Tempcode();
        $rows = $GLOBALS['SITE_DB']->query_select('staff_checklist_cus_tasks', array('*'));
        foreach ($rows as $r) {
            $recur_every = '';
            switch ($r['recur_every']) {
                case 'mins':
                    $recur_every = do_lang('DPLU_MINUTES');
                    break;
                case 'hours':
                    $recur_every = do_lang('DPLU_HOURS');
                    break;
                case 'days':
                    $recur_every = do_lang('DPLU_DAYS');
                    break;
                case 'months':
                    $recur_every = do_lang('DPLU_MONTHS');
                    break;
            }
            $custom_tasks->attach(do_template('BLOCK_MAIN_STAFF_CHECKLIST_CUSTOM_TASK', array(
                '_GUID' => 'fa747347ad7b9eb1a7f3f54867154db4',
                'TASK_TITLE' => comcode_to_tempcode($r['task_title']),
                'ADD_DATE' => display_time_period($r['add_date']),
                'RECUR_INTERVAL' => ($r['recur_interval'] == 0) ? '' : integer_format($r['recur_interval']),
                'RECUR_EVERY' => $recur_every,
                'TASK_DONE' => ((!is_null($r['task_is_done'])) && (($r['recur_interval'] == 0) || (($r['recur_every'] != 'mins') || (time() < $r['task_is_done'] + 60 * $r['recur_interval'])) && (($r['recur_every'] != 'hours') || (time() < $r['task_is_done'] + 60 * 60 * $r['recur_interval'])) && (($r['recur_every'] != 'days') || (time() < $r['task_is_done'] + 24 * 60 * 60 * $r['recur_interval'])) && (($r['recur_every'] != 'months') || (time() < $r['task_is_done'] + 31 * 24 * 60 * 60 * $r['recur_interval'])))) ? 'checklist1' : 'not_completed',
                'ID' => strval($r['id']),
                'ADD_TIME' => do_lang_tempcode('DAYS_AGO', escape_html(integer_format(intval(round(floatval(time() - $r['add_date']) / 60.0 / 60.0 / 24.0))))),
            )));
        }

        require_lang('staff_checklist');
        require_css('adminzone_dashboard');

        // Handle built in items

        $rets_no_times = array();
        $rets_todo_counts = array();
        $rets_dates = array();

        $_hooks = find_all_hooks('blocks', 'main_staff_checklist');
        ksort($_hooks);
        foreach (array_keys($_hooks) as $hook) {
            require_code('hooks/blocks/main_staff_checklist/' . filter_naughty_harsh($hook));
            $object = object_factory('Hook_checklist_' . filter_naughty_harsh($hook), true);
            if (is_null($object)) {
                continue;
            }
            $ret = $object->run();
            if ((!is_null($ret)) && (count($ret) != 0)) {
                foreach ($ret as $r) {
                    if ((is_null($r[1])) && (is_null($r[2]))) {
                        $rets_no_times[] = $r;
                    } elseif (!is_null($r[2])) {
                        $rets_todo_counts[] = $r;
                    } else {
                        $rets_dates[] = $r;
                    }
                }
            }
        }

        sort_maps_by($rets_todo_counts, '!2');
        sort_maps_by($rets_dates, '1');

        $out_no_times = new Tempcode();
        foreach ($rets_no_times as $item) {
            $out_no_times->attach($item[0]);
        }
        $out_todo_counts = new Tempcode();
        foreach ($rets_todo_counts as $item) {
            $out_todo_counts->attach($item[0]);
        }
        $out_dates = new Tempcode();
        foreach ($rets_dates as $item) {
            $out_dates->attach($item[0]);
        }

        return do_template('BLOCK_MAIN_STAFF_CHECKLIST', array('_GUID' => 'aefbca8252dc1d6edc44fc6d1e78b3ec', 'URL' => get_self_url(), 'DATES' => $out_dates, 'NO_TIMES' => $out_no_times, 'TODO_COUNTS' => $out_todo_counts, 'CUSTOM_TASKS' => $custom_tasks));
    }
}

/**
 * Work out when an action should happen, and last happened.
 *
 * @param  ?integer $seconds_ago The number of seconds ago since it last happened (null: never happened) OR If $recur_hours is null then the number of seconds until it happens (null: won't happen)
 * @param  ?integer $recur_hours It should be done every this many hours (null: never happened)
 * @return array A pair: Tempcode to display, and the number of seconds to go until the action should happen
 */
function staff_checklist_time_ago_and_due($seconds_ago, $recur_hours = null)
{
    if (is_null($recur_hours)) { // None recurring
        $seconds_to_go = $seconds_ago; // Actually, if only one parameter given, meaning is different
        $seconds_ago = mixed();
        if (is_null($seconds_to_go)) {
            return array(do_lang_tempcode('DUE_NOT'), 1000000);
        }
    } else { // Recurring
        if (is_null($seconds_ago)) {
            return array(do_lang_tempcode('DUE_NOW'), 0); // Due for first time now
        } else {
            $seconds_to_go = $recur_hours * 60 * 60 - $seconds_ago;
        }
    }

    if ($seconds_to_go == 0) {
        return array(do_lang_tempcode('DUE_NOW'), 0); // Due for first time now (this is a special encoding for non-recurring tasks that still need doing on some form of schedule and need doing for first time now)
    }
    if ($seconds_to_go > 0) {
        return array(do_lang_tempcode('DUE_TIME', is_null($seconds_ago) ? do_lang_tempcode('NA_EM') : make_string_tempcode(escape_html(display_time_period($seconds_ago))), make_string_tempcode(escape_html(display_time_period($seconds_to_go)))), $seconds_to_go);
    } else {
        return array(do_lang_tempcode('DUE_TIME_AGO', is_null($seconds_ago) ? do_lang_tempcode('NA_EM') : make_string_tempcode(escape_html(display_time_period($seconds_ago))), make_string_tempcode(escape_html(display_time_period(-$seconds_to_go)))), $seconds_to_go);
    }
}
