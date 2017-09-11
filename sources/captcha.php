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
 * @package    captcha
 */

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__captcha()
{
    require_lang('captcha');
}

/**
 * Outputs and stores information for a CAPTCHA.
 */
function captcha_script()
{
    if (get_option('recaptcha_site_key') != '') {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    if (!function_exists('imagepng')) {
        warn_exit(do_lang_tempcode('GD_NEEDED'));
    }

    $code_needed = $GLOBALS['SITE_DB']->query_select_value_if_there('captchas', 'si_code', array('si_session_id' => get_session_id()));
    if ($code_needed === null) {
        generate_captcha();
        $code_needed = $GLOBALS['SITE_DB']->query_select_value_if_there('captchas', 'si_code', array('si_session_id' => get_session_id()));
        /*set_http_status_code(500);    This would actually be very slightly insecure, as it could be used to probe (binary) login state via rogue sites that check if CAPTCHAs had been generated
        warn_exit(do_lang_tempcode('CAPTCHA_NO_SESSION'));*/
    }
    mt_srand(crc32($code_needed)); // Important: to stop averaging out of different attempts. This makes the distortion consistent for that particular code.

    safe_ini_set('ocproducts.xss_detect', '0');

    $mode = get_param_string('mode', '');

    // Audio version
    if (($mode == 'audio') && (get_option('audio_captcha') === '1')) {
        header('Content-Type: audio/x-wav');
        header('Content-Disposition: inline; filename="captcha.wav"');
        //header('Content-Disposition: attachment; filename="captcha.wav"');  Useful for testing

        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            return;
        }

        $data = '';
        for ($i = 0; $i < strlen($code_needed); $i++) {
            $char = strtolower($code_needed[$i]);

            $file_path = get_file_base() . '/data_custom/sounds/' . $char . '.wav';
            if (!file_exists($file_path)) {
                $file_path = get_file_base() . '/data/sounds/' . $char . '.wav';
            }
            $myfile = fopen($file_path, 'rb');
            if ($i != 0) {
                fseek($myfile, 44);
            } else {
                $data = fread($myfile, 44);
            }
            $_data = fread($myfile, filesize($file_path));
            for ($j = 0; $j < strlen($_data); $j++) {
                if (get_option('captcha_noise') == '1') {
                    $amp_mod = mt_rand(-2, 2);
                    $_data[$j] = chr(min(255, max(0, ord($_data[$j]) + $amp_mod)));
                }
                if (get_option('captcha_noise') == '1') {
                    if (($j != 0) && (mt_rand(0, 10) == 1)) {
                        $data .= $_data[$j - 1];
                    }
                }
                $data .= $_data[$j];
            }
            fclose($myfile);
        }

        safe_ini_set('zlib.output_compression', 'Off');

        // Fix up header
        $data = substr_replace($data, pack('V', strlen($data) - 8), 4, 4);
        $data = substr_replace($data, pack('V', strlen($data) - 44), 40, 4);
        header('Content-Length: ' . strval(strlen($data)));
        echo $data;
        return;
    }

    // Write basic, using multiple fonts with random Y-position offsets
    $characters = strlen($code_needed);
    $fonts = array();
    $width = 20;
    for ($i = 0; $i < $characters; $i++) {
        $font = mt_rand(4, 5); // 1 is too small
        $fonts[] = $font;
        $width += imagefontwidth($font) + 2;
    }
    $height = imagefontheight($font) + 20;
    $img = imagecreate($width, $height);
    $black = imagecolorallocate($img, 0, 0, 0);
    $off_black = imagecolorallocate($img, mt_rand(1, 45), mt_rand(1, 45), mt_rand(1, 45));
    $white = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $black);
    $x = 10;
    foreach ($fonts as $i => $font) {
        $y_dif = mt_rand(-15, 15);
        imagestring($img, $font, $x, 10 + $y_dif, $code_needed[strlen($code_needed) - $i - 1], $off_black);
        $x += imagefontwidth($font) + 2;
    }
    $x = 10;
    foreach ($fonts as $i => $font) {
        $y_dif = mt_rand(-5, 5);
        imagestring($img, $font, $x, 10 + $y_dif, $code_needed[$i], $white);
        if (get_option('captcha_noise') == '1') {
            imagestring($img, $font, $x + 1, 10 + mt_rand(-1, 1) + $y_dif, $code_needed[$i], $white);
        }
        $x += imagefontwidth($font) + 2;
    }

    // Add some noise
    if (get_option('captcha_noise') == '1') {
        $tricky_remap = array();
        $tricky_remap[$black] = array();
        $tricky_remap[$off_black] = array();
        $tricky_remap[$white] = array();
        for ($i = 0; $i <= 5; $i++) {
            $tricky_remap['!' . strval($black)][] = imagecolorallocate($img, 0 + mt_rand(0, 15), 0 + mt_rand(0, 15), 0 + mt_rand(0, 15));
            $tricky_remap['!' . strval($off_black)][] = $off_black;
            $tricky_remap['!' . strval($white)][] = imagecolorallocate($img, 255 - mt_rand(0, 145), 255 - mt_rand(0, 145), 255 - mt_rand(0, 145));
        }
        $noise_amount = 0.02;//0.04;
        for ($i = 0; $i < intval($width * $height * $noise_amount); $i++) {
            $x = mt_rand(0, $width);
            $y = mt_rand(0, $height);
            if (mt_rand(0, 1) == 0) {
                imagesetpixel($img, $x, $y, $white);
            } else {
                imagesetpixel($img, $x, $y, $black);
            }
        }
        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {
                imagesetpixel($img, $i, $j, $tricky_remap['!' . strval(imagecolorat($img, $i, $j))][mt_rand(0, 5)]);
            }
        }
    }

    // Output using CSS
    if (get_option('css_captcha') === '1') {
        echo '
        <!DOCTYPE html>
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <title>' . do_lang('CONTACT_STAFF_TO_JOIN_IF_IMPAIRED') . '</title>
            <meta name="robots" content="noindex" />
        </head>
        <body style="margin: 0">
        ';
        if (get_option('js_captcha') === '1') {
            echo '<div style="display: none" id="hidden_captcha">';
        }
        echo '<div style="width: ' . strval($width) . 'px; font-size: 0; line-height: 0">';
        for ($j = 0; $j < $height; $j++) {
            for ($i = 0; $i < $width; $i++) {
                $colour = imagecolorsforindex($img, imagecolorat($img, $i, $j));
                echo '<span style="vertical-align: bottom; overflow: hidden; display: inline-block; -webkit-text-size-adjust: none; text-size-adjust: none; background: rgb(' . strval($colour['red']) . ',' . strval($colour['green']) . ',' . strval($colour['blue']) . '); width: 1px; height: 1px"></span>';
            }
        }
        echo '</div>';
        if (get_option('js_captcha') === '1') {
            echo '</div>';
            echo '<script ' . csp_nonce_html() . '>document.getElementById(\'hidden_captcha\').style.display = \'block\';</script>';
        }
        echo '
        </body>
        </html>
        ';
        imagedestroy($img);
        exit();
    }

    // Output as a PNG
    header('Content-Type: image/png');
    imagepng($img);
    imagedestroy($img);
}

/**
 * Get a captcha (aka security code) form field.
 *
 * @param  Tempcode $hidden Hidden fields (will attach to here for non-visible CAPTCHA systems)
 * @return Tempcode The field
 */
function form_input_captcha($hidden)
{
    $tabindex = get_form_field_tabindex(null);

    generate_captcha();

    // Show template
    $input = do_template('FORM_SCREEN_INPUT_CAPTCHA', array('_GUID' => 'f7452af9b83db36685ae8a86f9762d30', 'TABINDEX' => strval($tabindex)));
    if (get_option('recaptcha_site_key') != '') {
        $hidden->attach($input);
        return new Tempcode();
    }
    return _form_input('captcha', do_lang_tempcode('SECURITY_IMAGE'), do_lang_tempcode('DESCRIPTION_CAPTCHA'), $input, true, false);
}

/**
 * Find whether captcha (the security image) should be used if preferred (making this call assumes it is preferred).
 *
 * @return boolean Whether captcha is used
 */
function use_captcha()
{
    if (get_option('recaptcha_site_key') != '') {
        return ((is_guest()) && (intval(get_option('use_captchas')) == 1));
    }

    $answer = ((is_guest()) && (intval(get_option('use_captchas')) == 1) && (function_exists('imagetypes')));
    return $answer;
}

/**
 * Generate a CAPTCHA image.
 */
function generate_captcha()
{
    if (get_option('recaptcha_site_key') != '') {
        attach_to_screen_footer('<script ' . csp_nonce_html() . ' src="https://www.google.com/recaptcha/api.js?render=explicit&amp;onload=recaptchaLoaded&amp;hl=' . strtolower(user_lang()) . '" async="async" defer="defer"></script>');
        return;
    }

    $session = get_session_id();

    // Clear out old codes
    $GLOBALS['SITE_DB']->query('DELETE FROM ' . get_table_prefix() . 'captchas WHERE si_time<' . strval(time() - 60 * 30) . ' OR ' . db_string_equal_to('si_session_id', $session));

    // Create code
    $choices = array('3', '4', '6', '7', '9', 'A', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'M', 'N', 'P', 'R', 'T', 'W', 'X', 'Y');
    $si_code = '';
    for ($i = 0; $i < 6; $i++) {
        $choice = mt_rand(0, count($choices) - 1);
        $si_code .= $choices[$choice]; // NB: In ASCII code all the chars in $choices are 10-99 (i.e. 2 digit)
    }

    // Store code
    $GLOBALS['SITE_DB']->query_insert('captchas', array('si_session_id' => $session, 'si_time' => time(), 'si_code' => $si_code), false, true);
}

/**
 * Calling this assumes captcha was needed. Checks that it was done correctly.
 *
 * @param  boolean $regenerate_on_error Whether to possibly regenerate upon error
 */
function enforce_captcha($regenerate_on_error = true)
{
    if (use_captcha()) {
        $error_message = do_lang_tempcode('INVALID_SECURITY_CODE_ENTERED');
        if (!check_captcha(null, $regenerate_on_error, $error_message)) {
            set_http_status_code(500);

            warn_exit($error_message, false, true);
        }
    }
}

/**
 * Checks a CAPTCHA.
 *
 * @param  ?string $code_entered CAPTCHA entered (null: read from standard-named parameter)
 * @param  boolean $regenerate_on_error Whether to possibly regenerate upon error
 * @param  ?Tempcode $error_message Error message to write out (null: none)
 * @return boolean Whether it is valid for the current session
 */
function check_captcha($code_entered = null, $regenerate_on_error = true, &$error_message = null)
{
    if (use_captcha()) {
        if (get_option('recaptcha_site_key') != '') {
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $post_params = array(
                'secret' => get_option('recaptcha_server_key'),
                'response' => post_param_string('g-recaptcha-response'),
            );
            $_response = http_get_contents($url, array('post_params' => $post_params));
            $response = json_decode($_response, true);

            if (!$response['success']) {
                foreach ($response['error-codes'] as $error_code) {
                    switch ($error_code) {
                        case 'timeout-or-duplicate':
                            $error_message = do_lang_tempcode('RECAPTCHA_ERROR_' . str_replace('-', '_', $error_code));
                            break;

                        case 'missing-input-secret':
                        case 'invalid-input-secret':
                        case 'missing-input-response':
                        case 'invalid-input-response':
                        case 'bad-request':
                            $error_message = do_lang_tempcode('RECAPTCHA_ERROR_' . str_replace('-', '_', $error_code));
                            break;
                    }
                }
            }

            return $response['success'];
        }

        if ($code_entered === null) {
            $code_entered = post_param_string('captcha');
        }

        $code_needed = $GLOBALS['SITE_DB']->query_select_value_if_there('captchas', 'si_code', array('si_session_id' => get_session_id()));
        if ($code_needed === null) {
            if (get_option('captcha_single_guess') == '1') {
                generate_captcha();
            }
            attach_message(do_lang_tempcode('NO_SESSION_SECURITY_CODE'), 'warn');
            return false;
        }
        $passes = (strtolower($code_needed) == strtolower($code_entered));
        if ($regenerate_on_error) {
            if (get_option('captcha_single_guess') == '1') {
                if ($passes) {
                    register_shutdown_function('_cleanout_captcha');
                } else {
                    generate_captcha();
                }
            }
        }
        if (!$passes) {
            $data = serialize($_POST);

            // Log hack-attack
            if (
                (strpos($data, '[url=http://') !== false) ||
                (strpos($data, '[link=') !== false) ||
                ((strpos($data, ' href="') !== false) && (strpos($data, '[html') === false) && (strpos($data, '[semihtml') === false) && (strpos($data, '__is_wysiwyg') === false))
            ) {
                log_hack_attack_and_exit('CAPTCHAFAIL_HACK', '', '', true);
            } else {
                log_hack_attack_and_exit('CAPTCHAFAIL', '', '', true, false, 1); // Very low-scored, because it may well just be user-error
            }
        }
        return $passes;
    }
    return true;
}

/**
 * Delete current CAPTCHA.
 *
 * @ignore
 */
function _cleanout_captcha()
{
    if (get_option('recaptcha_site_key') != '') {
        return;
    }

    if (!running_script('snippet')) {
        $GLOBALS['SITE_DB']->query_delete('captchas', array('si_session_id' => get_session_id())); // Only allowed to check once
    }
}

/**
 * Get code to do an AJAX check of the CAPTCHA.
 *
 * @return string Function name
 */
function captcha_ajax_check_function()
{
    if (!use_captcha()) {
        return '';
    }

    require_javascript('captcha');
    return 'captchaCaptchaAjaxCheck';
}
