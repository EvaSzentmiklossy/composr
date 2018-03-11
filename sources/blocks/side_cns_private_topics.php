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
 * @package    cns_forum
 */

/**
 * Block class.
 */
class Block_side_cns_private_topics
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled)
     */
    public function info()
    {
        if (get_forum_type() != 'cns') {
            return null;
        }

        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = false;
        $info['parameters'] = array();
        return $info;
    }

    /**
     * Find caching details for the block.
     *
     * @return ?array Map of cache details (cache_on and ttl) (null: block is disabled)
     */
    public function caching_environment()
    {
        $info = array();
        $info['cache_on'] = 'array()';
        $info['special_cache_flags'] = CACHE_AGAINST_DEFAULT | CACHE_AGAINST_MEMBER;
        $info['ttl'] = (get_value('disable_block_timeout') === '1') ? 60 * 60 * 24 * 365 * 5/*5 year timeout*/ : 60 * 100;
        return $info;
    }

    /**
     * Execute the block.
     *
     * @param  array $map A map of parameters
     * @return Tempcode The result of execution
     */
    public function run($map)
    {
        $error_msg = new Tempcode();
        if (!addon_installed__autoinstall('cns_forum', $error_msg)) {
            return $error_msg;
        }

        if (get_forum_type() != 'cns') {
            return paragraph(do_lang_tempcode('NO_CNS'), '0g2we1y689cd9211nq5i3830v32cqsjh', 'red-alert');
        }

        if (is_guest()) {
            return new Tempcode();
        }

        cns_require_all_forum_stuff();

        require_code('cns_notifications');

        $block_id = get_block_id($map);

        // Only show what's new in week. Some forums may want to tweak this, but forums themselves only mark unread topics for a week.
        $rows = cns_get_pp_rows();
        require_lang('cns');
        $out = new Tempcode();
        foreach ($rows as $topic) {
            $topic_url = build_url(array('page' => 'topicview', 'type' => 'findpost', 'id' => $topic['id']), get_module_zone('topicview'));
            $topic_url->attach('#post_' . strval($topic['id']));
            $title = $topic['t_cache_first_title'];
            $date = get_timezoned_date_time_tempcode($topic['t_cache_last_time']);
            $num_posts = $topic['t_cache_num_posts'];

            $last_post_by_username = $topic['t_cache_last_username'];
            $last_post_by_member_url = $GLOBALS['CNS_DRIVER']->member_profile_url($topic['t_cache_last_member_id'], true);

            $with_poster_id = ($topic['t_pt_from'] == get_member()) ? $topic['t_pt_to'] : $topic['t_pt_from'];
            $with_username = $GLOBALS['FORUM_DRIVER']->get_username($with_poster_id);
            $with_member_url = $GLOBALS['CNS_DRIVER']->member_profile_url($with_poster_id, true);

            $by_poster_id = $topic['t_pt_from'];
            $by_username = $GLOBALS['FORUM_DRIVER']->get_username($by_poster_id);
            $by_member_url = $GLOBALS['CNS_DRIVER']->member_profile_url($by_poster_id, true);

            $to_poster_id = $topic['t_pt_to'];
            $to_username = $GLOBALS['FORUM_DRIVER']->get_username($to_poster_id);
            $to_member_url = $GLOBALS['CNS_DRIVER']->member_profile_url($to_poster_id, true);

            $is_unread = ($topic['t_cache_last_time'] > time() - 60 * 60 * 24 * intval(get_option('post_read_history_days'))) && (($topic['l_time'] === null) || ($topic['l_time'] < $topic['p_time']));

            $out->attach(do_template('CNS_PRIVATE_TOPIC_LINK', array(
                '_GUID' => '05beab5a3fab191df988bf101f44a47a',
                'TOPIC_URL' => $topic_url,
                'TITLE' => $title,
                'DATE' => $date,
                'DATE_RAW' => strval($topic['t_cache_last_time']),
                'LAST_POST_BY_POSTER_URL' => $last_post_by_member_url,
                'LAST_POST_BY_USERNAME' => $last_post_by_username,
                'LAST_POST_BY_POSTER_ID' => strval($topic['t_cache_last_member_id']),
                'WITH_POSTER_URL' => $with_member_url,
                'WITH_USERNAME' => $with_username,
                'WITH_POSTER_ID' => strval($with_poster_id),
                'BY_POSTER_URL' => $by_member_url,
                'BY_USERNAME' => $by_username,
                'BY_POSTER_ID' => strval($by_poster_id),
                'TO_POSTER_URL' => $to_member_url,
                'TO_USERNAME' => $to_username,
                'TO_POSTER_ID' => strval($to_poster_id),
                'NUM_POSTS' => integer_format($num_posts),
                'HAS_READ' => !$is_unread,
            )));
        }
        $send_url = build_url(array('page' => 'topics', 'type' => 'new_pt', 'redirect' => protect_url_parameter(SELF_REDIRECT_RIP)), get_module_zone('topics'));
        if (!cns_may_make_private_topic()) {
            $send_url = new Tempcode();
        }
        $view_url = build_url(array('page' => 'members', 'type' => 'view', 'id' => get_member()), get_module_zone('members'), array(), true, false, false, 'tab--pts');
        return do_template('BLOCK_SIDE_CNS_PRIVATE_TOPICS', array(
            '_GUID' => '9376cd47884a78f3d1914c176b67ee28',
            'BLOCK_ID' => $block_id,
            'SEND_URL' => $send_url,
            'VIEW_URL' => $view_url,
            'CONTENT' => $out,
            'FORUM_NAME' => do_lang_tempcode('PRIVATE_TOPICS'),
        ));
    }
}
