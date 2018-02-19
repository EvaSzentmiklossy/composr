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
 * @package    core_rich_media
 */

/**
 * Check the Comcode is valid.
 *
 * @param  LONG_TEXT $comcode The Comcode to convert
 * @param  ?MEMBER $source_member The member the evaluation is running as. This is a security issue, and you should only run as an administrator if you have considered where the Comcode came from carefully (null: current member)
 * @param  boolean $as_admin Whether to explicitly execute this with admin rights. There are a few rare situations where this should be done, for data you know didn't come from a member, but is being evaluated by one.
 * @param  ?object $db The database connector to use (null: standard site connector)
 * @param  boolean $attachment_possibility Whether there might be new attachments. If there are, we will check as lax- as attachments are always preserved by forcing lax parsing.
 */
function check_comcode($comcode, $source_member = null, $as_admin = false, $db = null, $attachment_possibility = false)
{
    if (running_script('stress_test_loader')) {
        return;
    }

    $lax = false;

    if ($attachment_possibility) {
        $has_one = false;
        foreach ($_POST as $key => $value) {
            if (is_string($key) && preg_match('#^hid_file_id_#i', $key) != 0) {
                require_code('uploads');
                $has_one = is_plupload();
            }
        }
        foreach ($_FILES as $key => $file) {
            $matches = array();
            if ((is_uploaded_file($file['tmp_name'])) && (preg_match('#file(\d)#', $key, $matches) != 0)) {
                $has_one = true;
            }
        }
        if ($has_one) {
            $lax = true; // We don't want a simple syntax error to cause us to lose our attachments
        }
    }

    comcode_to_tempcode($comcode, $source_member, $as_admin, null, $db, COMCODE_CHECK_ONLY | ($lax ? COMCODE_FORCE_LAX_MODE_ON : COMCODE_FORCE_LAX_MODE_OFF));
}
