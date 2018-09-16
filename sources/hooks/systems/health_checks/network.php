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
class Hook_health_check_network extends Hook_Health_Check
{
    protected $category_label = 'Network';

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
        $this->process_checks_section('testExternalAccess', 'External access', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);
        $this->process_checks_section('testPacketLoss', 'Packet loss (slow)', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);
        $this->process_checks_section('testTransferLatency', 'Transfer latency', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);
        $this->process_checks_section('testTransferSpeed', 'Transfer speed', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);

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
    public function testExternalAccess($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context != CHECK_CONTEXT__LIVE_SITE) {
            return;
        }

        if ($this->is_localhost_domain()) {
            return;
        }

        $url = 'https://compo.sr/uploads/website_specific/compo.sr/scripts/testing.php?type=http_status_check&url=' . urlencode($this->get_page_url());
        for ($i = 0; $i < 3; $i++) { // Try a few times in case of some temporary network issue or compo.sr issue
            $data = http_get_contents($url, array('trigger_error' => false));

            if ($data !== null) {
                break;
            }
            if (php_function_allowed('usleep')) {
                usleep(5000000);
            }
        }
        $result = @json_decode($data, true);
        $this->assertTrue($result === '200', 'Could not access website externally');
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
    public function testPacketLoss($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context == CHECK_CONTEXT__INSTALL) {
            return;
        }
        if ($check_context == CHECK_CONTEXT__SPECIFIC_PAGE_LINKS) {
            return;
        }

        if (php_function_allowed('shell_exec')) {
            if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
                $cmd = 'ping -n 10 8.8.8.8';
            } else {
                $cmd = 'ping -c 10 8.8.8.8';
            }
            $data = shell_exec($cmd);

            $matches = array();
            if (preg_match('# (\d(\.\d+)?%) packet loss#', $data, $matches) != 0) {
                $this->assertTrue(floatval($matches[1]) == 0.0, 'Unreliable Internet connection on server');
            } else {
                $this->stateCheckSkipped('Could not get a recognised ping response');
            }
        } else {
            $this->stateCheckSkipped('PHP shell_exec function not available');
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
    public function testTransferLatency($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context != CHECK_CONTEXT__LIVE_SITE) {
            return;
        }

        $threshold = floatval(get_option('hc_transfer_latency_threshold'));

        for ($i = 0; $i < 3; $i++) { // Try a few times in case of some temporary network issue or Google issue
            $time_before = microtime(true);

            $data = http_get_contents('http://www.google.com/', array('trigger_error' => false)); // Somewhere with very high availability

            if ($data === null) {
                $ok = false;
                if (php_function_allowed('usleep')) {
                    usleep(5000000);
                }

                continue;
            }

            $time_after = microtime(true);

            $time = ($time_after - $time_before);

            $ok = ($time < $threshold);
            if ($ok) {
                break;
            }
            if (php_function_allowed('usleep')) {
                usleep(5000000);
            }
        }

        $this->assertTrue($ok, 'Slow transfer latency @ ' . float_format($time) . ' seconds (downloading Google home page)');
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
    public function testTransferSpeed($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context != CHECK_CONTEXT__LIVE_SITE) {
            return;
        }

        $threshold_in_megabits_per_second = floatval(get_option('hc_transfer_speed_threshold'));

        $test_file_path = get_file_base() . '/data/curl-ca-bundle.crt';

        $data_to_send = str_repeat(file_get_contents($test_file_path), 5);
        $post_params = array('test_data' => $data_to_send);

        for ($i = 0; $i < 3; $i++) { // Try a few times in case of some temporary network issue or compo.sr issue
            $time_before = microtime(true);

            $data = http_get_contents('https://compo.sr/uploads/website_specific/compo.sr/scripts/testing.php?type=test_upload', array('trigger_error' => false, 'post_params' => $post_params));

            if ($data === null) {
                $ok = false;
                $megabits_per_second = 0.0;
                if (php_function_allowed('usleep')) {
                    usleep(5000000);
                }

                continue;
            }

            $time_after = microtime(true);

            $time = ($time_after - $time_before);

            $megabytes_per_second = floatval(strlen($data_to_send)) / (1024.0 * 1024.0 * $time);
            $megabits_per_second = $megabytes_per_second * 8.0;

            $ok = ($megabits_per_second > $threshold_in_megabits_per_second);
            if ($ok) {
                break;
            }
            if (php_function_allowed('usleep')) {
                usleep(5000000);
            }
        }

        $this->assertTrue($ok, 'Slow speed transfering data to a remote machine @ ' . float_format($megabits_per_second) . ' Megabits per second');
    }
}
