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
 * @package    newsletter
 */

/**
 * Hook class.
 */
class Hook_task_import_newsletter_subscribers
{
    /**
     * Run the task hook.
     *
     * @param  LANGUAGE_NAME $_language The language for subscribers
     * @param  AUTO_LINK $newsletter_id The newsletter being imported into
     * @param  boolean $subscribe Whether we are subscribing the member (true=adding, false=removing)
     * @param  PATH $path The path of the file to import
     * @return ?array A tuple of at least 2: Return mime-type, content (either Tempcode, or a string, or a filename and file-path pair to a temporary file), map of HTTP headers if transferring immediately, map of ini_set commands if transferring immediately (null: show standard success message)
     */
    public function run($_language, $newsletter_id, $subscribe, $path)
    {
        require_lang('cns');
        require_lang('newsletter');
        require_code('newsletter');

        $done_special_notice = false;

        push_query_limiting(false);

        if (filesize($path) < 1024 * 1024 * 3) { // Cleanup possible line ending problems, but only if file not too big
            $fixed_contents = unixify_line_format(file_get_contents($path));
            require_code('files');
            cms_file_put_contents_safe($path, $fixed_contents, FILE_WRITE_FAILURE_SILENT | FILE_WRITE_FIX_PERMISSIONS);
        }

        safe_ini_set('auto_detect_line_endings', '1');
        $myfile = fopen($path, 'rb');
        $del = ',';
        $csv_test_line = fgetcsv($myfile, 4096, $del);
        if ((count($csv_test_line) == 1) && (strpos($csv_test_line[0], ';') !== false)) {
            $del = ';';
        }
        rewind($myfile);

        $email_index = 0;
        $forename_index = null;
        $surname_index = null;
        $username_index = null;
        $hash_index = null;
        $salt_index = null;
        $language_index = null;
        $code_confirm_index = null;
        $join_time_index = null;

        $count = 0;
        $count2 = 0;

        do {
            $i = 0;
            $_csv_data = array();
            while (($csv_line = fgetcsv($myfile, 4096, $del)) !== false) {
                $_csv_data[] = $csv_line;
                $i++;
                if ($i == 500) {
                    break;
                }
            }

            // Process data
            foreach ($_csv_data as $i => $csv_line) {
                if (($i <= 1) && (count($csv_line) >= 1) && ($csv_line[$email_index] !== null) && (strpos($csv_line[$email_index], '@') === false)) {
                    foreach ($csv_line as $j => $val) {
                        if (in_array(strtolower($val), array('e-mail', 'email', 'email address', 'e-mail address', strtolower(do_lang('EMAIL_ADDRESS'))))) {
                            $email_index = $j;
                        }
                        if (in_array(strtolower($val), array('forename', 'forenames', 'first name', strtolower(do_lang('FORENAME'))))) {
                            $forename_index = $j;
                        }
                        if (in_array(strtolower($val), array('surname', 'surnames', 'last name', strtolower(do_lang('SURNAME'))))) {
                            $surname_index = $j;
                        }
                        if (in_array(strtolower($val), array('username', strtolower(do_lang('NAME'))))) {
                            $username_index = $j;
                        }
                        if (in_array(strtolower($val), array('hash', 'password', 'pass', 'code', 'secret', strtolower(do_lang('PASSWORD_HASH'))))) {
                            $hash_index = $j;
                        }
                        if (in_array(strtolower($val), array('salt', strtolower(do_lang('SALT'))))) {
                            $salt_index = $j;
                        }
                        if (in_array(strtolower($val), array('lang', 'language', strtolower(do_lang('LANGUAGE'))))) {
                            $hash_index = $j;
                        }
                        if (in_array(strtolower($val), array('confirm code', strtolower(do_lang('CONFIRM_CODE'))))) {
                            $code_confirm_index = $j;
                        }
                        if ((stripos($val, 'time') !== false) || (stripos($val, 'date') !== false) || (strtolower($val) == do_lang('JOIN_DATE'))) {
                            $join_time_index = $j;
                        }
                    }
                    continue;
                }

                if ((count($csv_line) >= 1) && ($csv_line[$email_index] !== null) && (strpos($csv_line[$email_index], '@') !== false)) {
                    $email = $csv_line[$email_index];
                    $forename = (($forename_index !== null) && (array_key_exists($forename_index, $csv_line))) ? $csv_line[$forename_index] : '';
                    if ($forename == $email) {
                        $forename = ucfirst(strtolower(preg_replace('#^(\w+)([^\w].*)?$#', '\\1', $forename)));
                        if (in_array($forename, array('Sales', 'Info', 'Business', 'Enquiries', 'Admin'))) {
                            $forename = '';
                        }
                    }
                    $surname = (($surname_index !== null) && (array_key_exists($surname_index, $csv_line))) ? $csv_line[$surname_index] : '';
                    $username = (($username_index !== null) && (array_key_exists($username_index, $csv_line))) ? $csv_line[$username_index] : '';
                    $hash = (($hash_index !== null) && (array_key_exists($hash_index, $csv_line))) ? $csv_line[$hash_index] : '';
                    $salt = (($salt_index !== null) && (array_key_exists($salt_index, $csv_line))) ? $csv_line[$salt_index] : '';
                    $language = (($language_index !== null) && (array_key_exists($language_index, $csv_line)) && ((file_exists(get_custom_file_base() . '/lang/' . $csv_line[$language_index])) || (file_exists(get_custom_file_base() . '/lang_custom/' . $csv_line[$language_index])))) ? $csv_line[$language_index] : $_language;
                    if ($language == '') {
                        $language = $_language;
                    }
                    $code_confirm = (($code_confirm_index !== null) && (array_key_exists($code_confirm_index, $csv_line))) ? intval($csv_line[$code_confirm_index]) : 0;
                    $join_time = (($join_time_index !== null) && (array_key_exists($join_time_index, $csv_line))) ? strtotime($csv_line[$join_time_index]) : time();
                    if ($join_time === false) {
                        $join_time = time();
                    }

                    if ($newsletter_id == -1) {
                        $test = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_members', 'id', array('m_email_address' => $email));
                        if ($test === null) {
                            if ($subscribe) {
                                if (!$done_special_notice) {
                                    attach_message(do_lang_tempcode('NEWSLETTER_WONT_IMPORT_MEMBERS'), 'notice');
                                    $done_special_notice = true;
                                }
                            }
                        } else {
                            if ($subscribe) {
                                $GLOBALS['FORUM_DB']->query_update('f_members', array('m_allow_emails_from_staff' => 1), array('m_email_address' => $email), '', 1);
                            } else {
                                $GLOBALS['FORUM_DB']->query_update('f_members', array('m_allow_emails_from_staff' => 0), array('m_email_address' => $email), '', 1);
                                $count++;
                            }
                        }
                    } else {
                        $test = $GLOBALS['SITE_DB']->query_select_value_if_there('newsletter_subscribers', 'id', array('email' => $email));
                        if ($test === null) {
                            add_newsletter_subscriber($email, $join_time, $code_confirm, $hash, $salt, $language, $forename, $surname);

                            if ($subscribe) {
                                $count++;
                            }
                        } else {
                            edit_newsletter_subscriber($test, null, null, null, null, null, null, $forename, $surname);

                            if (!$subscribe) {
                                $count++;
                            }
                        }

                        // In case $email is already a subscriber, we delete first
                        $GLOBALS['SITE_DB']->query_delete('newsletter_subscribe', array(
                            'newsletter_id' => $newsletter_id,
                            'email' => $email,
                        ), '', 1);
                        if ($subscribe) {
                            $GLOBALS['SITE_DB']->query_insert('newsletter_subscribe', array(
                                'newsletter_id' => $newsletter_id,
                                'email' => $email,
                            ));
                        }
                    }

                    $count2++;
                }
            }
        } while (count($_csv_data) != 0);

        fclose($myfile);

        if ($subscribe) {
            $message = do_lang_tempcode('NEWSLETTER_IMPORTED_THIS', escape_html(integer_format($count)), escape_html(integer_format($count2)));
        } else {
            $message = do_lang_tempcode('NEWSLETTER_REMOVED_THIS', escape_html(integer_format($count)), escape_html(integer_format($count2)));
        }

        log_it('IMPORT_NEWSLETTER_SUBSCRIBERS');

        @unlink($path);
        return array('text/html', $message);
    }
}
