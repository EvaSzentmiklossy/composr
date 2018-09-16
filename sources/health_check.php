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
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__health_check()
{
    if (!defined('CHECK_CONTEXT__INSTALL')) {
        define('CHECK_CONTEXT__INSTALL', 0);
        define('CHECK_CONTEXT__TEST_SITE', 1);
        define('CHECK_CONTEXT__LIVE_SITE', 2);
        define('CHECK_CONTEXT__PROBING_FOR_SECTIONS', 3);
        define('CHECK_CONTEXT__SPECIFIC_PAGE_LINKS', 4);

        define('HEALTH_CHECK__FAIL', 'FAIL');
        define('HEALTH_CHECK__PASS', 'PASS');
        define('HEALTH_CHECK__SKIP', 'SKIP');
        define('HEALTH_CHECK__MANUAL', 'MANUAL');
    }

    require_lang('health_check');

    global $HEALTH_CHECK_LOG_FILE;
    $HEALTH_CHECK_LOG_FILE = null;

    global $HEALTH_CHECK_PAGE_RESPONSE_CACHE, $HEALTH_CHECK_COMCODE_PAGE_CONTENT_CACHE, $HEALTH_CHECK_PAGE_URLS_CACHE;
    $HEALTH_CHECK_PAGE_RESPONSE_CACHE = array();
    $HEALTH_CHECK_COMCODE_PAGE_CONTENT_CACHE = array();
    $HEALTH_CHECK_PAGE_URLS_CACHE = array();
}

/**
 * Get a nice, formatted XHTML list to select Health Check sections.
 *
 * @param  array $default List of sections to select by default
 * @return Tempcode The list of sections
 */
function create_selection_list_health_check_sections($default)
{
    $categories = find_health_check_categories_and_sections();

    $list = new Tempcode();
    foreach ($categories as $category_label => $results) {
        foreach (array_keys($results) as $section_label) {
            $compound_label = $category_label . ' \\ ' . $section_label;

            $is_selected = in_array($compound_label, $default);

            $list->attach(form_input_list_entry($compound_label, $is_selected));
        }
    }
    return $list;
}

/**
 * Find all the Health Check categories and sections.
 *
 * @return array List of result categories
 */
function find_health_check_categories_and_sections()
{
    $check_context = CHECK_CONTEXT__PROBING_FOR_SECTIONS;

    $categories = array();

    $hook_obs = find_all_hook_obs('systems', 'health_checks', 'Hook_health_check_');
    foreach ($hook_obs as $ob) {
        list($category_label, $sections) = $ob->run(null, $check_context, true);

        ksort($sections, SORT_NATURAL | SORT_FLAG_CASE);

        $categories[$category_label] = $sections;
    }
    ksort($categories, SORT_NATURAL | SORT_FLAG_CASE);

    return $categories;
}

/**
 * Script to run a Health Check.
 *
 * @ignore
 */
function health_check_script()
{
    if (!addon_installed('health_check')) {
        warn_exit(do_lang_tempcode('MISSING_ADDON', escape_html('health_check')));
    }

    header('X-Robots-Tag: noindex');

    if (!is_cli()) {
        if (!has_actual_page_access(get_member(), 'admin_health_check', 'adminzone')) {
            require_lang('permissions');
            fatal_exit(do_lang_tempcode('ACCESS_DENIED__PAGE_ACCESS', escape_html($GLOBALS['FORUM_DRIVER']->get_username(get_member()))));
        }
    }

    $_sections_to_run = get_param_string('sections_to_run', null);
    if ($_sections_to_run === null) {
        $sections_to_run = (get_option('hc_cron_sections_to_run') == '') ? array() : explode(',', get_option('hc_cron_sections_to_run'));
    } else {
        $sections_to_run = ($_sections_to_run == '') ? array() : explode(',', $_sections_to_run);
    }
    $passes = (get_param_integer('passes', 0) == 1);
    $skips = (get_param_integer('skips', 0) == 1);
    $manual_checks = (get_param_integer('manual_checks', 0) == 1);

    $has_fails = false;
    $categories = run_health_check($has_fails, $sections_to_run, $passes, $skips, $manual_checks);

    header('Content-type: text/plain; charset=' . get_charset());
    cms_ini_set('ocproducts.xss_detect', '0');

    $out = display_health_check_results_as_text($categories);
}

/**
 * Take Health Check results and convert into a simple text output.
 *
 * @param  array $categories Results
 * @return string Output
 */
function display_health_check_results_as_text($categories)
{
    $out = '';
    foreach ($categories as $category_label => $sections) {
        foreach ($sections['SECTIONS'] as $section_label => $results) {
            foreach ($results['RESULTS'] as $result) {
                $out .= $result['RESULT'] . ': ' . strip_html($result['MESSAGE']->evaluate()) . "\n";
            }
        }
    }
    return $out;
}

/**
 * Run a Health Check.
 *
 * @param  boolean $has_fails Whether there are fails (returned by reference)
 * @param  ?array $sections_to_run Which check sections to run (null: all)
 * @param  boolean $passes Mention passed checks
 * @param  boolean $skips Mention skipped checks
 * @param  boolean $manual_checks Mention manual checks
 * @param  boolean $automatic_repair Do automatic repairs where possible
 * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
 * @param  ?array $urls_or_page_links List of URLs and/or page-links to operate on, if applicable (null: those configured)
 * @param  ?array $comcode_segments Map of field names to Comcode segments to operate on, if applicable (null: N/A)
 * @param  ?integer $check_context The current state of the website (a CHECK_CONTEXT__* constant) (null: auto-decide)
 * @return array List of result categories with results, template-ready
 */
function run_health_check(&$has_fails, $sections_to_run = null, $passes = false, $skips = false, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null, $check_context = null)
{
    if (php_function_allowed('set_time_limit')) {
        set_time_limit(180);
    }

    if ($check_context === null) {
        if (running_script('install')) {
            $check_context = CHECK_CONTEXT__INSTALL;
        } else {
            if ((get_option('hc_is_test_site') == '1') || ((get_option('hc_is_test_site') == '-1') && (get_option('site_closed') == '1'))) {
                $check_context = CHECK_CONTEXT__TEST_SITE;
            } else {
                $check_context = CHECK_CONTEXT__LIVE_SITE;
            }
        }
    }

    $_log_file = get_custom_file_base() . '/data_custom/health_check.log';
    global $HEALTH_CHECK_LOG_FILE;
    if (is_file($_log_file)) {
        $HEALTH_CHECK_LOG_FILE = fopen($_log_file, 'at');

        fwrite($HEALTH_CHECK_LOG_FILE, date('Y-m-d H:i:s') . '  (HEALTH CHECK STARTING)' . "\n");
    }

    $categories = array();

    $hook_obs = find_all_hook_obs('systems', 'health_checks', 'Hook_health_check_');
    foreach ($hook_obs as $ob) {
        list($category_label, $sections) = $ob->run($sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);

        $_sections = array();
        foreach ($sections as $section_label => $results) {
            $num_fails = 0;
            $num_passes = 0;
            $num_skipped = 0;
            $num_manual = 0;
            $_results = array();
            foreach ($results as $_result) {
                $result = array(
                    'RESULT' => $_result[0],
                    'MESSAGE' => comcode_to_tempcode($_result[1], $GLOBALS['FORUM_DRIVER']->get_guest_id()),
                );

                switch ($result['RESULT']) {
                    case HEALTH_CHECK__FAIL:
                        $has_fails = true;
                        $_results[] = $result;
                        $num_fails++;
                        break;

                    case HEALTH_CHECK__PASS:
                        if ($passes) {
                            $_results[] = $result;
                            $num_passes++;
                        }
                        break;

                    case HEALTH_CHECK__SKIP:
                        if ($skips) {
                            $_results[] = $result;
                            $num_skipped++;
                        }
                        break;

                    case HEALTH_CHECK__MANUAL:
                        if ($manual_checks) {
                            $_results[] = $result;
                            $num_manual++;
                        }
                        break;

                    default:
                        $_results[] = $result;
                        break;
                }
            };
            if (count($_results) > 0) {
                $_sections[$section_label] = array(
                    'RESULTS' => $_results,

                    'NUM_FAILS' => integer_format($num_fails),
                    'NUM_PASSES' => integer_format($num_passes),
                    'NUM_SKIPPED' => integer_format($num_skipped),
                    'NUM_MANUAL' => integer_format($num_manual),

                    '_NUM_FAILS' => strval($num_fails),
                    '_NUM_PASSES' => strval($num_passes),
                    '_NUM_SKIPPED' => strval($num_skipped),
                    '_NUM_MANUAL' => strval($num_manual),
                );
            }
        }

        if (count($_sections) > 0) {
            ksort($_sections, SORT_NATURAL | SORT_FLAG_CASE);
            $categories[$category_label] = array(
                'SECTIONS' => $_sections,
            );
        }
    }
    ksort($categories, SORT_NATURAL | SORT_FLAG_CASE);

    if ($HEALTH_CHECK_LOG_FILE !== null) {
        fwrite($HEALTH_CHECK_LOG_FILE, date('Y-m-d H:i:s') . '  (HEALTH CHECK ENDING)' . "\n");

        fclose($HEALTH_CHECK_LOG_FILE);
    }

    return $categories;
}

/**
 * Base object for Health Check hooks.
 *
 * @package    health_check
 */
abstract class Hook_Health_Check
{
    protected $category_label = 'Unknown category';
    private $current_section_label = 'Unknown section';
    protected $results = array();

    /*
    HEALTH CHECK BASIC API
    */

    /**
     * Process a checks section.
     *
     * @param  string $method The method containing the checks
     * @param  string $section_label The section label
     * @param  ?array $sections_to_run Which check sections to run (null: all)
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     * @param  ?array $urls_or_page_links List of URLs and/or page-links to operate on, if applicable (null: those configured)
     * @param  ?array $comcode_segments Map of field names to Comcode segments to operate on, if applicable (null: N/A)
     */
    protected function process_checks_section($method, $section_label, $sections_to_run, $check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if (($sections_to_run !== null) && (!in_array($this->category_label . ' \\ ' . $section_label, $sections_to_run)) && (!in_array($method, $sections_to_run))) {
            return;
        }

        $this->current_section_label = $section_label;

        if ($check_context != CHECK_CONTEXT__PROBING_FOR_SECTIONS) {
            global $HEALTH_CHECK_LOG_FILE;
            if ($HEALTH_CHECK_LOG_FILE !== null) {
                fwrite($HEALTH_CHECK_LOG_FILE, date('Y-m-d H:i:s') . '  STARTING ' . $this->category_label . ' \\ ' . $section_label . "\n");
            }
            call_user_func(array($this, $method), $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);
            if ($HEALTH_CHECK_LOG_FILE !== null) {
                fwrite($HEALTH_CHECK_LOG_FILE, date('Y-m-d H:i:s') . '  FINISHED ' . $this->category_label . ' \\ ' . $section_label . "\n");
            }
        } else {
            if (strpos($section_label, ',') !== false) {
                fatal_exit(do_lang_tempcode('INTERNAL_ERROR')); // We cannot have commas in section labels because we store label sets in comma-separated lists
            }
            $this->results[$section_label] = null;
        }
    }

    /*
    CHECK REPORTING
    */

    /**
     * Report a check result, with the message if it failed.
     *
     * @param  boolean $result Whether the check passed
     * @param  string $message Failure message
     */
    protected function assertTrue($result, $message)
    {
        if (!isset($this->results[$this->current_section_label])) {
            $this->results[$this->current_section_label] = array();
        }
        if ($result) {
            $this->results[$this->current_section_label][] = array(HEALTH_CHECK__PASS, $message);
        } else {
            $this->results[$this->current_section_label][] = array(HEALTH_CHECK__FAIL, $message);
        }
    }

    /**
     * State a manual check.
     *
     * @param  string $message What to check
     */
    protected function stateCheckManual($message)
    {
        if (!isset($this->results[$this->current_section_label])) {
            $this->results[$this->current_section_label] = array();
        }
        $this->results[$this->current_section_label][] = array(HEALTH_CHECK__MANUAL, $message);
    }

    /**
     * State that a check was skipped.
     * This is only called when we would like to run a check but something is stopping us; we do not call it for checks that don't make any sense to run for any reason.
     *
     * @param  string $message The reason for the skip, with possible details of exactly what was skipped
     */
    protected function stateCheckSkipped($message)
    {
        if (!isset($this->results[$this->current_section_label])) {
            $this->results[$this->current_section_label] = array();
        }
        $this->results[$this->current_section_label][] = array(HEALTH_CHECK__SKIP, $message);
    }

    /*
    SITE URL/DOMAIN QUERYING
    */

    /**
     * Get the URL for a page-link.
     *
     * @param  string $page_link The page-link
     * @return string The URL
     */
    protected function get_page_url($page_link = ':')
    {
        global $HEALTH_CHECK_PAGE_URLS_CACHE;
        if (!array_key_exists($page_link, $HEALTH_CHECK_PAGE_URLS_CACHE)) {
            $url = page_link_to_url($page_link);
            if (strpos($url, '?') === false) {
                $url .= '?keep_su=Guest';
            } else {
                $url .= '&keep_su=Guest';
            }
            $HEALTH_CHECK_PAGE_URLS_CACHE[$page_link] = $url;
        }
        return $HEALTH_CHECK_PAGE_URLS_CACHE[$page_link];
    }

    /**
     * Convert any URLs to page-links in the given array.
     *
     * @param  ?array $_urls_or_page_links List of URLs and/or page-links (null: those configured)
     * @return array List of page-links
     */
    protected function process_urls_into_page_links($_urls_or_page_links = null)
    {
        if ($_urls_or_page_links === null) {
            $__urls_or_page_links = get_option('hc_scan_page_links');
            if ($__urls_or_page_links == '') {
                $_urls_or_page_links = array();
            } else {
                $_urls_or_page_links = explode("\n", $__urls_or_page_links);
            }
        }

        require_code('zones3');

        $page_links = array();
        foreach ($_urls_or_page_links as $url_or_page_link) {
            if (looks_like_url($url_or_page_link)) {
                $page_links[] = url_to_page_link($url_or_page_link);
            } else {
                $page_links = array_merge($page_links, expand_wildcarded_page_links($url_or_page_link));
            }
        }

        return $page_links;
    }

    /**
     * Get the website domain names.
     *
     * @param  boolean $remap_www Whether to strip www from domain names
     * @return array Domain names
     */
    protected function get_domains($remap_www = true)
    {
        $domains = array();

        $host = parse_url(get_base_url(), PHP_URL_HOST);
        if (preg_match('#[A-Z]#i', $host) != 0) {
            $domains[''] = $host;
        }

        global $SITE_INFO;
        $zl = strlen('ZONE_MAPPING_');
        foreach ($SITE_INFO as $key => $_val) {
            if ($key !== '' && $key[0] === 'Z' && substr($key, 0, $zl) === 'ZONE_MAPPING_') {
                $domains[substr($key, strlen('ZONE_MAPPING_'))] = $_val[0];
            }
        }

        if ($remap_www) {
            foreach ($domains as &$domain) {
                $domain = preg_replace('#^www\d*\.#', '', $domain);
            }
        }

        return array_unique($domains);
    }

    /**
     * Find whether a domain is local.
     *
     * @param  ?string $domain The domain (null: website domain)
     * @return boolean Whether it is local
     */
    protected function is_localhost_domain($domain = null)
    {
        if ($domain === null) {
            $domain = parse_url(get_base_url(), PHP_URL_HOST);
        }

        return ($domain == 'localhost') || (trim($domain, '0123456789.') == '') || (strpos($domain, ':') !== false);
    }

    /**
     * Get a list of e-mail domains the site uses.
     *
     * @param  boolean $include_all Include all e-mail domains, as opposed to just the main outgoing one
     * @return array Map of e-mail domains to e-mail addresses on the  domain
     */
    protected function get_mail_domains($include_all = true)
    {
        require_code('mail');

        $domains = array();
        $addresses = find_system_email_addresses($include_all);
        foreach ($addresses as $address => $domain) {
            if (!$this->is_localhost_domain($domain)) {
                $domains[$domain] = $address;
            }
        }

        return $domains;
    }

    /*
    PAGE DOWNLOADING
    */

    /**
     * Download a page by page-link.
     *
     * @param  string $page_link Page-link
     * @param  boolean $inner_screen_only Whether to try and restrict to just an inner Comcode screen
     * @return string Page content
     */
    protected function get_page_content($page_link = ':', $inner_screen_only = false)
    {
        if ($inner_screen_only) {
            $test = $this->get_comcode_page_content($page_link);
            if ($test !== null) {
                return $test[1];
            }
        }

        $http_result = $this->get_page_http_content($page_link);
        return $http_result->data;
    }

    /**
     * Download a page by page-link.
     *
     * @param  string $page_link Page-link
     * @return object Response data
     */
    protected function get_page_http_content($page_link = ':')
    {
        global $HEALTH_CHECK_PAGE_RESPONSE_CACHE;
        if (!array_key_exists($page_link, $HEALTH_CHECK_PAGE_RESPONSE_CACHE)) {
            $HEALTH_CHECK_PAGE_RESPONSE_CACHE[$page_link] = cms_http_request($this->get_page_url($page_link), array('timeout' => 20.0, 'trigger_error' => false, 'no_redirect' => true));

            // Server blocked to access itself
            if ($page_link == ':') {
                $this->assertTrue($HEALTH_CHECK_PAGE_RESPONSE_CACHE[$page_link] !== null, 'The server cannot download itself');
            }
        }
        return $HEALTH_CHECK_PAGE_RESPONSE_CACHE[$page_link];
    }

    /**
     * Get a Comcode page-link's Comcode and HTML.
     *
     * @param  string $page_link Page-link
     * @return ?array A tuple: Comcode, HTML, Zone name, Page name (null: not a Comcode page or not a page at all)
     */
    protected function get_comcode_page_content($page_link)
    {
        global $HEALTH_CHECK_COMCODE_PAGE_CONTENT_CACHE;
        if (!array_key_exists($page_link, $HEALTH_CHECK_COMCODE_PAGE_CONTENT_CACHE)) {
            require_code('site');

            list($zone, $attributes) = page_link_decode($page_link);
            $page = $attributes['page'];
            $path_details = find_comcode_page(user_lang(), $page, $zone);
            if ($path_details[2] != '') {
                $comcode = cms_file_get_contents_safe($path_details[2]);
                $html = load_comcode_page($path_details[1], $zone, $page, $path_details[0], true);
                $ret = array($comcode, $html->evaluate(), $zone, $page);
                $HEALTH_CHECK_COMCODE_PAGE_CONTENT_CACHE[$page_link] = $ret;
                return $ret;
            }
            $HEALTH_CHECK_COMCODE_PAGE_CONTENT_CACHE[$page_link] = null;
            return null;
        }
        return $HEALTH_CHECK_COMCODE_PAGE_CONTENT_CACHE[$page_link];
    }

    /*
    PAGE SCANNING
    */

    /**
     * Get all the embedded URLs in some HTML.
     *
     * @param  string $data HTML
     * @return array List of URLs
     */
    protected function get_embed_urls_from_data($data)
    {
        $urls = array();

        require_code('xhtml');
        $data = xhtmlise_html($data, true);

        $matches = array();

        $num_matches = preg_match_all('#<link\s[^<>]*href="([^"]*)"[^<>]*rel="stylesheet"#is', $data, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $urls[] = $matches[1][$i];
        }
        $num_matches = preg_match_all('#<link\s[^<>]*rel="stylesheet"[^<>]*href="([^"]*)"#is', $data, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $urls[] = $matches[1][$i];
        }
        $num_matches = preg_match_all('#<script\s[^<>]*src="([^"]*)"#is', $data, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $urls[] = $matches[1][$i];
        }
        $num_matches = preg_match_all('#<(img|audio|video|source|track|input|iframe|embed)\s[^<>]*src="([^"]*)"#is', $data, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $urls[] = $matches[2][$i];
        }
        $num_matches = preg_match_all('#<(area)\s[^<>]*href="([^"]*)"#is', $data, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $urls[] = $matches[2][$i];
        }
        $num_matches = preg_match_all('#<object\s[^<>]*data="([^"]*)"#is', $data, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $urls[] = $matches[1][$i];
        }

        $urls = array_unique($urls);

        return $urls;
    }

    /**
     * Get all the hyperlinked URLs in some HTML.
     *
     * @param  string $data HTML
     * @return array List of URLs
     */
    protected function get_link_urls_from_data($data)
    {
        $urls = array();

        require_code('xhtml');
        $data = xhtmlise_html($data, true);

        $matches = array();

        $num_matches = preg_match_all('#<(a)\s[^<>]*href="([^"]*)"#is', $data, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $urls[] = $matches[2][$i];
        }

        $urls = array_unique($urls);

        return $urls;
    }

    /*
    COMPO.SR API
    */

    /**
     * Call a compo.sr API function.
     *
     * @param  string $type API type
     * @param  array $params Map of parameters
     * @return mixed API result
     */
    protected function call_composr_homesite_api($type, $params)
    {
        $url = 'https://compo.sr/uploads/website_specific/compo.sr/scripts/api.php?type=' . urlencode($type);
        foreach ($params as $key => $_val) {
            switch (gettype($_val)) {
                case 'boolean':
                    $val = $_val ? '1' : '0';
                    break;

                case 'integer':
                    $val = strval($_val);
                    break;

                case 'double':
                    $val = float_to_raw_string($_val);
                    break;

                case 'array':
                    $val = @implode(',', array_map('strval', $_val));
                    break;

                case 'NULL':
                    $val = '';
                    break;

                case 'string':
                default:
                    $val = $_val;
                    break;
            }

            $url .= '&' . $key . '=' . urlencode($val);
        }
        return @json_decode(http_get_contents($url, array('trigger_error' => false)), true);
    }
}
