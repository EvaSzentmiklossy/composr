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
 * @package    core
 */

/*
This file is designed to be able to work as standalone, should you want to hook rewrite rules directly into it.
This allows static cache to run even when Composr is itself not booting at all.
*/

if (!isset($GLOBALS['FILE_BASE'])) {
    // Find Composr base directory, and chdir into it
    global $FILE_BASE;
    $FILE_BASE = (strpos(__FILE__, './') === false) ? __FILE__ : realpath(__FILE__);
    $FILE_BASE = dirname(dirname($FILE_BASE));

    chdir($FILE_BASE);

    require($FILE_BASE . '/_config.php');

    if (!defined('STATIC_CACHE__FAST_SPIDER')) {
        define('STATIC_CACHE__FAST_SPIDER', 1);
        define('STATIC_CACHE__GUEST', 2);
        define('STATIC_CACHE__FAILOVER_MODE', 4);
    }

    static_cache(STATIC_CACHE__FAILOVER_MODE);
}

/**
 * Get a well formed URL equivalent to the current URL. Reads direct from the environment and does no clever mapping at all. This function should rarely be used.
 *
 * @return URLPATH The URL
 */
function static_cache__get_self_url_easy()
{
    $self_url = '';
    if (!empty($_SERVER['REQUEST_URI'])) {
        $self_url .= $_SERVER['REQUEST_URI'];
    } elseif (!empty($_SERVER['PHP_SELF'])) {
        $self_url .= $_SERVER['PHP_SELF'];
        if (!empty($_SERVER['QUERY_STRING'])) {
            $self_url .= '?' . $_SERVER['QUERY_STRING'];
        }
    }
    return $self_url;
}

/**
 * Find if we can use the static cache.
 *
 * @return boolean Whether we can
 */
function can_static_cache()
{
    if (isset($_GET['redirect'])) {
        return false;
    }

    global $EXTRA_HEAD;
    if ($EXTRA_HEAD !== null) {
        if (strpos($EXTRA_HEAD->evaluate(), '<meta name="robots" content="noindex"') !== false) {
            return false; // Too obscure to waste cache space with
        }
    }

    global $NON_CANONICAL_PARAMS;
    if ($NON_CANONICAL_PARAMS !== null) {
        foreach ($NON_CANONICAL_PARAMS as $param => $block_page_from_static_cache_if_present) {
            if (isset($_GET[$param])) {
                if ($block_page_from_static_cache_if_present) {
                    return false; // Too parameterised
                }
            }
        }
    }

    if ((isset($_GET['page'])) && ($_GET['page'] == '404')) {
        return false;
    }

    return true;
}

/**
 * Get the URL we are considering static caching against.
 *
 * @return URLPATH The URL
 */
function static_cache_current_url()
{
    $url = static_cache__get_self_url_easy();
    $url = preg_replace('#(keep_session|keep_devtest|keep_failover)=\d+#', '', $url);
    $url = str_replace('keep_su=Guest', '', $url);
    $url = preg_replace('#\?&+#', '?', $url);
    $url = preg_replace('#&+#', '&', $url);
    $url = preg_replace('#[&?]$#', '', $url);
    return $url;
}

/**
 * If possible dump the user to 100% static caching.
 *
 * @param  integer $mode The mode
 */
function static_cache($mode)
{
    global $SITE_INFO;

    $script_name = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : (isset($_ENV['SCRIPT_NAME']) ? $_ENV['SCRIPT_NAME'] : '');
    if (basename($script_name) == 'backend.php') {
        $file_extension = '.xml';
    } else {
        $file_extension = '.htm';
    }

    if (($mode & STATIC_CACHE__FAILOVER_MODE) == 0) {
        if (!can_static_cache()) {
            return;
        }
    }

    if (($mode & STATIC_CACHE__FAILOVER_MODE) != 0) {
        // Correct HTTP status
        if ((!function_exists('browser_matches')) || (!function_exists('cms_srv')) || ((!browser_matches('ie')) && (strpos(cms_srv('SERVER_SOFTWARE'), 'IIS') === false))) {
            header('HTTP/1.0 503 Service Temporarily Unavailable');
        }
    }

    if (function_exists('is_mobile')) {
        $is_mobile = is_mobile();
    } else {
        // The set of browsers
        $browsers = array(
            // Implication by technology claims
            'WML',
            'WAP',
            'Wap',
            'MIDP', // Mobile Information Device Profile

            // Generics
            'Mobile',
            'Smartphone',
            'WebTV',

            // Well known/important browsers/brands
            'Mobile Safari', // Usually Android
            'iPhone',
            'iPod',
            'Opera Mobi',
            'Opera Mini',
            'BlackBerry',
            'Windows Phone',
            'nook browser', // Barnes and Noble
        );
        $is_mobile = (preg_match('#' . implode('|', $browsers) . '#', $_SERVER['HTTP_USER_AGENT']) != 0);
    }

    // Work out cache path (potentially will search a few places, based on priority)
    $url = static_cache_current_url();
    $_fast_cache_path = (function_exists('get_custom_file_base') ? get_custom_file_base() : $GLOBALS['FILE_BASE']) . '/caches/guest_pages/' . md5($url);
    $param_sets = array(
        array(
            'non_bot' => ($mode & STATIC_CACHE__FAST_SPIDER) == 0,
            'no_js' => !array_key_exists('js_on', $_COOKIE),
            'mobile' => $is_mobile,
            'failover_mode' => ($mode & STATIC_CACHE__FAILOVER_MODE) != 0,
        ),
    );
    if (($mode & STATIC_CACHE__FAILOVER_MODE) != 0) {
        foreach ($param_sets[0]['mobile'] ? array(true, false) : array(false, true) as $mobile) {
            foreach ($param_sets[0]['no_js'] ? array(true, false) : array(false, true) as $no_js) {
                foreach ($param_sets[0]['non_bot'] ? array(true, false) : array(false, true) as $non_bot) {
                    $param_sets[] = array(
                        'non_bot' => $non_bot,
                        'no_js' => $no_js,
                        'mobile' => $mobile,
                        'failover_mode' => true, // This is always saved as a variant anyway
                    );
                }
            }
        }
    }
    foreach ($param_sets as $param) {
        $fast_cache_path = $_fast_cache_path;
        if (!$param['failover_mode']) {
            if ($param['non_bot']) {
                $fast_cache_path .= '__non-bot';
            }
            if ($param['no_js']) {
                $fast_cache_path .= '__no-js';
            }
        }
        if ($param['mobile']) {
            $fast_cache_path .= '__mobile';
        }
        if ($param['failover_mode']) {
            $fast_cache_path .= '__failover_mode';
        }
        $fast_cache_path .= $file_extension;
        if (is_file($fast_cache_path)) {
            break;
        }
    }

    // Is cached
    if (is_file($fast_cache_path)) {
        if ($file_extension == '.htm') {
            header('Content-type: text/html');
        } else {
            header('Content-type: text/xml');
        }

        $expires = intval(60.0 * 60.0 * floatval($SITE_INFO['fast_spider_cache']));
        $mtime = filemtime($fast_cache_path);
        if (($mtime > time() - $expires) || (($mode & STATIC_CACHE__FAILOVER_MODE) != 0)) {
            // Only bots can do HTTP caching, as they won't try to login and end up reaching a previously cached page
            if ((($mode & STATIC_CACHE__FAST_SPIDER) != 0) && (($mode & STATIC_CACHE__FAILOVER_MODE) == 0) && (function_exists('cms_srv'))) {
                header("Pragma: public");
                header("Cache-Control: max-age=" . strval($expires));
                header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');

                $since = cms_srv('HTTP_IF_MODIFIED_SINCE');
                if ($since != '') {
                    if (strtotime($since) < $mtime) {
                        header('HTTP/1.0 304 Not Modified');
                        exit();
                    }
                }
            }

            // Output
            if ((($mode & STATIC_CACHE__FAILOVER_MODE) == 0) && (function_exists('gzencode')) && (function_exists('php_function_allowed')) && (php_function_allowed('ini_set'))) {
                ini_set('zlib.output_compression', 'Off');
                header('Content-Encoding: gzip');
            }
            $contents = file_get_contents($fast_cache_path);
            if (function_exists('ocp_mark_as_escaped')) {
                ocp_mark_as_escaped($contents);
            }
            if (($mode & STATIC_CACHE__FAILOVER_MODE) != 0) {
                $contents .= "\n\n" . '<!-- Served ' . htmlentities($fast_cache_path) . ' -->';
            }
            exit($contents);
        } else {
            @unlink($fast_cache_path);
            if (function_exists('sync_file')) {
                sync_file($fast_cache_path);
            }
        }
    }

    if (($mode & STATIC_CACHE__FAILOVER_MODE) != 0) {
        // Error message saying nothing cached
        header('Content-type: text/plain');
        if (!isset($SITE_INFO['failover_cache_miss_message'])) {
            $SITE_INFO['failover_cache_miss_message'] = 'Cannot find cache file.';
        }
        exit($SITE_INFO['failover_cache_miss_message']);
    }
}


