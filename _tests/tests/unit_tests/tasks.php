<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    testing_platform
 */

/**
 * Composr test case class (unit testing).
 */
class tasks_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        require_code('files');
        require_code('tasks');
    }

    public function testNewsletterCSV()
    {
        $only = get_param_string('only', null);
        if (($only !== null) && ($only != 'testNewsletterCSV')) {
            return;
        }

        if (!addon_installed('newsletter')) {
            $this->assertTrue(false, 'Cannot run test without newsletter addon');
            return;
        }

        $tmp_path = cms_tempnam();

        cms_file_put_contents_safe($tmp_path, "Email,Name\ntest@example.com,Test");

        require_code('hooks/systems/tasks/import_newsletter_subscribers');
        $ob_import = new Hook_task_import_newsletter_subscribers();
        $ob_import->run(fallback_lang(), db_get_first_id(), true, $tmp_path);

        $this->establish_admin_session();
        $url = build_url(array('page' => 'admin_newsletter', 'type' => 'subscribers', 'id' => db_get_first_id(), 'lang' => fallback_lang(), 'csv' => 1), 'adminzone');
        $data = http_get_contents($url->evaluate(), array('cookies' => array(get_session_cookie() => get_session_id())));
        $this->assertTrue(strpos($data, 'test@example.com') !== false);

        file_put_contents($tmp_path, $data);
        $ob_import->run(fallback_lang(), db_get_first_id(), true, $tmp_path);
    }

    public function testCatalogueCSV()
    {
        $only = get_param_string('only', null);
        if (($only !== null) && ($only != 'testCatalogueCSV')) {
            return;
        }

        if (!addon_installed('catalogues')) {
            $this->assertTrue(false, 'Cannot run test without catalogues addon');
        }

        $test = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogues', 'c_name', array('c_name' => 'links'));
        if ($test === null) {
            $this->assertTrue(false, 'links catalogue not available, test cannot run');
            return;
        }

        $tmp_path = cms_tempnam();

        cms_file_put_contents_safe($tmp_path, "Title,URL,Description\nTestingABC,http://example.com,Test");

        require_code('hooks/systems/tasks/import_catalogue');
        $ob_import = new Hook_task_import_catalogue();
        $import_result = $ob_import->run('links', 'Title', 'add', 'leave', 'skip', '', '', '', true, true, true, $tmp_path);

        require_code('hooks/systems/tasks/export_catalogue');
        $ob_export = new Hook_task_export_catalogue();
        $results = $ob_export->run('links');
        $c = cms_file_get_contents_safe($results[1][1]);
        $this->assertTrue(strpos($c, 'TestingABC') !== false, 'Did not see our TestingABC record in: ' . $c . "\n\n" . serialize($import_result));

        $ob_import->run('links', 'Title', 'add', 'leave', 'skip', '', '', '', true, true, true, $results[1][1]);
    }

    public function testCalendarICal()
    {
        $only = get_param_string('only', null);
        $debugging = (get_param_integer('debug', 0) == 1);

        if (($only !== null) && ($only != 'testCalendarICal')) {
            return;
        }

        if (!addon_installed('calendar')) {
            $this->assertTrue(false, 'Cannot run test without calendar addon');
            return;
        }

        $this->establish_admin_session();

        require_code('calendar2');

        // Add complex event with start and recurrence
        $complex_event_id = add_calendar_event(8, 'daily', 3, 0, 'complex event', '', 3, 2010, 1, 10, 'day_of_month', 10, 15, 2010, 1, 10, 'day_of_month', 11, 15, null, 1, null, 1, 1, 1, 1, '', null, 0, null, null, null);

        if (get_db_type() == 'xml') {
            sleep(1);
        }

        // Add event with start only
        $simple_event_id = add_calendar_event(8, 'none', null, 0, 'simple event', '', 3, 2010, 1, 10, 'day_of_month', 10, 15, null, null, null, 'day_of_month', null, null, null, 1, null, 1, 1, 1, 1, '', null, 0, null, null, null);

        $last_rows_before = $GLOBALS['SITE_DB']->query_select('calendar_events', array('*'), array(), 'ORDER BY e_add_date DESC,id DESC', 2);
        $this->clean_event_rows_for_comparison($last_rows_before);

        require_code('calendar_ical');
        ob_start();
        output_ical(false);
        $ical = ob_get_contents();
        ob_end_clean();

        $temp_path = cms_tempnam();
        rename($temp_path, $temp_path . '.ics');
        $temp_path .= '.ics';
        file_put_contents($temp_path, $ical);

        /*
        This validator seems to be down now, so we implement a new one below
        $post_params = array('snip' => $ical);
        $url = 'http://severinghaus.org/projects/icv/';
        if ($result === null) {
            $this->assertTrue(false, 'ical validator is down?');
        } else {
            $this->assertTrue(strpos($result, 'Congratulations; your calendar validated!') !== false);
        }
        */

        $result = http_get_contents('https://ical-validator.herokuapp.com/validate/', array('trigger_error' => false));
        if ($result !== null) {
            /* Could not get this working with upload method
            $matches = array();
            preg_match('#<form id="id2" method="post" action="([^"]*)"#', $result, $matches);
            $rel_url = $matches[1];
            preg_match('#jsessionid=(\w+)#', $result, $matches);
            $session_id = $matches[1];
            $files = array('file' => $temp_path);
            $post_params = array('id2_hf_0' => '', 'Validate' => '');
            $cookies = array('JSESSIONID' => $session_id);
            $extra_headers = array();
            $url = qualify_url(html_entity_decode($rel_url, ENT_QUOTES), 'https://ical-validator.herokuapp.com/validate/');
            $result = http_get_contents($url, array('ignore_http_status' => $debugging, 'trigger_error' => false, 'files' => $files, 'post_params' => $post_params, 'cookies' => $cookies, 'extra_headers' => $extra_headers));
            */

            $matches = array();
            preg_match('#<form [^<>]*method="post" action="([^"]*snippetForm[^"]*)"#', $result, $matches);
            $rel_url = $matches[1];
            $post_params = array('snippet' => $ical);
            $url = qualify_url(html_entity_decode($rel_url, ENT_QUOTES), 'https://ical-validator.herokuapp.com/validate/');
            $result = http_get_contents($url, array('ignore_http_status' => $debugging, 'trigger_error' => false, 'post_params' => $post_params));
            if ($debugging) {
                @var_dump($url);
                @var_dump($result);
                exit();
            }
        }
        if ($result === null) {
            //Validator often down also so show no error $this->assertTrue(false, 'ical validator is down?');
        } else {
            $this->assertTrue((strpos($result, '1 results in 1 components') !== false) && (strpos($result, 'CRLF should be used for newlines')/*bug in validator*/ !== false));
        }

        delete_calendar_event($complex_event_id);
        delete_calendar_event($simple_event_id);

        $num_events_before = $GLOBALS['SITE_DB']->query_select_value('calendar_events', 'COUNT(*)');
        ical_import($temp_path);
        $num_events_after = $GLOBALS['SITE_DB']->query_select_value('calendar_events', 'COUNT(*)');
        $this->assertTrue($num_events_after > $num_events_before, 'Did not appear to import events (' . integer_format($num_events_after) . ' after, ' . integer_format($num_events_before) . ' before)');

        $_last_rows_after = $GLOBALS['SITE_DB']->query_select('calendar_events', array('*'), array(), 'ORDER BY e_add_date DESC,id DESC', 2);
        $last_rows_after = $_last_rows_after;
        $this->clean_event_rows_for_comparison($last_rows_after);

        $ok = ($last_rows_before == $last_rows_after);
        $this->assertTrue($ok, 'Our test events changed during the export/import cycle)');
        if ((!$ok) && (get_param_integer('debug', 0) == 1)) {
            @var_dump($last_rows_before);
            @var_dump($last_rows_after);
        }

        foreach ($_last_rows_after as $row) {
            delete_calendar_event($row['id']);
        }

        unlink($temp_path);
    }

    protected function clean_event_rows_for_comparison(&$rows)
    {
        foreach ($rows as &$row) {
            unset($row['id']);
            unset($row['e_add_date']);
            $row['e_title'] = get_translated_text($row['e_title']);
            unset($row['e_title__text_parsed']);
            $row['e_content'] = get_translated_text($row['e_content']);
            unset($row['e_content__text_parsed']);
        }
    }

    public function testMemberCSV()
    {
        $only = get_param_string('only', null);
        if (($only !== null) && ($only != 'testMemberCSV')) {
            return;
        }

        if (get_forum_type() != 'cns') {
            $this->assertTrue(false, 'Cannot run test when not running Conversr');
            return;
        }

        $tmp_path = cms_tempnam();

        cms_file_put_contents_safe($tmp_path, "Username,E-mail\nTestingABC,test@example.com");

        require_code('hooks/systems/tasks/import_members');
        $ob_import = new Hook_task_import_members();
        $ob_import->run('', false, $tmp_path);

        @unlink($tmp_path);

        require_code('hooks/systems/tasks/export_members');
        $ob_export = new Hook_task_export_members();
        $results = $ob_export->run(false, '.csv', '', array('ID', 'Username'), array(), 'ID');
        $this->assertTrue(strpos(cms_file_get_contents_safe($results[1][1]), 'TestingABC') !== false);

        $ob_import->run('', false, $results[1][1]);
    }
}
