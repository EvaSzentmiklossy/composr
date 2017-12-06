<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2017

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core_cleanup_tools
 */

/**
 * Hook class.
 *
 * @package    core_cleanup_tools
 */
class BrokenURLScanner
{
    /**
     * Scan URL fields for URLs.
     *
     * @param  array $live_base_urls The live base URL(s)
     * @param  integer $maximum_api_results Maximum results to query from APIs
     * @return array List of URLs (each list entry is a map of URL details)
     */
    public function enumerate_url_fields($live_base_urls, $maximum_api_results)
    {
        $urls = array();

        push_db_scope_check(false);

        $skip_hooks = find_all_hooks('systems', 'non_active_urls');
        $sql = 'SELECT m_name,m_table FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'db_meta WHERE m_type LIKE \'' . db_encode_like('%URLPATH%') . '\'';
        $urlpaths = $GLOBALS['SITE_DB']->query($sql);
        foreach ($urlpaths as $field) {
            if (array_key_exists($field['m_table'], $skip_hooks)) {
                continue;
            }
            if (in_array($field['m_table'], array('hackattack', 'url_title_cache', 'theme_images', 'incoming_uploads'))) {
                continue;
            }

            $sql = 'SELECT m_name FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'db_meta WHERE m_type LIKE \'*%\' AND ' . db_string_equal_to('m_table', $field['m_table']);
            $key_fields = $GLOBALS['SITE_DB']->query($sql);

            $ofs = $GLOBALS['SITE_DB']->query_select($field['m_table'], array('*'));
            foreach ($ofs as $of) {
                $url = $of[$field['m_name']];

                if ($url == '') {
                    continue;
                }

                $table_name = $field['m_table'];
                $field_name = $field['m_name'];

                $id = '';
                foreach ($key_fields as $i => $key_field) {
                    if ($i != 0) {
                        $id .= ':';
                    }
                    $id .= @strval($of[$key_field['m_name']]);
                }

                $edit_url = $this->find_table_content_edit_url($table_name, $id, $key_fields);

                $urls[] = array(
                    'url' => $url,
                    'table_name' => $table_name,
                    'field_name' => $field_name,
                    'identifier' => array_key_exists('id', $of) ? strval($of['id']) : (array_key_exists('name', $of) ? $of['name'] : do_lang('UNKNOWN')),
                    'edit_url' => $edit_url,
                );
            }
        }

        pop_db_scope_check();

        return $urls;
    }

    /**
     * Scan Comcode fields for URLs.
     *
     * @param  array $live_base_urls The live base URL(s)
     * @param  integer $maximum_api_results Maximum results to query from APIs
     * @return array List of URLs (each list entry is a map of URL details)
     */
    public function enumerate_comcode_fields($live_base_urls, $maximum_api_results)
    {
        $urls = array();

        /*
        For testing...
        $url = get_base_url() . '/foo.html';
        $urls[] = array(
            'url' => $url,
            'table_name' => null,
            'field_name' => null,
            'identifier' => null,
            'edit_url' => null,
        );
        return $urls;
        */

        push_db_scope_check(false);

        global $COMCODE_URLS;

        push_lax_comcode(true);

        $sql = 'SELECT m_table,m_name FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'db_meta WHERE m_type LIKE \'' . db_encode_like('%LONG\_TRANS\_\_COMCODE%') . '\'';
        $possible_comcode_fields = $GLOBALS['SITE_DB']->query($sql);
        foreach ($possible_comcode_fields as $field) {
            if (in_array($field['m_table'], array('seo_meta', 'cached_comcode_pages'))) {
                continue;
            }

            $sql = 'SELECT m_name FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'db_meta WHERE m_type LIKE \'*%\' AND ' . db_string_equal_to('m_table', $field['m_table']);
            $key_fields = $GLOBALS['SITE_DB']->query($sql);

            $ofs = $GLOBALS['SITE_DB']->query_select($field['m_table'], array('*'));
            foreach ($ofs as $of) {
                if (strpos($of[$field['m_name']], '/') === false) {
                    continue; // Doesn't appear to contain any URLs
                }

                $COMCODE_URLS = array();

                get_translated_tempcode($field['m_table'], $of, $field['m_name']);

                if ($COMCODE_URLS !== null) {
                    foreach (array_keys($COMCODE_URLS) as $url) {
                        if ($url == '') {
                            continue;
                        }

                        $table_name = $field['m_table'];
                        $field_name = $field['m_name'];
                        if (multi_lang_content()) {
                            $table_name .= ' / translate';
                            $field_name .= ' / text_original';
                        }

                        $id = '';
                        foreach ($key_fields as $i => $key_field) {
                            if ($i != 0) {
                                $id .= ':';
                            }
                            $id .= @strval($of[$key_field['m_name']]);
                        }

                        $edit_url = $this->find_table_content_edit_url($table_name, $id, $key_fields);

                        if (multi_lang_content()) {
                            $id .= '/' . strval($of[$field['m_name']]);
                        }

                        $urls[] = array(
                            'url' => $url,
                            'table_name' => $table_name,
                            'field_name' => $field_name,
                            'identifier' => $id,
                            'edit_url' => $edit_url,
                        );
                    }
                }
            }
        }

        pop_lax_comcode();

        pop_db_scope_check();

        return $urls;
    }

    /**
     * Find the URL to some content.
     *
     * @param  string $table_name Table
     * @param  string $id ID
     * @param  array $key_fields Key fields
     * @return ?Tempcode Edit URL (null: none)
     */
    protected function find_table_content_edit_url($table_name, $id, $key_fields)
    {
        $edit_url = null;
        if (count($key_fields) == 1) {
            require_code('content');
            $content_type = convert_composr_type_codes('table', $table_name, 'content_type');
            if ($content_type != '') {
                $cma_ob = get_content_object($content_type);
                if (is_object($cma_ob)) {
                    $cma_info = $cma_ob->info();
                    if (!empty($cma_info['edit_page_link_pattern'])) {
                        $edit_page_link = str_replace('_WILD', $id, $cma_info['edit_page_link_pattern']);
                        list($zone, $attributes,) = page_link_decode($edit_page_link);
                        $edit_url = build_url($attributes, $zone);
                    }
                }
            }
        }
        return $edit_url;
    }

    /**
     * Scan catalogues for URLs.
     *
     * @param  array $live_base_urls The live base URL(s)
     * @param  integer $maximum_api_results Maximum results to query from APIs
     * @return array List of URLs (each list entry is a map of URL details)
     */
    public function enumerate_catalogue_fields($live_base_urls, $maximum_api_results)
    {
        $urls = array();

        if (addon_installed('catalogues')) {
            $catalogue_fields = $GLOBALS['SITE_DB']->query_select('catalogue_fields', array('id'), array('cf_type' => 'url'));
            $or_list = '';
            foreach ($catalogue_fields as $field) {
                if ($or_list != '') {
                    $or_list .= ' OR ';
                }
                $or_list .= 'cf_id=' . strval($field['id']);
            }
            if ($or_list != '') {
                $sql = 'SELECT id,cv_value,ce_id FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'catalogue_efv_short WHERE ' . $or_list;
                $values = $GLOBALS['SITE_DB']->query($sql, null, 0, false, true);
                foreach ($values as $value) {
                    $url = $value['cv_value'];

                    if ($url == '') {
                        continue;
                    }

                    $urls[] = array(
                        'url' => $url,
                        'table_name' => 'catalogue_efv_short',
                        'field_name' => 'cv_value',
                        'identifier' => strval($value['id']),
                        'edit_url' => build_url(array('page' => 'cms_catalogues', 'type' => '_edit_entry', 'id' => $value['ce_id']), get_module_zone('cms_catalogues')),
                    );
                }
            }
        }

        return $urls;
    }

    /**
     * Scan Comcode pages for URLs.
     *
     * @param  array $live_base_urls The live base URL(s)
     * @param  integer $maximum_api_results Maximum results to query from APIs
     * @return array List of URLs (each list entry is a map of URL details)
     */
    public function enumerate_comcode_pages($live_base_urls, $maximum_api_results)
    {
        $urls = array();

        global $COMCODE_URLS;

        push_lax_comcode(true);

        $zones = find_all_zones();
        foreach ($zones as $zone) {
            $pages = array();
            $pages += find_all_pages($zone, 'comcode_custom/' . get_site_default_lang(), 'txt', false, null, FIND_ALL_PAGES__ALL);
            $pages += find_all_pages($zone, 'comcode/' . get_site_default_lang(), 'txt', false, null, FIND_ALL_PAGES__ALL);
            foreach ($pages as $page => $type) {
                $COMCODE_URLS = array();

                $file_path = zone_black_magic_filterer(((strpos($type, '_custom') !== false) ? get_custom_file_base() : get_file_base()) . '/' . $zone . '/pages/' . $type . '/' . $page . '.txt');
                $comcode = file_get_contents($file_path);

                if (strpos($comcode, '/') === false) {
                    continue; // Doesn't appear to contain any URLs
                }

                $eval = @static_evaluate_tempcode(comcode_to_tempcode($comcode, null, true));

                $matches = array();
                $num_matches = preg_match_all('#\shref="([^"]+)"#', $eval, $matches);
                for ($i = 0; $i < $num_matches; $i++) {
                    $url = html_entity_decode($matches[1][$i], ENT_QUOTES);
                    $COMCODE_URLS[$url] = true;
                }

                if ($COMCODE_URLS !== null) {
                    foreach (array_keys($COMCODE_URLS) as $i => $url) {
                        if ($url == '') {
                            continue;
                        }

                        $urls[] = array(
                            'url' => $url,
                            'table_name' => null,
                            'field_name' => null,
                            'identifier' => $zone . ':' . $page,
                            'edit_url' => build_url(array('page' => 'cms_comcode_pages', 'type' => '_edit', 'page_link' => $zone . ':' . $page), get_module_zone('cms_comcode_pages')),
                        );
                    }
                }
            }
        }

        pop_lax_comcode();

        return $urls;
    }

    /**
     * Enumerate the backlinks Moz has found.
     *
     * @param  array $live_base_urls The live base URL(s)
     * @param  integer $maximum_api_results Maximum results to query from APIs
     * @return array List of URLs (each list entry is a map of URL details)
     */
    public function enumerate_moz_backlinks($live_base_urls, $maximum_api_results)
    {
        $domains = array();
        foreach ($live_base_urls as $live_base_url) {
            $domain = @parse_url($live_base_url, PHP_URL_HOST);
            if (!empty($domain)) {
                $domains[] = $domain;
            }
        }
        $domains = array_unique($domains);

        $urls = array();

        foreach ($domains as $domain) {
            $expires = time() + 300;
            $string_to_sign = get_option('moz_access_id') . "\n" . strval($expires);
            $binary_signature = $this->hmac_sha1(get_option('moz_secret_key'), $string_to_sign);
            $url_safe_signature = base64_encode($binary_signature);

            do {
                $continuing = false;
                $offset = 0;

                $api_url = 'http://lsapi.seomoz.com/linkscape/links/' . $domain;
                $api_url .= '?Scope=page_to_domain';
                $api_url .= '&Sort=page_authority';
                $api_url .= '&Filter=external';
                $api_url .= '&Offset=' . strval($offset);
                $api_url .= '&Limit=50';
                $api_url .= '&SourceCols=536870916';
                $api_url .= '&TargetCols=4';
                $api_url .= '&AccessID=' . get_option('moz_access_id');
                $api_url .= '&Expires=' . strval($expires);
                $api_url .= '&Signature=' . urlencode($url_safe_signature);

                $_result = http_get_contents($api_url, array('trigger_error' => false));
                if ($_result !== null) {
                    $result = json_decode($_result, true);
                    foreach ($result as $_url) {
                        $urls[] = array(
                            'url' => 'http://' . $_url['luuu'],
                            'table_name' => null,
                            'field_name' => null,
                            'identifier' => parse_url('http://' . $_url['uu'], PHP_URL_HOST),
                            'edit_url' => 'http://' . $_url['uu'],
                        );
                    }

                    if ((count($result) == 50) && ($maximum_api_results > count($urls))) {
                        $continuing = true;
                        $offset += 50;
                    }
                }

                if ($continuing) {
                    if (get_option('moz_paid') == '0') {
                        sleep(10);
                    }
                }
            }
            while ($continuing);
        }

        return $urls;
    }

    /**
     * Do a HMAC-SHA1 hash.
     *
     * @param  string $key Key
     * @param  string $data Data to hash
     * @return string Hash
     */
    protected function hmac_sha1($key, $data)
    {
        $pack = 'H' . strval(strlen(sha1('test')));
        $size = 64;
        $opad = str_repeat(chr(0x5C), $size);
        $ipad = str_repeat(chr(0x36), $size);

        if (strlen($key) > $size) {
            $key = str_pad(pack($pack, sha1($key)), $size, chr(0x00));
        } else {
            $key = str_pad($key, $size, chr(0x00));
        }

        for ($i = 0; $i < strlen($key) - 1; $i++) {
            $opad[$i] = $opad[$i] ^ $key[$i];
            $ipad[$i] = $ipad[$i] ^ $key[$i];
        }

        $output = sha1($opad . pack($pack, sha1($ipad . $data)));
        return pack($pack, $output);
    }

    /**
     * Enumerate the backlinks Google has found and considered broken at last check.
     *
     * @param  array $live_base_urls The live base URL(s)
     * @param  integer $maximum_api_results Maximum results to query from APIs
     * @return array List of URLs (each list entry is a map of URL details)
     */
    public function enumerate_google_broken_backlinks__auth_permissions($live_base_urls, $maximum_api_results)
    {
        return $this->_enumerate_google_broken_backlinks($live_base_urls, 'authPermissions');
    }

    /**
     * Enumerate the backlinks Google has found and considered broken at last check.
     *
     * @param  array $live_base_urls The live base URL(s)
     * @param  integer $maximum_api_results Maximum results to query from APIs
     * @return array List of URLs (each list entry is a map of URL details)
     */
    public function enumerate_google_broken_backlinks__not_found($live_base_urls, $maximum_api_results)
    {
        return $this->_enumerate_google_broken_backlinks($live_base_urls, 'notFound');
    }

    /**
     * Enumerate the backlinks Google has found and considered broken at last check.
     *
     * @param  array $live_base_urls The live base URL(s)
     * @param  integer $maximum_api_results Maximum results to query from APIs
     * @return array List of URLs (each list entry is a map of URL details)
     */
    public function enumerate_google_broken_backlinks__server_error($live_base_urls, $maximum_api_results)
    {
        return $this->_enumerate_google_broken_backlinks($live_base_urls, 'serverError');
    }

    /**
     * Enumerate the backlinks Google has found and considered broken at last check.
     *
     * @param  array $live_base_urls The live base URL(s)
     * @param  integer $maximum_api_results Maximum results to query from APIs
     * @return array List of URLs (each list entry is a map of URL details)
     */
    public function enumerate_google_broken_backlinks__soft404($live_base_urls, $maximum_api_results)
    {
        return $this->_enumerate_google_broken_backlinks($live_base_urls, 'soft404');
    }

    /**
     * Enumerate the backlinks Google has found and considered broken at last check.
     *
     * @param  array $live_base_urls The live base URL(s)
     * @param  string $category The Google Search Console category
     * @return array List of URLs (each list entry is a map of URL details)
     */
    protected function _enumerate_google_broken_backlinks($live_base_urls, $category)
    {
        $urls = array();

        foreach ($live_base_urls as $live_base_url) {
            $api_url = 'https://www.googleapis.com/webmasters/v3/sites/' . urlencode($live_base_url) . '/urlCrawlErrorsSamples?category=' . urlencode($category) . '&platform=web';
            $api_url .= '&access_token=' . urlencode(refresh_oauth2_token('google_search_console'));

            $_result = http_get_contents($api_url, array('trigger_error' => false));
            if ($_result !== null) {
                $result = json_decode($_result, true);
                if (!isset($result['urlCrawlErrorSample'])) {
                    continue; // Nothing for this live base URL?
                }
                foreach ($result['urlCrawlErrorSample'] as $_url) {
                    if (preg_match('#^(data/|calendar/browse/)#', $_url['pageUrl']) != 0) {
                        // Common thing Google should not be looking at
                        continue;
                    }
                    if (!isset($_url['urlDetails']['linkedFromUrls'][0])) {
                        // No longer even linked, historic
                        continue;
                    }

                    $urls[] = array(
                        'url' => $live_base_url . ((substr($live_base_url, -1) == '/') ? '' : '/') . $_url['pageUrl'],
                        'table_name' => null,
                        'field_name' => null,
                        'identifier' => parse_url($_url['urlDetails']['linkedFromUrls'][0], PHP_URL_HOST),
                        'edit_url' => $_url['urlDetails']['linkedFromUrls'][0],
                    );
                }
            }
        }

        return $urls;
    }

    /**
     * Check to see if a URL is there.
     *
     * @param  URLPATH $url URL to check
     * @return boolean Whether the URL is there (i.e. false = broken)
     */
    public function check_url($url)
    {
        // Check if it's a local file URL
        if (((substr($url, 0, 8) == 'uploads/') || (substr($url, 0, 7) == 'themes/')) && (strpos($url, '?') === false)) {
            return file_exists(rawurldecode($url));
        }

        // Normal URL...

        $url = qualify_url($url, get_base_url());

        $test = cms_http_request($url, array('byte_limit' => 0, 'trigger_error' => false));
        if (($test === null) && ($test->message == '403')) {
            $test = cms_http_request($url, array('byte_limit' => 1, 'trigger_error' => false)); // Try without HEAD, sometimes it's not liked
        }

        if (($test === null) || (in_array($test->message, array('404', 'could not connect to host')))) {
            return false;
        }

        // If a URL is only redirecting back to the home page, we can consider that broken too... 

        static $undesirable_redirects = null;
        if ($undesirable_redirects === null) {
            $undesirable_redirects = array(
                get_base_url(),
                get_base_url() . '/',
                build_url(array('page' => ''), '', array(), false, false, true),
                build_url(array('page' => ''), '', array(), false, true, true),
            );
        }

        if ((in_array($test->download_url, $undesirable_redirects)) && (!in_array($url, $undesirable_redirects))) {
            return false;
        }

        // ---

        return true;
    }
}