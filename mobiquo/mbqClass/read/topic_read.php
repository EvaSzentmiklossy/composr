<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2015

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    cns_tapatalk
 */

/**
 * Composr API helper class.
 */
class CMSTopicRead
{
    const GET_TOPICS_ALL = 0;
    const GET_TOPICS_UNREAD_ONLY = 1;
    const GET_TOPICS_PARTICIPATED_ONLY = 2;

    /**
     * Get basic details of some topics.
     *
     * @param  array $topic_ids List of topic IDs
     * @return array Details
     */
    public function get_topic_statuses($topic_ids)
    {
        cms_verify_parameters_phpdoc();

        $member_id = get_member();

        if (count($topic_ids) == 0) {
            return array();
        }

        $table_prefix = $GLOBALS['FORUM_DB']->get_table_prefix();
        $sql_ids = '';
        foreach ($topic_ids as $topic_id) {
            if ($sql_ids != '') {
                $sql_ids .= ',';
            }
            $sql_ids .= strval($topic_id);
        }
        $sql = 'SELECT *,id AS topic_id FROM ' . $table_prefix . 'f_topics WHERE id IN (' . $sql_ids . ')';
        $sql .= ' AND t_forum_id IN (' . get_allowed_forum_sql() . ')';
        if (addon_installed('unvalidated')) {
            $sql .= ' AND t_validated=1';
        }
        $_topics = ($sql_ids == '') ? array() : $GLOBALS['FORUM_DB']->query($sql);

        $topics = array();
        foreach ($_topics as $topic) {
            $topics[] = array(
                'topic_id' => $topic['topic_id'],
                'is_subscribed' => get_topic_subscription_status($topic['topic_id'], $member_id),
                'can_subscribe' => !is_guest($member_id),
                'is_closed' => ($topic['t_is_open'] == 0),
                'last_reply_time' => $topic['t_cache_last_time'],
                'new_post' => is_topic_unread($topic['topic_id'], $member_id, $topic),
                'reply_number' => $topic['t_cache_num_posts'],
                'view_number' => $topic['t_num_views'],
            );
        }
        return $topics;
    }

    /**
     * Get some topics from a forum.
     *
     * @param  string $mode Mode
     * @set TOP ANN ALL
     * @param  AUTO_LINK $forum_id Forum ID
     * @param  integer $start Start position
     * @param  integer $max Maximum results
     * @return array A tuple of details: total topics, topics, forum name, unread sticky count, unread announce count, action assessment
     */
    public function get_topics($mode, $forum_id, $start, $max)
    {
        if (!has_category_access(get_member(), 'forums', strval($forum_id))) {
            access_denied('I_ERROR');
        }

        $where_basic = array('t_forum_id' => $forum_id);
        if (addon_installed('unvalidated')) {
            $where_basic['t_validated'] = 1;
        }
        $where = $where_basic;

        switch ($mode) {
            case 'TOP': // Sticky topics
                $where['t_pinned'] = 1;
                $where['t_cascading'] = 0;
                break;

            case 'ANN': // Announcement topics
                $where['t_cascading'] = 1;
                break;

            default: // Standard topics
                $where['t_pinned'] = 0;
                $where['t_cascading'] = 0;
                break;
        }

        $table_prefix = $GLOBALS['FORUM_DB']->get_table_prefix();
        $_details = $GLOBALS['FORUM_DB']->query_select(
            'f_topics t JOIN ' . $table_prefix . 'f_forums f ON f.id=t.t_forum_id JOIN ' . $table_prefix . 'f_posts p ON t.t_cache_first_post_id=p.id',
            array('*', 'f.id as forum_id', 't.id AS topic_id', 'p.id AS post_id'),
            $where,
            'ORDER BY t_cache_last_time DESC',
            $max,
            $start
        );

        if (count($_details) > 0) {
            $forum_details = $_details[0];
        } else {
            $_forum_details = $GLOBALS['FORUM_DB']->query_select('f_forums f', array('*', 'f.id AS forum_id'), array('f.id' => $forum_id), '', 1);
            if (!isset($_forum_details[0])) {
                // Ideally we'd do a normal MISSING_RESOURCE, but tapatalk is sending some spurious requests to a forum '0' after doing moderation actions on iOS
                return array(
                    0,
                    array(),
                    do_lang('MISSING_RESOURCE'),
                    0,
                    0,
                    array(
                        'can_post' => false,
                        'can_upload' => false,
                    ),
                );
            }
            $forum_details = $_forum_details[0];
        }
        $forum_name = $forum_details['f_name'];

        $total_topic_num = $GLOBALS['FORUM_DB']->query_select_value('f_topics', 'COUNT(*)', $where, ' AND t_cache_first_member_id IS NOT NULL');

        $extra = ' AND (SELECT l_time FROM ' . $table_prefix . 'f_read_logs l WHERE l_topic_id=t.id AND l_member_id=' . strval(get_member()) . ')<t.t_cache_last_time';
        $unread_sticky_count = $GLOBALS['FORUM_DB']->query_select_value('f_topics t', 'COUNT(*)', array('t_pinned' => 1, 't_cascading' => 0) + $where_basic, $extra);
        $unread_announce_count = $GLOBALS['FORUM_DB']->query_select_value('f_topics t', 'COUNT(*)', array('t_cascading' => 1) + $where_basic, $extra);

        $action_details = action_assessment_forum($forum_details, get_member());

        return array(
            $total_topic_num,
            $_details,
            $forum_name,
            $unread_sticky_count,
            $unread_announce_count,
            $action_details
        );
    }

    /**
     * Get some topics matching some filters.
     *
     * @param  integer $start Start position
     * @param  integer $max Maximum results
     * @param  array $filters List of filter maps (supports only_in, not_in, excuded_topics)
     * @param  integer $method Method (a GET_TOPICS_* constant)
     * @param  ?mixed $method_data Data for search method (null: N/A)
     * @return array A pair: total topics, topics
     */
    public function get_topics_advanced($start, $max, $filters, $method = 0, $method_data = null)
    {
        cms_verify_parameters_phpdoc();

        $member_id = get_member();

        $table_prefix = $GLOBALS['FORUM_DB']->get_table_prefix();

        $conditions = array();

        // Implement filter
        if (!empty($filters)) {
            if (isset($filters['only_in'])) {
                $or_list = '';
                foreach ($filters['only_in'] as $_forum_id) {
                    if ($or_list != '') {
                        $or_list .= ' OR ';
                    }
                    $or_list .= strval($_forum_id);
                }
                if ($or_list != '') {
                    array_push($conditions, 't_forum_id IN (' . $or_list . ')');
                } else {
                    array_push($conditions, '0');
                }
            }

            if (isset($filters['not_in'])) {
                $or_list = '';
                foreach ($filters['not_in'] as $_forum_id) {
                    if ($or_list != '') {
                        $or_list .= ' OR ';
                    }
                    $or_list .= strval($_forum_id);
                }
                if ($or_list != '') {
                    array_push($conditions, 't_forum_id NOT IN (' . $or_list . ')');
                }
            }

            if (isset ($filters['excluded_topics'])) {
                $or_list = '';
                foreach ($filters['excluded_topics'] as $_forum_id) {
                    if ($or_list != '') {
                        $or_list .= ' OR ';
                    }
                    $or_list .= strval($_forum_id);
                }
                if ($or_list != '') {
                    array_push($conditions, 'id NOT IN (' . $or_list . ')');
                }
            }
        }

        // Enforce permissions
        if (get_allowed_forum_sql() != '') {
            array_push($conditions, 't_forum_id IN (' . get_allowed_forum_sql() . ')');
        } else {
            array_push($conditions, '0');
        }

        // No PTs
        array_push($conditions, 't_forum_id IS NOT NULL');

        // No empty topic shells
        array_push($conditions, 't_cache_first_member_id IS NOT NULL');

        // Validated-only
        if (addon_installed('unvalidated')) {
            array_push($conditions, 't_validated=1');
        }

        // Only unread ones?
        if ($method == self::GET_TOPICS_UNREAD_ONLY) {
            if (is_guest()) {
                return array(0, array());
            }

            $subquery = 'SELECT l_topic_id FROM ' . $table_prefix . 'f_read_logs WHERE ';
            $subquery .= 'l_member_id=' . strval($member_id) . ' AND ';
            $subquery .= 'l_topic_id=t.id AND ';
            $subquery .= 'l_time>=t_cache_last_time';

            array_push($conditions, 't_cache_last_time>' . strval(time() - 60 * 60 * 24 * intval(get_option('post_history_days'))) . ' AND NOT EXISTS (' . $subquery . ')');
        }

        // Only participated ones?
        if ($method == self::GET_TOPICS_PARTICIPATED_ONLY) {
            if (is_guest()) {
                return array(0, array());
            }

            $participant_id = $method_data;

            $subquery = 'SELECT id FROM ' . $table_prefix . 'f_posts WHERE ';
            $subquery .= 'p_poster=' . strval($participant_id) . ' AND ';
            $subquery .= 'p_topic_id=t.id';

            array_push($conditions, 'EXISTS (' . $subquery . ')');
        }

        // Run query
        $table_prefix = $GLOBALS['FORUM_DB']->get_table_prefix();
        $sql = $table_prefix . 'f_topics t JOIN ' . $table_prefix . 'f_posts p ON t.t_cache_first_post_id=p.id JOIN ' . $table_prefix . 'f_forums f ON f.id=t.t_forum_id WHERE ' . implode(' AND ', $conditions) . ' ORDER BY t_cache_last_time DESC,t.id DESC';
        $forum_topics = $GLOBALS['FORUM_DB']->query('SELECT *,t.id AS topic_id,p.id AS post_id,f.id AS forum_id FROM ' . $sql, $max, $start);
        $total_forum_topics = $GLOBALS['FORUM_DB']->query_value_if_there('SELECT COUNT(*) FROM ' . $sql);

        return array($total_forum_topics, $forum_topics);
    }
}
