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
 * @package    core_cns
 */

/**
 * Edit a multi moderation.
 *
 * @param  AUTO_LINK $id The ID of the multi moderation we are editing
 * @param  SHORT_TEXT $name The name of the multi moderation
 * @param  LONG_TEXT $post_text The default post text to add when applying (may be blank)
 * @param  ?AUTO_LINK $move_to The forum to move the topic when applying (null: do not move)
 * @param  ?BINARY $pin_state The pin state after applying (null: unchanged)
 * @param  ?BINARY $open_state The open state after applying (null: unchanged)
 * @param  SHORT_TEXT $forum_multi_code The forum multi code for where this multi moderation may be applied
 * @param  SHORT_TEXT $title_suffix The title suffix
 */
function cns_edit_multi_moderation($id, $name, $post_text, $move_to, $pin_state, $open_state, $forum_multi_code, $title_suffix)
{
    if (!addon_installed('cns_multi_moderations')) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    $_name = $GLOBALS['FORUM_DB']->query_select_value('f_multi_moderations', 'mm_name', array('id' => $id));

    $map = array(
        'mm_post_text' => $post_text,
        'mm_move_to' => $move_to,
        'mm_pin_state' => $pin_state,
        'mm_open_state' => $open_state,
        'mm_forum_multi_code' => $forum_multi_code,
        'mm_title_suffix' => $title_suffix,
    );
    $map += lang_remap('mm_name', $_name, $name, $GLOBALS['FORUM_DB']);
    $GLOBALS['FORUM_DB']->query_update('f_multi_moderations', $map, array('id' => $id), '', 1);

    log_it('EDIT_MULTI_MODERATION', strval($id), $name);

    if ((addon_installed('commandr')) && (!running_script('install'))) {
        require_code('resource_fs');
        generate_resource_fs_moniker('multi_moderation', strval($id));
    }
}

/**
 * Delete a multi moderation.
 *
 * @param  AUTO_LINK $id The ID of the multi moderation we are deleting
 */
function cns_delete_multi_moderation($id)
{
    if (!addon_installed('cns_multi_moderations')) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    $_name = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_multi_moderations', 'mm_name', array('id' => $id));
    if ($_name === null) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
    }
    $name = get_translated_text($_name, $GLOBALS['FORUM_DB']);
    $GLOBALS['FORUM_DB']->query_delete('f_multi_moderations', array('id' => $id), '', 1);
    delete_lang($_name, $GLOBALS['FORUM_DB']);

    log_it('DELETE_MULTI_MODERATION', strval($id), $name);

    if ((addon_installed('commandr')) && (!running_script('install'))) {
        require_code('resource_fs');
        expunge_resource_fs_moniker('multi_moderation', strval($id));
    }
}

/**
 * Perform a multi moderation.
 *
 * @param  AUTO_LINK $id The ID of the multi moderation we are performing
 * @param  AUTO_LINK $topic_id The ID of the topic we are performing the multi moderation on
 * @param  LONG_TEXT $reason The reason for performing the multi moderation (may be blank)
 * @param  LONG_TEXT $post_text The post text for a post to be added to the topic (blank: do not add a post)
 * @param  BINARY $is_emphasised Whether the post is marked emphasised
 * @param  BINARY $skip_sig Whether to skip showing the posters signature in the post
 */
function cns_perform_multi_moderation($id, $topic_id, $reason, $post_text = '', $is_emphasised = 1, $skip_sig = 0)
{
    if (!addon_installed('cns_multi_moderations')) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    $topic_details = $GLOBALS['FORUM_DB']->query_select('f_topics', array('t_forum_id', 't_cache_first_title', 't_cache_first_post_id'), array('id' => $topic_id), '', 1);
    if (!array_key_exists(0, $topic_details)) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'multi_moderation'));
    }
    $from = $topic_details[0]['t_forum_id'];
    if (!cns_may_perform_multi_moderation($from)) {
        access_denied('I_ERROR');
    }

    $mm = $GLOBALS['FORUM_DB']->query_select('f_multi_moderations', array('*'), array('id' => $id), '', 1);
    if (!array_key_exists(0, $mm)) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'multi_moderation'));
    }

    require_code('selectcode');
    $idlist = selectcode_to_idlist_using_db($mm[0]['mm_forum_multi_code'], 'id', 'f_forums', 'f_forums', 'f_parent_forum', 'f_parent_forum', 'id', true, true, $GLOBALS['FORUM_DB']);
    if (!in_array($from, $idlist)) {
        warn_exit(do_lang_tempcode('MM_APPLY_TWICE'));
    }

    $pin_state = $mm[0]['mm_pin_state'];
    $open_state = $mm[0]['mm_open_state'];
    $move_to = $mm[0]['mm_move_to'];
    $title_suffix = $mm[0]['mm_title_suffix'];
    //$post_text = $mm[0]['mm_post_text']; We'll allow user to specify the post_text, with this as a default
    $update_array = array();
    if ($pin_state !== null) {
        $update_array['t_pinned'] = $pin_state;
    }
    if ($open_state !== null) {
        $update_array['t_is_open'] = $open_state;
    }
    if ($title_suffix != '') {
        $new_title = $topic_details[0]['t_cache_first_title'] . ' [' . $title_suffix . ']';
        $update_array['t_cache_first_title'] = $new_title;
        $GLOBALS['FORUM_DB']->query_update('f_posts', array('p_title' => $new_title), array('id' => $topic_details[0]['t_cache_first_post_id']), '', 1);
    }

    if (count($update_array) != 0) {
        $GLOBALS['FORUM_DB']->query_update('f_topics', $update_array, array('id' => $topic_id), '', 1);
    }

    if ($move_to !== null) {
        require_code('cns_topics_action');
        require_code('cns_topics_action2');
        cns_move_topics($from, $move_to, array($topic_id));
    }

    if ($post_text != '') {
        require_code('cns_posts_action');
        require_code('cns_posts_action2');
        require_code('cns_topics_action');
        require_code('cns_topics_action2');
        cns_make_post($topic_id, '', $post_text, $skip_sig, false, 1, $is_emphasised);

        $forum_id = ($move_to === null) ? $from : $move_to;
        handle_topic_ticket_reply($forum_id, $topic_id, $topic_details[0]['t_cache_first_title'], $post_text);
    }

    require_lang('cns_multi_moderations');
    require_code('cns_general_action2');
    cns_mod_log_it('PERFORM_MULTI_MODERATION', strval($id), strval($topic_id), $reason);
}

/**
 * Script for loading presets from saved warnings.
 */
function warnings_script()
{
    if (!addon_installed('cns_warnings')) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    if (get_forum_type() != 'cns') {
        warn_exit(do_lang_tempcode('NO_CNS'));
    } else {
        cns_require_all_forum_stuff();
    }

    require_lang('cns_warnings');

    if (!cns_may_warn_members()) {
        access_denied('PRIVILEGE', 'warn_members');
    }

    $type = get_param_string('type');

    if ($type == 'delete') { // Delete a saved warning
        $_title = post_param_string('title');
        $GLOBALS['FORUM_DB']->query_delete('f_saved_warnings', array('s_title' => $_title), '', 1);
        $content = paragraph(do_lang_tempcode('SUCCESS'));
        $echo = do_template('STANDALONE_HTML_WRAP', array('_GUID' => 'dc97492788a5049e697a296ca10a0390', 'TITLE' => do_lang_tempcode('DELETE_SAVED_WARNING'), 'POPUP' => true, 'CONTENT' => $content));
        $echo->evaluate_echo();
        return;
    }

    // Show list of saved warnings
    // ---------------------------

    $content = new Tempcode();
    $rows = $GLOBALS['FORUM_DB']->query_select('f_saved_warnings', array('*'), array(), 'ORDER BY s_title');
    $keep = symbol_tempcode('KEEP');
    $url = find_script('warnings_browse') . '?type=delete' . $keep->evaluate();
    foreach ($rows as $myrow) {
        $delete_link = hyperlink($url, do_lang_tempcode('DELETE'), false, false, '', null, form_input_hidden('title', $myrow['s_title']));
        $content->attach(do_template('CNS_SAVED_WARNING', array(
            '_GUID' => '537a5e28bfdc3f2d2cb6c06b0a939b51',
            'MESSAGE' => $myrow['s_message'],
            'MESSAGE_HTML' => comcode_to_tempcode($myrow['s_message'], $GLOBALS['FORUM_DRIVER']->get_guest_id()),
            'EXPLANATION' => $myrow['s_explanation'],
            'TITLE' => $myrow['s_title'],
            'DELETE_LINK' => $delete_link,
        )));
    }
    if ($content->is_empty()) {
        $content = paragraph(do_lang_tempcode('NO_ENTRIES'), 'rfdsfsdf3t45', 'nothing-here');
    }

    $echo = do_template('STANDALONE_HTML_WRAP', array('_GUID' => '90c86490760cee23a8d5b8a5d14122e9', 'TITLE' => do_lang_tempcode('CHOOSE_SAVED_WARNING'), 'POPUP' => true, 'CONTENT' => $content));
    $echo->evaluate_echo();
}

/**
 * Add a formal warning.
 *
 * @param  MEMBER $member_id The member being warned
 * @param  LONG_TEXT $explanation An explanation for why the member is being warned
 * @param  ?MEMBER $by The member doing the warning (null: current member)
 * @param  ?TIME $time The time of the warning (null: now)
 * @param  BINARY $is_warning Whether this counts as a warning
 * @param  ?AUTO_LINK $silence_from_topic The topic being silenced from (null: none)
 * @param  ?AUTO_LINK $silence_from_forum The forum being silenced from (null: none)
 * @param  integer $probation Number of extra days for probation
 * @param  IP $banned_ip The IP address being banned (blank: none)
 * @param  integer $charged_points The points being charged
 * @param  BINARY $banned_member Whether the member is being banned
 * @param  ?GROUP $changed_usergroup_from The usergroup being changed from (null: no change)
 * @return AUTO_LINK The ID of the newly created warning
 */
function cns_make_warning($member_id, $explanation, $by = null, $time = null, $is_warning = 1, $silence_from_topic = null, $silence_from_forum = null, $probation = 0, $banned_ip = '', $charged_points = 0, $banned_member = 0, $changed_usergroup_from = null)
{
    if (!addon_installed('cns_warnings')) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    if (($time === null) && (!cns_may_warn_members())) {
        access_denied('PRIVILEGE', 'warn_members');
    }

    if ($time === null) {
        $time = time();
    }
    if ($by === null) {
        $by = get_member();
    }

    if ($is_warning == 1) {
        $GLOBALS['FORUM_DB']->query('UPDATE ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_members SET m_cache_warnings=(m_cache_warnings+1) WHERE id=' . strval($member_id), 1);
    }

    return $GLOBALS['FORUM_DB']->query_insert('f_warnings', array(
        'w_member_id' => $member_id,
        'w_time' => $time,
        'w_explanation' => $explanation,
        'w_by' => $by,
        'w_is_warning' => $is_warning,
        'p_silence_from_topic' => $silence_from_topic,
        'p_silence_from_forum' => $silence_from_forum,
        'p_probation' => $probation,
        'p_banned_ip' => $banned_ip,
        'p_charged_points' => $charged_points,
        'p_banned_member' => $banned_member,
        'p_changed_usergroup_from' => $changed_usergroup_from,
    ), true);
}

/**
 * Edit a formal warning.
 *
 * @param  AUTO_LINK $warning_id The ID of the formal warning we are editing
 * @param  LONG_TEXT $explanation An explanation for why the member is being warned
 * @param  BINARY $is_warning Whether this counts as a warning
 * @return AUTO_LINK The member ID the warning was for
 */
function cns_edit_warning($warning_id, $explanation, $is_warning = 1)
{
    if (!addon_installed('cns_warnings')) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    if (!cns_may_warn_members()) {
        access_denied('PRIVILEGE', 'warn_members');
    }

    $member_id = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_warnings', 'w_member_id', array('id' => $warning_id));
    if ($member_id === null) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
    }

    $GLOBALS['FORUM_DB']->query_update('f_warnings', array('w_explanation' => $explanation, 'w_is_warning' => $is_warning), array('id' => $warning_id), '', 1);

    $member_id = $GLOBALS['FORUM_DB']->query_select_value('f_warnings', 'w_member_id', array('id' => $warning_id));
    $num_warnings = $GLOBALS['FORUM_DB']->query_select_value('f_warnings', 'COUNT(*)', array('w_is_warning' => 1, 'w_member_id' => $member_id));

    $GLOBALS['FORUM_DB']->query_update('f_members', array('m_cache_warnings' => $num_warnings), array('id' => $member_id), '', 1);

    return $member_id;
}

/**
 * Delete a formal warning.
 *
 * @param  AUTO_LINK $warning_id The ID of the formal warning we are deleting
 * @return AUTO_LINK The member ID the warning was for
 */
function cns_delete_warning($warning_id)
{
    if (!addon_installed('cns_warnings')) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    if (!cns_may_warn_members()) {
        access_denied('PRIVILEGE', 'warn_members');
    }

    $member_id = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_warnings', 'w_member_id', array('id' => $warning_id));
    if ($member_id === null) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
    }

    $GLOBALS['FORUM_DB']->query_delete('f_warnings', array('id' => $warning_id), '', 1);

    $num_warnings = $GLOBALS['FORUM_DB']->query_select_value('f_warnings', 'COUNT(*)', array('w_is_warning' => 1, 'w_member_id' => $member_id));
    $GLOBALS['FORUM_DB']->query_update('f_members', array('m_cache_warnings' => $num_warnings), array('id' => $member_id), '', 1);

    return $member_id;
}
