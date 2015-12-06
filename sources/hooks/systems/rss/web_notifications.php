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
 * @package    core_notifications
 */

/**
 * Hook class.
 */
class Hook_rss_web_notifications
{
    /**
     * Run function for RSS hooks.
     *
     * @param  string $_filters A list of categories we accept from
     * @param  TIME $cutoff Cutoff time, before which we do not show results from
     * @param  string $prefix Prefix that represents the template set we use
     * @set    RSS_ ATOM_
     * @param  string $date_string The standard format of date to use for the syndication type represented in the prefix
     * @param  integer $max The maximum number of entries to return, ordering by date
     * @return ?array A pair: The main syndication section, and a title (null: error)
     */
    public function run($_filters, $cutoff, $prefix, $date_string, $max)
    {
        require_code('notifications');
        require_lang('notifications');

        if (is_guest()) {
            return null;
        }

        $where = array(
            'd_to_member_id' => get_member(),
            'd_frequency' => A_WEB_NOTIFICATION,
        );

        $extra = 'ORDER BY d_date_and_time DESC';

        if ($_filters != '*') {
            $in_list = '';
            $in_list_priority = '';
            foreach (explode(',', $_filters) as $filter) {
                if (!is_numeric($filter)) {
                    if ($in_list != '') {
                        $in_list .= ',';
                    }
                    $in_list .= '\'' . db_escape_string($filter) . '\'';
                } else {
                    if ($in_list_priority != '') {
                        $in_list_priority .= ',';
                    }
                    $in_list_priority .= $filter;
                }
            }
            if ($in_list != '') {
                $extra = 'AND d_notification_code IN (' . $in_list . ')' . ' ' . $extra;
            }
            if ($in_list_priority != '') {
                $extra = 'AND d_priority IN (' . $in_list_priority . ')' . ' ' . $extra;
            }
        }

        $extra = 'AND d_date_and_time>' . strval($cutoff) . ' ' . $extra;

        $rows = $GLOBALS['SITE_DB']->query_select('digestives_tin', array('*'), $where, $extra, $max, 0);

        require_all_lang();

        $content = new Tempcode();
        foreach ($rows as $row) {
            $id = strval($row['id']);
            $author = is_null($row['d_from_member_id']) ? null : $GLOBALS['FORUM_DRIVER']->get_username($row['d_from_member_id']);
            if (is_null($author)) {
                $author = do_lang('UNKNOWN');
            }

            $news_date = date($date_string, $row['d_date_and_time']);
            $edit_date = escape_html('');

            $news_title = xmlentities($row['d_subject']);
            $summary = xmlentities(strip_comcode(get_translated_text($row['d_message'])));
            $news = escape_html(get_translated_text($row['d_message']));

            $ob = _get_notification_ob_for_code($row['d_notification_code']);
            if (is_null($ob)) {
                continue;
            }
            $codes = $ob->list_handled_codes();
            if (!isset($codes[$row['d_notification_code']])) {
                continue;
            }
            $category = $codes[$row['d_notification_code']][1];
            $category_raw = $row['d_notification_code'] . '/' . $row['d_code_category'];

            $view_url = build_url(array('page' => 'notifications', 'type' => 'view', 'id' => $row['id']), get_module_zone('notifications'));

            if ($prefix == 'RSS_') {
                $if_comments = do_template('RSS_ENTRY_COMMENTS', array('COMMENT_URL' => $view_url, 'ID' => strval($row['id'])), null, false, null, '.xml', 'xml');
            } else {
                $if_comments = new Tempcode();
            }

            $content->attach(do_template($prefix . 'ENTRY', array('VIEW_URL' => $view_url, 'SUMMARY' => $summary, 'EDIT_DATE' => $edit_date, 'IF_COMMENTS' => $if_comments, 'TITLE' => $news_title, 'CATEGORY_RAW' => $category_raw, 'CATEGORY' => $category, 'AUTHOR' => $author, 'ID' => $id, 'NEWS' => $news, 'DATE' => $news_date), null, false, null, '.xml', 'xml'));
        }

        return array($content, do_lang('NOTIFICATIONS'));
    }
}
