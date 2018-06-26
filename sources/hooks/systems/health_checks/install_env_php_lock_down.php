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
class Hook_health_check_install_env_php_lock_down extends Hook_Health_Check
{
    protected $category_label = 'Installation environment (PHP)';

    /**
     * Standard hook run function to run this category of health checks.
     *
     * @param  ?array $sections_to_run Which check sections to run (null: all)
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     * @return array A pair: category label, list of results
     */
    public function run($sections_to_run, $check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null)
    {
        $this->process_checks_section('testMemoryLimits', 'Memory limit', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass);
        $this->process_checks_section('testMbstringOverload', 'mbstring overload', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass);
        $this->process_checks_section('testMaxInputVars', 'max_input_vars', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass);
        $this->process_checks_section('testSuhosin', 'Suhosin', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass);
        $this->process_checks_section('testMaxExecutionTime', 'max_execution_time', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass);
        $this->process_checks_section('testNeededFunctions', 'Needed functions', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass);
        $this->process_checks_section('testFileUploads', 'File uploads', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass);
        $this->process_checks_section('testOpenBasedir', 'open_basedir', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass);

        return array($this->category_label, $this->results);
    }

    /**
     * Run a section of health checks.
     *
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     */
    public function testMemoryLimits($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null)
    {
        $setting = get_cfg_var('memory_limit');
        $low_memory = (!empty($setting)) && ($setting != '-1') && ($setting != '0') && (intval(trim(str_replace('M', '', $setting))) < 128);
        $this->assertTrue(!$low_memory, do_lang('LOW_MEMORY_LIMIT'));
    }

    /**
     * Run a section of health checks.
     *
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     */
    public function testMbstringOverload($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null)
    {
        $test = ini_get('mbstring.func_overload');
        $func_overload_set = (($test !== false) && ($test !== '') && ($test !== '0'));
        $this->assertTrue(!$func_overload_set, do_lang('WARNING_MBSTRING_FUNC_OVERLOAD'));
    }

    /**
     * Run a section of health checks.
     *
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     */
    public function testMaxInputVars($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null)
    {
        // .user.ini should set this correctly, but let's be sure it's not been force-lowered

        foreach (array('max_input_vars', 'suhosin.post.max_vars', 'suhosin.request.max_vars') as $setting) {
            if (@is_numeric(ini_get($setting))) {
                $this_setting_value = intval(ini_get($setting));
                $this->assertTrue($this_setting_value >= 1000, do_lang('__SUHOSIN_MAX_VARS_TOO_LOW', $setting));
            } else {
                $this->assertTrue(true, do_lang('__SUHOSIN_MAX_VARS_TOO_LOW', $setting));
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
     */
    public function testSuhosin($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null)
    {
        $this->assertTrue(ini_get('suhosin.executor.disable_eval') !== '1', do_lang('DISABLED_FUNCTION', 'eval'));

        $setting_minimums = array(
            'suhosin.cookie.max_vars' => 100,
            'suhosin.post.max_value_length' => 100000000,
            'suhosin.get.max_value_length' => 512,
            'suhosin.request.max_value_length' => 100000000,
            'suhosin.cookie.max_value_length' => 10000,
            'suhosin.post.max_name_length' => 64,
            'suhosin.get.max_name_length' => 64,
            'suhosin.request.max_name_length' => 64,
            'suhosin.cookie.max_name_length' => 64,
            'suhosin.post.max_totalname_length' => 256,
            'suhosin.get.max_totalname_length' => 256,
            'suhosin.request.max_totalname_length' => 256,
            'suhosin.cookie.max_totalname_length' => 256,
        );
        foreach ($setting_minimums as $key => $min) {
            $val = ini_get($key);
            $this->assertTrue((empty($val)) || (intval($val) < $min), 'The ' . $key . ' Suhosin PHP setting should be raised (see [tt]recommended.htaccess[/tt])');
        }

        $settings_off = array(
            'suhosin.cookie.encrypt',
            'suhosin.sql.union',
            'suhosin.sql.comment',
            'suhosin.sql.multiselect',
            'suhosin.upload.remove_binary',
        );
        foreach ($settings_off as $key) {
            $val = ini_get($key);
            $this->assertTrue(empty($val), 'The ' . $key . ' Suhosin PHP setting should be off (see [tt]recommended.htaccess[/tt])');
        }

        if (!is_maintained('platform_suhosin')) {
            if (php_function_allowed('extension_loaded')) {
                $this->assertTrue(
                    (!extension_loaded('suhosin')),
                    '[html]' . do_lang('WARNING_NON_MAINTAINED', escape_html('Suhosin'), escape_html(get_brand_base_url()), escape_html('platform_suhosin')) . '[/html]'
                );
            } else {
                $this->stateCheckSkipped('PHP extension_loaded function not available');
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
     */
    public function testMaxExecutionTime($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null)
    {
        // .user.ini should set this correctly, but let's be sure it's not been force-lowered

        $low_met = (is_numeric(ini_get('max_execution_time'))) && (intval(ini_get('max_execution_time')) > 0) && (intval(ini_get('max_execution_time')) < 10);
        $this->assertTrue(!$low_met, do_lang('WARNING_MAX_EXECUTION_TIME'));
    }

    /**
     * Run a section of health checks.
     *
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     */
    public function testNeededFunctions($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null)
    {
        // These aren't all actually needed. But we can't reasonably expect developers to work if arbitrary stuff may be disabled:
        // so we allow everything that we could reasonably assume will be there.
        $baseline_functions = <<<END
            abs addslashes array_count_values array_diff array_flip array_key_exists array_keys
            array_intersect array_merge array_pop array_push array_reverse array_search array_shift
            array_slice array_splice array_unique array_values arsort asort base64_decode base64_encode
            call_user_func ceil chdir checkdate chmod chr chunk_split class_exists clearstatcache closedir
            constant copy cos count crypt current date dechex decoct define defined dirname
            deg2rad error_reporting eval exit explode fclose feof fgets file file_exists
            file_get_contents filectime filegroup filemtime fileowner fileperms filesize floatval floor
            get_defined_vars get_declared_classes get_defined_functions fopen fread fseek ftell
            function_exists fwrite get_class get_html_translation_table getcwd
            getdate getenv gmdate header headers_sent hexdec htmlentities is_float ob_get_level
            implode in_array include include_once ini_get ini_set intval is_a is_array is_bool
            is_integer is_null is_numeric is_object is_readable is_resource is_string is_uploaded_file
            isset krsort ksort localeconv ltrim mail max md5 method_exists microtime min is_writable
            mkdir mktime move_uploaded_file mt_getrandmax mt_rand mt_srand number_format ob_end_clean
            ob_end_flush ob_get_contents ob_start octdec opendir ord pack parse_url pathinfo
            preg_replace preg_replace_callback preg_split print_r rawurldecode rmdir
            rawurlencode readdir realpath register_shutdown_function rename require require_once reset
            round rsort rtrim serialize set_error_handler preg_match preg_grep preg_match_all
            setcookie setlocale sha1 sin sort fprintf sprintf srand str_pad str_repeat str_replace
            strcmp strftime strip_tags stripslashes strlen strpos strrpos strstr strtok strtolower
            strtotime strtoupper strtr strval substr substr_count time trim trigger_error
            uasort ucfirst lcfirst ucwords uksort uniqid unlink unserialize unset urldecode urlencode usort
            utf8_decode utf8_encode wordwrap cos array_rand array_unshift asin assert
            assert_options atan base_convert basename bin2hex bindec call_user_func_array
            connection_aborted connection_status crc32 decbin empty fflush fileatime flock flush
            gethostbyaddr getrandmax gmmktime gmstrftime ip2long is_dir is_file
            levenshtein log log10 long2ip md5_file pow preg_quote prev rad2deg
            range readfile shuffle similar_text sqrt strcasecmp strcoll strcspn stristr strnatcasecmp
            strnatcmp strncasecmp strncmp strrchr strrev strspn substr_replace tan unpack version_compare
            gettype var_dump vprintf vsprintf touch tanh sinh stripcslashes
            restore_error_handler rewind rewinddir exp lcg_value localtime addcslashes
            array_filter array_map array_merge_recursive array_multisort array_pad array_reduce array_walk
            atan2 fgetc fgetcsv fgetss filetype fscanf fstat array_change_key_case
            date_default_timezone_get ftruncate func_get_arg func_get_args func_num_args
            parse_ini_file parse_str is_executable memory_get_usage
            is_scalar nl2br ob_get_length ob_implicit_flush
            ob_clean printf cosh count_chars gethostbynamel getlastmod fpassthru
            gettimeofday get_cfg_var get_resource_type hypot ignore_user_abort array_intersect_assoc
            is_link is_callable debug_print_backtrace stream_context_create next array_sum
            file_get_contents str_word_count html_entity_decode
            array_combine array_walk_recursive header_remove
            str_split strpbrk substr_compare file_put_contents get_headers headers_list
            http_build_query scandir str_shuffle
            ob_get_clean array_diff_assoc glob debug_backtrace date_default_timezone_set sha1
            array_diff_key inet_pton array_product json_encode json_decode
            inet_ntop fputcsv is_nan is_finite is_infinite ob_flush array_chunk array_fill
            var_export array_intersect_key end sys_get_temp_dir error_get_last stream_get_contents
            gethostbyname htmlspecialchars stat str_ireplace stripos key pi print set_exception_handler acos
            readgzfile ob_gzhandler gzcompress gzdeflate gzencode gzfile gzinflate gzuncompress gzclose gzopen gzwrite
            array_column array_fill_keys getimagesizefromstring hash_equals preg_last_error
            http_response_code memory_get_peak_usage password_get_info password_hash gzdecode hex2bin
            password_needs_rehash password_verify str_getcsv strripos spl_autoload_register
END;

        if (function_exists('imagecreatefromstring')) {
            $baseline_functions .= <<<END
                imagecreatefromgif imagegif
                imagepalettetotruecolor iptcembed iptcparse
                imagecolorallocatealpha imageistruecolor imagealphablending imagecolorallocate imagecolortransparent imagecopy
                imagecopyresampled imagecopyresized imagecreate imagecreatefrompng
                imagecreatefromjpeg imagecreatetruecolor imagecolorat imagecolorsforindex
                imagedestroy imagefill imagefontheight imagefontwidth imagesavealpha
                imagesetpixel imagestring imagesx imagesy imagestringup imagettftext imagetypes
                imagearc imagefilledarc imagecopymergegray imageline imageellipse imagefilledellipse
                imagechar imagefilledpolygon imagepolygon imagefilledrectangle imagerectangle imagefilltoborder
                imagegammacorrect imageinterlace imageloadfont imagepalettecopy imagesetbrush
                imagesetstyle imagesetthickness imagesettile imagetruecolortopalette
                imagecharup imagecolorclosest imagecolorclosestalpha imagecolorclosesthwb
                imagecolordeallocate imagecolorexact imagecolorexactalpha imagecolorresolve image_type_to_mime_type
                imagecolorresolvealpha imagecolorset imagecolorstotal imagecopymerge getimagesize image_type_to_extension imagefilter
                gd_info
END;

            // These ones are separately checked as extension checks
            $notused = <<<END
                imagecreatefromstring imagejpeg imagepng imagettfbbox
END;
        }

        foreach (preg_split('#\s+#', $baseline_functions) as $function) {
            if (trim($function) == '') {
                continue;
            }
            $ext = ((strpos($function, 'image') !== false) && (!function_exists('imagettfbbox'))); // GD/TTF is non-optional, but if it's not there it's likely due to extension being missing
            $this->assertTrue(php_function_allowed($function), do_lang($ext ? 'NONPRESENT_EXTENSION_FUNCTION' : 'DISABLED_FUNCTION', $function));
        }
    }

    /**
     * Run a section of health checks.
     *
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     */
    public function testFileUploads($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null)
    {
        $this->assertTrue(ini_get('file_uploads') != '0', do_lang('NO_UPLOAD'));

        foreach (array('post_max_size', 'upload_max_filesize') as $setting) {
            require_code('files');
            $bytes = php_return_bytes(ini_get($setting));
            $this->assertTrue(
                $bytes >= 8000000,
                '[html]' . do_lang('PHP_UPLOAD_SETTING_VERY_LOW', $setting, ini_get($setting), integer_format($bytes)) . '[/html]'
            );
        }
    }

    /**
     * Run a section of health checks.
     *
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     */
    public function testOpenBasedir($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null)
    {
        require_code('files2');
        if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {
            $files = get_directory_contents('/home', '', null, false);
        } else {
            $files = get_directory_contents('C:\\', '', null, false);
        }
        $this->assertTrue(count($files) == 0, do_lang('WARNING_OPEN_BASEDIR'));
    }
}
