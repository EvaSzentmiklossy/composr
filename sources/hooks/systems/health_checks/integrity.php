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
 * @package    health_check
 */

/**
 * Hook class.
 */
class Hook_health_check_integrity extends Hook_Health_Check
{
    protected $category_label = 'Software integrity';

    /**
     * Standard hook run function to run this category of health checks.
     *
     * @param  ?array $sections_to_run Which check sections to run (null: all)
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     * @param  ?array $urls_or_page_links List of URLs and/or page-links to operate on, if applicable (null: those configured)
     * @param  ?array $comcode_segments Map of field names to Comcode segments to operate on, if applicable (null: N/A)
     * @return array A pair: category label, list of results
     */
    public function run($sections_to_run, $check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        $this->process_checks_section('testFileIntegrity', 'File integrity (slow)', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);
        $this->process_checks_section('testDatabaseIntegrity', 'Database integrity', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);
        $this->process_checks_section('testDatabaseCorruption', 'Database corruption (slow)', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);
        $this->process_checks_section('testUpgradeCompletion', 'Upgrade completion', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);
        $this->process_checks_section('testAddonUpgradeCompletion', 'Addon upgrade completion', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);
        $this->process_checks_section('testChmod', 'File permissions integrity', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);

        return array($this->category_label, $this->results);
    }

    /**
     * Run a section of health checks.
     *
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     * @param  ?array $urls_or_page_links List of URLs and/or page-links to operate on, if applicable (null: those configured)
     * @param  ?array $comcode_segments Map of field names to Comcode segments to operate on, if applicable (null: N/A)
     */
    public function testFileIntegrity($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context == CHECK_CONTEXT__INSTALL) {
            return;
        }
        if ($check_context == CHECK_CONTEXT__SPECIFIC_PAGE_LINKS) {
            return;
        }

        if (!$manual_checks) {
            $this->stateCheckSkipped('Will not check automatically due to possibility of intentional modifications');
            return;
        }

        require_code('upgrade_integrity_scan');
        $data = run_integrity_check(false, false, false);
        $this->assertTrue($data == do_lang('NO_ISSUES_FOUND'), 'Integrity checker reporting potential issues, see upgrader for details');
    }

    /**
     * Run a section of health checks.
     *
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     * @param  ?array $urls_or_page_links List of URLs and/or page-links to operate on, if applicable (null: those configured)
     * @param  ?array $comcode_segments Map of field names to Comcode segments to operate on, if applicable (null: N/A)
     */
    public function testDatabaseIntegrity($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context == CHECK_CONTEXT__INSTALL) {
            return;
        }
        if ($check_context == CHECK_CONTEXT__SPECIFIC_PAGE_LINKS) {
            return;
        }

        if (strpos(get_db_type(), 'mysql') === false) {
            $this->stateCheckSkipped('Can only check when running MySQL');
            return;
        }

        if (!$manual_checks) {
            $this->stateCheckSkipped('Will not check automatically due to possibility of intentional modifications');
            return;
        }

        require_code('database_repair');
        $repair_ob = new DatabaseRepair();
        list($phase, $sql) = $repair_ob->search_for_database_issues();
        $this->assertTrue($sql == '', 'There seem to be some inconsistencies in the database, run the "Correct MySQL schema issues (advanced)" Website Cleanup Tool');
    }

    /**
     * Run a section of health checks.
     *
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     * @param  ?array $urls_or_page_links List of URLs and/or page-links to operate on, if applicable (null: those configured)
     * @param  ?array $comcode_segments Map of field names to Comcode segments to operate on, if applicable (null: N/A)
     */
    public function testDatabaseCorruption($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context == CHECK_CONTEXT__INSTALL) {
            return;
        }
        if ($check_context == CHECK_CONTEXT__SPECIFIC_PAGE_LINKS) {
            return;
        }

        if (strpos(get_db_type(), 'mysql') !== false) {
            push_db_scope_check(false);

            $tables = $GLOBALS['SITE_DB']->query_select('db_meta', array('DISTINCT m_table'));
            foreach ($tables as $table) {
                $results = $GLOBALS['SITE_DB']->query('CHECK TABLE ' . get_table_prefix() . $table['m_table']);
                $msg_text = $results[0]['Msg_text'];
                $ok = ($msg_text == 'OK') || (strpos($msg_text, 'closed the table properly') !== false/*common false positive*/) || (strpos($msg_text, 'doesn\'t support check') !== false);

                $message = 'Corrupt table likely needing repairing: [tt]' . $table['m_table'] . '[/tt] gave status "' . $results[0]['Msg_text'] . '"';
                if (!$ok) {
                    if ($automatic_repair) {
                        $results_repair = $GLOBALS['SITE_DB']->query('REPAIR TABLE ' . get_table_prefix() . $table['m_table']);
                        $ok_repair = $results[0]['Msg_text'] == 'OK';
                        if ($ok_repair) {
                            $message = 'Corrupt table automatically repaired: [tt]' . $table['m_table'] . '[/tt] gave status "' . $results[0]['Msg_text'] . '"';
                        }
                    }
                }
                $this->assertTrue($ok, $message);
            }

            pop_db_scope_check();
        } else {
            $this->stateCheckSkipped('Can only check when running MySQL');
        }
    }

    /**
     * Run a section of health checks.
     *
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     * @param  ?array $urls_or_page_links List of URLs and/or page-links to operate on, if applicable (null: those configured)
     * @param  ?array $comcode_segments Map of field names to Comcode segments to operate on, if applicable (null: N/A)
     */
    public function testUpgradeCompletion($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context == CHECK_CONTEXT__INSTALL) {
            return;
        }
        if ($check_context == CHECK_CONTEXT__SPECIFIC_PAGE_LINKS) {
            return;
        }

        $version_files = cms_version_number();

        $_version_database = get_value('version');
        $version_database = floatval($_version_database);
        $this->assertTrue($version_files <= $version_database, 'Database seems to need an upgrade (' . float_format($version_files, 1) . ' vs ' . float_format($version_database, 1) . '), run upgrader');

        $_version_database = get_value('cns_version');
        $version_database = floatval($_version_database);
        $this->assertTrue($version_files <= $version_database, 'Database seems to need an upgrade (' . float_format($version_files, 1) . ' vs ' . float_format($version_database, 1) . '), run upgrader');
    }

    /**
     * Run a section of health checks.
     *
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     * @param  ?array $urls_or_page_links List of URLs and/or page-links to operate on, if applicable (null: those configured)
     * @param  ?array $comcode_segments Map of field names to Comcode segments to operate on, if applicable (null: N/A)
     */
    public function testAddonUpgradeCompletion($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context == CHECK_CONTEXT__INSTALL) {
            return;
        }
        if ($check_context == CHECK_CONTEXT__SPECIFIC_PAGE_LINKS) {
            return;
        }

        require_code('addons2');
        $this->assertTrue(find_updated_addons() === array(), 'Some addon(s) need updating');
    }

    /**
     * Run a section of health checks.
     *
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     * @param  ?array $urls_or_page_links List of URLs and/or page-links to operate on, if applicable (null: those configured)
     * @param  ?array $comcode_segments Map of field names to Comcode segments to operate on, if applicable (null: N/A)
     */
    public function testChmod($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context == CHECK_CONTEXT__INSTALL) {
            return;
        }
        if ($check_context == CHECK_CONTEXT__SPECIFIC_PAGE_LINKS) {
            return;
        }

        require_code('upgrade_perms');

        $this->assertTrue(upgrader_check_perms_screen() == do_lang('UPGRADER_ALL_CHMODDED_GOOD'), 'File permissions (chmodding) are not complete, see upgrader for details');
    }
}
