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
class Hook_health_check_mistakes_user_ux extends Hook_Health_Check
{
    protected $category_label = 'User-experience for mistakes';

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
        $this->process_checks_section('test404Pages', '404 pages', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);
        $this->process_checks_section('testWWWRedirection', 'www redirection', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);
        $this->process_checks_section('testHTTPSRedirection', 'HTTPS redirection', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);

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
    public function test404Pages($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context == CHECK_CONTEXT__INSTALL) {
            return;
        }
        if ($check_context == CHECK_CONTEXT__SPECIFIC_PAGE_LINKS) {
            return;
        }

        $url = get_base_url() . '/testing-for-404.html';
        $data = http_get_contents($url, array('trigger_error' => false, 'ignore_http_status' => true));
        $this->assertTrue(($data === null) || (strpos($data, '<link') !== false) || (strpos($data, '<a ') !== false), '[tt]404[/tt] status page is too basic looking, probably not helpful, suggest to display a sitemap');
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
    public function testWWWRedirection($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context != CHECK_CONTEXT__LIVE_SITE) {
            return;
        }

        $domains = $this->get_domains(false);

        foreach ($domains as $zone => $domain) {
            if ($domain == 'localhost') {
                continue;
            }

            $parts = explode('.', $domain);

            if (substr($parts[0], 0, 3) == 'www') {
                array_shift($parts);
                $wrong_domain = implode('.', $parts);
            } else {
                $wrong_domain = 'www.' . $domain;
            }

            $lookup = cms_gethostbyname($wrong_domain);
            $ok = ($lookup != $wrong_domain);
            $this->assertTrue($ok, 'Could not lookup [tt]' . $wrong_domain . '[/tt], should exist for it to redirect from [tt]' . $domain . '[/tt]');
            if (!$ok) {
                return;
            }

            //$url = preg_replace('#(://.*)/.*$#U', '$1/data/empty.php', $this->get_page_url(':'));
            if ($zone == '') {
                $url = $this->get_page_url($zone . ':privacy');
            } else {
                $url = $this->get_page_url($zone . ':');
            }
            $wrong_url = str_replace('://' . $domain, '://' . $wrong_domain, $url);

            $http_result = cms_http_request($wrong_url, array('trigger_error' => false));
            $redirected = ($http_result->download_url != $wrong_url);
            $this->assertTrue($redirected, 'Domain [tt]' . $wrong_domain . '[/tt] is not redirecting to [tt]' . $domain . '[/tt]');

            if ($redirected) {
                $ok = ($http_result->download_url == $url);
                $this->assertTrue($ok, 'Domain [tt]' . $wrong_domain . '[/tt] is not redirecting to deep URLs of [tt]' . $domain . '[/tt]');

                $http_result = cms_http_request($wrong_url, array('trigger_error' => false));
                $ok = ($http_result->message == '301');
                $this->assertTrue($ok, 'Domain [tt]' . $wrong_domain . '[/tt] is not redirecting to [tt]' . $domain . '[/tt] with a [tt]301[/tt] code ([tt]' . $http_result->message . '[/tt] code used)');
            }
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
    public function testHTTPSRedirection($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context != CHECK_CONTEXT__LIVE_SITE) {
            return;
        }

        global $SITE_INFO;

        if (empty($SITE_INFO['base_url'])) {
            return;
        }

        if (strpos(get_option('ip_forwarding'), '://') !== false) {
            return; // Will mess up protocol
        }

        $protocol = parse_url($SITE_INFO['base_url'], PHP_URL_SCHEME);

        if ($protocol == 'http') {
            return;
        }

        $wrong_protocol = 'http';

        $url = $this->get_page_url(':privacy');
        $wrong_url = str_replace($protocol . '://', $wrong_protocol . '://', $url);

        $http_result = cms_http_request($wrong_url, array('trigger_error' => false));
        $redirected = ($http_result->download_url != $wrong_url);
        $this->assertTrue($redirected, 'Protocol [tt]' . $wrong_protocol . '[/tt] is not redirecting to [tt]' . $protocol . '[/tt] protocol');

        if ($redirected) {
            $ok = ($http_result->download_url == $url);
            $this->assertTrue($ok, 'Protocol [tt]' . $wrong_protocol . '[/tt] is not redirecting to deep URLs of [tt]' . $protocol . '[/tt] protocol');

            http_get_contents($wrong_url, array('trigger_error' => false, 'no_redirect' => true));
            $ok = ($http_result->message == '301');
            $this->assertTrue($ok, 'Protocol [tt]' . $wrong_protocol . '[/tt] is not redirecting to [tt]' . $protocol . '[/tt] protocol with a [tt]301[/tt] code ([tt]' . $http_result->message . '[/tt] code used)');
        }
    }
}
