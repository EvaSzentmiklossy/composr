<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

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

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__lang_compile()
{
    global $DECACHED_COMCODE_LANG_STRINGS;
    $DECACHED_COMCODE_LANG_STRINGS = false;
}

/**
 * Load up a language file, compiling it (it's not cached yet).
 *
 * @param  ID_TEXT $codename The language file name
 * @param  ?LANGUAGE_NAME $lang The language (null: uses the current language)
 * @param  ?string $type The language type (lang_custom, or custom) (null: normal priorities are used)
 * @set    lang_custom custom
 * @param  PATH $cache_path Where we are caching too
 * @param  boolean $ignore_errors Whether to just return if there was a loading error
 * @return boolean Whether we FAILED to load
 */
function require_lang_compile($codename, $lang, $type, $cache_path, $ignore_errors = false)
{
    global $LANGUAGE_STRINGS_CACHE, $REQUIRE_LANG_LOOP, $LANG_LOADED_LANG;

    $desire_cache = (function_exists('has_caching_for') && has_caching_for('lang'));
    if ($desire_cache) {
        if (!$GLOBALS['IN_MINIKERNEL_VERSION']) {
            global $DECACHED_COMCODE_LANG_STRINGS;

            // Cleanup language strings
            if (!$DECACHED_COMCODE_LANG_STRINGS) {
                $DECACHED_COMCODE_LANG_STRINGS = true;
                $comcode_lang_strings = $GLOBALS['SITE_DB']->query('SELECT string_index FROM ' . get_table_prefix() . 'cached_comcode_pages WHERE ' . db_string_equal_to('the_zone', '') . ' AND the_page LIKE \'' . db_encode_like($codename . ':') . '\'');
                if ($comcode_lang_strings !== null) {
                    foreach ($comcode_lang_strings as $comcode_lang_string) {
                        $GLOBALS['SITE_DB']->query_delete('cached_comcode_pages', $comcode_lang_string);
                        delete_lang($comcode_lang_string['string_index']);
                    }
                }
            }
        }

        $load_target = array();
    } else {
        $load_target = &$LANGUAGE_STRINGS_CACHE[$lang];
    }

    global $FILE_ARRAY;
    if ((@is_array($FILE_ARRAY)) && (file_array_exists('lang/' . $lang . '/' . $codename . '.ini'))) {
        $lang_file = 'lang/' . $lang . '/' . $codename . '.ini';
        $file = file_array_get($lang_file);
        _get_lang_file_map($file, $load_target, 'strings', true, true, $lang);
        $bad = true;
    } else {
        $bad = true;
        $dirty = false;

        // Load originals
        $lang_file = get_file_base() . '/lang/' . $lang . '/' . filter_naughty($codename) . '.ini';
        if (is_file($lang_file)) { // Non-custom, Proper language
            _get_lang_file_map($lang_file, $load_target, 'strings', false, true, $lang);
            $bad = false;
        }

        // Load overrides now if they are there
        if ($type !== 'lang') {
            $lang_file = get_custom_file_base() . '/lang_custom/' . $lang . '/' . $codename . '.ini';
            if ((!is_file($lang_file)) && (get_file_base() !== get_custom_file_base())) {
                $lang_file = get_file_base() . '/lang_custom/' . $lang . '/' . $codename . '.ini';
            }
        }
        if (($type !== 'lang') && (is_file($lang_file))) {
            _get_lang_file_map($lang_file, $load_target, 'strings', false, true, $lang);
            $bad = false;
            $dirty = true; // Tainted from the official pack, so can't store server wide
        }

        // NB: Merge op doesn't happen in require_lang. It happens when do_lang fails and then decides it has to force a recursion to do_lang(xx, fallback_lang()) which triggers require_lang(xx, fallback_lang()) when it sees it's not loaded

        if (($bad) && ($lang !== fallback_lang())) { // Still some hope
            require_lang($codename, fallback_lang(), $type, $ignore_errors);
            $REQUIRE_LANG_LOOP--;
            $fallback_cache_path = get_custom_file_base() . '/caches/lang/' . fallback_lang() . '/' . $codename . '.lcd';
            if (is_file($fallback_cache_path)) {
                require_code('files');
                @copy($fallback_cache_path, $cache_path);
                fix_permissions($cache_path);
            }

            if (!array_key_exists($lang, $LANG_LOADED_LANG)) {
                $LANG_LOADED_LANG[$lang] = array();
            }
            $LANG_LOADED_LANG[$lang][$codename] = true;

            return $bad;
        }

        if ($bad) { // Out of hope
            if ($ignore_errors) {
                return true;
            }

            if (($codename !== 'critical_error') || ($lang !== get_site_default_lang())) {
                $error_msg = do_lang_tempcode('MISSING_LANG_FILE', escape_html($codename), escape_html($lang));
                if (get_page_name() == 'admin_themes') {
                    warn_exit($error_msg);
                } else {
                    fatal_exit($error_msg);
                }
            } else {
                critical_error('CRIT_LANG');
            }
        }
    }

    // Cache
    if ($desire_cache) {
        require_code('files');
        cms_file_put_contents_safe($cache_path, serialize($load_target), FILE_WRITE_FAILURE_SOFT | FILE_WRITE_FIX_PERMISSIONS);
    }

    if ($desire_cache) {
        $LANGUAGE_STRINGS_CACHE[$lang] += $load_target;
    }

    return $bad;
}

/**
 * Get an array of all the INI entries in the specified language for a particular section.
 *
 * @param  LANGUAGE_NAME $lang The language
 * @param  ?ID_TEXT $file The language file (null: all language files)
 * @param  string $section The section
 * @return array The INI entries
 */
function get_lang_file_section($lang, $file = null, $section = 'descriptions')
{
    $entries = array();

    if ($file === null) {
        foreach (array('lang', 'lang_custom') as $dir) {
            $dh = @opendir(get_file_base() . '/' . $dir . '/' . $lang);
            if ($dh !== false) {
                while (($f = readdir($dh)) !== false) {
                    if (substr($f, -4) == '.ini') {
                        $entries = array_merge($entries, get_lang_file_section($lang, basename($f, '.ini'), $section));
                    }
                }
                closedir($dh);
            }
        }
        return $entries;
    }

    $a = get_custom_file_base() . '/lang_custom/' . $lang . '/' . $file . '.ini';
    if ((get_custom_file_base() !== get_file_base()) && (!is_file($a))) {
        $a = get_file_base() . '/lang_custom/' . $lang . '/' . $file . '.ini';
    }

    $b = (is_file($a)) ? $a : get_file_base() . '/lang/' . $lang . '/' . $file . '.ini';

    if (!is_file($b)) {
        $b = get_file_base() . '/lang/' . fallback_lang() . '/' . $file . '.ini';
    }

    require_code('lang_compile');
    _get_lang_file_map($b, $entries, $section, false, true, $lang);
    return $entries;
}

/**
 * Get an array of all the INI language entries in the specified language.
 *
 * @param  LANGUAGE_NAME $lang The language
 * @param  ID_TEXT $file The language file
 * @param  boolean $non_custom Force usage of original file
 * @param  boolean $apply_filter Apply the language pack filter
 * @return array The language entries
 */
function get_lang_file_map($lang, $file, $non_custom = false, $apply_filter = true)
{
    $a = get_custom_file_base() . '/lang_custom/' . $lang . '/' . $file . '.ini';
    if ((get_custom_file_base() !== get_file_base()) && (!is_file($a))) {
        $a = get_file_base() . '/lang_custom/' . $lang . '/' . $file . '.ini';
    }

    if ((!is_file($a)) || ($non_custom)) {
        $b = get_file_base() . '/lang/' . $lang . '/' . $file . '.ini';

        if (is_file($b)) {
            $a = $b;
        } else {
            if ($non_custom) {
                return array();
            }
        }
    }

    $target = array();
    _get_lang_file_map($a, $target, 'strings', false, $apply_filter, $lang);
    return $target;
}

/**
 * Extend a language map from strings in a given language file.
 *
 * @param  PATH $b The path to the language file
 * @param  array $entries The currently loaded language map
 * @param  string $section The section to get
 * @param  boolean $given_whole_file Whether $b is in fact not a path, but the actual file contents
 * @param  boolean $apply_filter Apply the language pack filter
 * @param  ?LANGUAGE_NAME $lang Language (null: current language)
 *
 * @ignore
 */
function _get_lang_file_map($b, &$entries, $section = 'strings', $given_whole_file = false, $apply_filter = true, $lang = null)
{
    if (!$given_whole_file) {
        if (!is_file($b)) {
            return;
        }

        $tmp = fopen($b, 'rb');
        // TODO: #3467
        flock($tmp, LOCK_SH);
        $lines = file($b);
        flock($tmp, LOCK_UN);
        fclose($tmp);
    } else {
        $lines = explode("\n", unixify_line_format($b));
    }

    global $LANG_FILTER_OB;

    // Parse ini file
    $in_lang = false;
    $nl = "\r\n";
    foreach ($lines as $line) {
        $line = rtrim($line, $nl);
        if ($line === '') {
            continue;
        }

        if ($line[0] === '[') {
            $in_lang = ($line === '[' . $section . ']');
        }

        if ($in_lang) {
            $parts = explode('=', $line, 2);

            if (isset($parts[1])) {
                $key = $parts[0];
                $value = str_replace('\n', "\n", rtrim($parts[1], $nl));
                if ($apply_filter) {
                    $value = $LANG_FILTER_OB->compile_time($key, $value, $lang);
                }
                $entries[$key] = $value;
            }
        }
    }
}
