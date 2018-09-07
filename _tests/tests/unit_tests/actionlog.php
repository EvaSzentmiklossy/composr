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
class actionlog_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        disable_php_memory_limit();

        require_code('actionlog');
        require_code('content');
    }

    public function testNoCrashes()
    {
        $hook_obs = find_all_hook_obs('systems', 'actionlog', 'Hook_actionlog_');
        foreach ($hook_obs as $hook => $ob) {
            $handlers = $ob->get_handlers();
            foreach (array_keys($handlers) as $handler) {
                $actionlog_row = array(
                    'the_type' => $handler,
                    'param_a' => '12345',
                    'param_b' => '12345',
                );
                $ob->get_extended_actionlog_data($actionlog_row);
            }
        }
    }

    public function testPageLinks()
    {
        $this->establish_admin_session();

        $hook_obs = find_all_hook_obs('systems', 'actionlog', 'Hook_actionlog_');
        foreach ($hook_obs as $hook => $ob) {
            $handlers = $ob->get_handlers();
            foreach ($handlers as $handler => $mappings) {
                // Basic checks
                foreach ($mappings['followup_page_links'] as $page_link) {
                    if ((is_string($page_link)) && (strpos($page_link, '{') === false)) {
                        list($zone, $attributes) = page_link_decode($page_link);
                        if (array_key_exists('page', $attributes)) {
                            $found_zone = ($attributes['page'] == DEFAULT_ZONE_PAGE_NAME) ? $zone : get_page_zone($attributes['page'], false);
                            $this->assertTrue($found_zone === $zone, 'Could not find page ' . $attributes['page'] . ' in ' . $page_link); // We want everything searchable
                        }
                    }
                }

                // Real check
                $actionlog_row = array(
                    'the_type' => $handler,
                    'param_a' => strval(db_get_first_id() + 1),
                    'param_b' => strval(db_get_first_id() + 1),
                );
                $mappings_final = $ob->get_extended_actionlog_data($actionlog_row);
                if ($mappings_final !== false) {
                    foreach ($mappings_final['followup_urls'] as $url) {
                        if (is_object($url)) {
                            $url = $url->evaluate();
                        }

                        static $done_urls = array();

                        if (!array_key_exists($url, $done_urls)) {
                            $http_result = cms_http_request($url, array('byte_limit' => 0, 'trigger_error' => false, 'cookies' => array(get_session_cookie() => get_session_id())));
                            $this->assertTrue(in_array($http_result->message, array('200', '404')), 'Unexpected HTTP response, ' . $http_result->message . ', for ' . $url . ' from ' . $handler);

                            $done_urls[$url] = true;
                        }
                    }
                }
            }
        }
    }

    public function testLangStringReferences()
    {
        $hook_obs = find_all_hook_obs('systems', 'actionlog', 'Hook_actionlog_');
        foreach ($hook_obs as $hook => $ob) {
            $handlers = $ob->get_handlers();
            foreach ($handlers as $handler => $mappings) {
                $this->assertTrue(do_lang($handler, null, null, null, null, false) !== null, 'Cannot find: ' . $handler);

                foreach ($mappings['followup_page_links'] as $lang_string => $page_link) {
                    $this->assertTrue(do_lang($lang_string, null, null, null, null, false) !== null, 'Cannot find: ' . $lang_string);
                }
            }
        }
    }

    public function testCMAHookReferences()
    {
        $hook_obs = find_all_hook_obs('systems', 'actionlog', 'Hook_actionlog_');
        foreach ($hook_obs as $hook => $ob) {
            $handlers = $ob->get_handlers();
            foreach ($handlers as $handler => $mappings) {
                if ($mappings['cma_hook'] !== null) {
                    $this->assertTrue(get_content_object($mappings['cma_hook']) !== null, $mappings['cma_hook'] . ' not found');
                }
            }
        }
    }

    public function testAllActionsCovered()
    {
        // Gather data...

        $handlers = array();
        $hook_obs = find_all_hook_obs('systems', 'actionlog', 'Hook_actionlog_');
        foreach ($hook_obs as $hook => $ob) {
            $handlers += $ob->get_handlers();
        }

        require_code('files');
        require_code('files2');
        $files = get_directory_contents(get_file_base());
        $all_code = '';
        foreach ($files as $f) {
            if (substr($f, -4) == '.php') {
                $c = file_get_contents(get_file_base() . '/' . $f);
                $all_code .= $c;
            }
        }

        // Check no missing handlers...

        $matches = array();
        $num_matches = preg_match_all('#log_it\(\'([^\']*)\'#', $all_code, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $action = $matches[1][$i];
            $this->assertTrue(array_key_exists($action, $handlers), 'Could not find actionlog hook handling for ' . $action);
        }

        // Check no missing log_it calls...

        foreach (array_keys($handlers) as $handler) {
            $look_for = 'log_it(\'' . $handler . '\'';
            $this->assertTrue(strpos($all_code, $look_for), 'Could not find log_it call for ' . $handler);
        }
    }
}