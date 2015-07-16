<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2015

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core_cns
 */

/**
 * Hook class.
 */
class Hook_cron_cns_birthdays
{
    /**
     * Run function for CRON hooks. Searches for tasks to perform.
     */
    public function run()
    {
        $this_birthday_day = date('d/m/Y', tz_time(time(), get_site_timezone()));
        if (get_value('last_birthday_day', null, true) !== $this_birthday_day) {
            set_value('last_birthday_day', $this_birthday_day, true);

            require_lang('cns');

            require_code('cns_general');
            $_birthdays = cns_find_birthdays();
            $birthdays = new Tempcode();
            foreach ($_birthdays as $_birthday) {
                $member_url = $GLOBALS['CNS_DRIVER']->member_profile_url($_birthday['id'], false, true);
                $username = $_birthday['username'];
                //$displayname=$GLOBALS['CNS_DRIVER']->get_username($_birthday['id'],true);
                $displayname = $GLOBALS['CNS_DRIVER']->get_displayname($username);
                $birthday_url = build_url(array('page' => 'topics', 'type' => 'birthday', 'id' => $_birthday['username']), get_module_zone('topics'));

                require_code('notifications');

                $subject = do_lang('BIRTHDAY_NOTIFICATION_MAIL_SUBJECT', get_site_name(), $displayname, $username);
                $mail = do_lang('BIRTHDAY_NOTIFICATION_MAIL', comcode_escape(get_site_name()), comcode_escape($username), array($member_url->evaluate(), $birthday_url->evaluate(), comcode_escape($displayname), strval($_birthday['id']), get_base_url(), urlencode($username)));

                if (addon_installed('chat')) {
                    $friends = $GLOBALS['SITE_DB']->query_select('chat_friends', array('member_likes'), array('member_liked' => $_birthday['id']));
                    dispatch_notification('cns_friend_birthday', null, $subject, $mail, collapse_1d_complexity('member_likes', $friends));
                }

                dispatch_notification('cns_birthday', null, $subject, $mail);
            }
        }
    }
}
