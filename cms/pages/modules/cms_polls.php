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
 * @package    polls
 */

require_code('crud_module');

/**
 * Module page class.
 */
class Module_cms_polls extends Standard_crud_module
{
    public $lang_type = 'POLL';
    public $archive_entry_point = '_SEARCH:polls:browse';
    public $view_entry_point = '_SEARCH:polls:view:_ID';
    public $user_facing = true;
    public $send_validation_request = false;
    public $permissions_require = 'mid';
    public $select_name = 'QUESTION';
    public $select_name_description = 'DESCRIPTION_QUESTION';
    public $menu_label = 'POLLS';
    public $table = 'poll';
    public $title_is_multi_lang = true;
    public $content_type = 'poll';
    public $donext_entry_content_type = 'poll';
    public $donext_category_content_type = null;

    /**
     * Find entry-points available within this module.
     *
     * @param  boolean $check_perms Whether to check permissions.
     * @param  ?MEMBER $member_id The member to check permissions as (null: current user).
     * @param  boolean $support_crosslinks Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name).
     * @param  boolean $be_deferential Whether to avoid any entry-point (or even return null to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "browse" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
     * @return ?array A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled).
     */
    public function get_entry_points($check_perms = true, $member_id = null, $support_crosslinks = true, $be_deferential = false)
    {
        $ret = array(
                   'browse' => array('MANAGE_POLLS', 'menu/social/polls'),
               ) + parent::get_entry_points();

        if ($support_crosslinks) {
            require_code('fields');
            $ret += manage_custom_fields_entry_points('poll');
        }

        return $ret;
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @param  boolean $top_level Whether this is running at the top level, prior to having sub-objects called.
     * @param  ?ID_TEXT $type The screen type to consider for metadata purposes (null: read from environment).
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none).
     */
    public function pre_run($top_level = true, $type = null)
    {
        $type = get_param_string('type', 'browse');

        require_lang('polls');

        set_helper_panel_tutorial('tut_feedback');

        return parent::pre_run($top_level);
    }

    /**
     * Standard crud_module run_start.
     *
     * @param  ID_TEXT $type The type of module execution
     * @return Tempcode The output of the run
     */
    public function run_start($type)
    {
        require_code('polls');
        require_code('polls2');
        require_css('polls');

        $this->add_one_label = do_lang_tempcode('ADD_POLL');
        $this->edit_this_label = do_lang_tempcode('EDIT_THIS_POLL');
        $this->edit_one_label = do_lang_tempcode('EDIT_POLL');

        if ($type == 'browse') {
            return $this->browse();
        }

        return new Tempcode();
    }

    /**
     * Find privileges defined as overridable by this module.
     *
     * @return array A map of privileges that are overridable; privilege to 0 or 1. 0 means "not category overridable". 1 means "category overridable".
     */
    public function get_privilege_overrides()
    {
        require_lang('polls');
        return array('submit_midrange_content' => array(0, 'ADD_POLL'), 'bypass_validation_midrange_content' => array(0, 'BYPASS_VALIDATION_POLL'), 'edit_own_midrange_content' => array(0, 'EDIT_OWN_POLL'), 'edit_midrange_content' => array(0, 'EDIT_POLL'), 'delete_own_midrange_content' => array(0, 'DELETE_OWN_POLL'), 'delete_midrange_content' => array(0, 'DELETE_POLL'), 'edit_own_highrange_content' => array(0, 'EDIT_OWN_LIVE_POLL'), 'edit_highrange_content' => array(0, 'EDIT_LIVE_POLL'), 'delete_own_highrange_content' => array(0, 'DELETE_OWN_LIVE_POLL'), 'delete_highrange_content' => array(0, 'DELETE_LIVE_POLL'), 'vote_in_polls' => 0);
    }

    /**
     * The do-next manager for before content management.
     *
     * @return Tempcode The UI
     */
    public function browse()
    {
        require_code('templates_donext');
        require_code('fields');
        return do_next_manager(get_screen_title('MANAGE_POLLS'), comcode_lang_string('DOC_POLLS'),
            array_merge(array(
                has_privilege(get_member(), 'submit_midrange_content', 'cms_polls') ? array('menu/_generic_admin/add_one', array('_SELF', array('type' => 'add'), '_SELF'), do_lang('ADD_POLL')) : null,
                has_privilege(get_member(), 'edit_own_midrange_content', 'cms_polls') ? array('menu/_generic_admin/edit_one', array('_SELF', array('type' => 'edit'), '_SELF'), do_lang('EDIT_OR_CHOOSE_POLL')) : null,
            ), manage_custom_fields_donext_link('poll')),
            do_lang('MANAGE_POLLS')
        );
    }

    /**
     * Standard crud_module table function.
     *
     * @param  array $url_map Details to go to build_url for link to the next screen.
     * @return array A quartet: The choose table, Whether re-ordering is supported from this screen, Search URL, Archive URL.
     */
    public function create_selection_list_choose_table($url_map)
    {
        require_code('templates_results_table');

        $default_order = 'is_current DESC,add_time DESC';
        $current_ordering = get_param_string('sort', $default_order);
        if ($current_ordering == 'is_current DESC,add_time DESC') {
            list($sortable, $sort_order) = array('is_current DESC,add_time', 'DESC');
        } elseif (($current_ordering == 'is_current ASC,add_time ASC') || ($current_ordering == 'is_current DESC,add_time ASC')) {
            list($sortable, $sort_order) = array('is_current ASC,add_time', 'ASC');
        } else {
            if (strpos($current_ordering, ' ') === false) {
                warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
            }
            list($sortable, $sort_order) = explode(' ', $current_ordering, 2);
        }
        $sortables = array(
            'question' => do_lang_tempcode('QUESTION'),
            'add_time' => do_lang_tempcode('ADDED'),
            'is_current DESC,add_time' => do_lang_tempcode('CURRENT'),
            'submitter' => do_lang_tempcode('metadata:OWNER'),
            'poll_views' => do_lang_tempcode('COUNT_VIEWS'),
            'votes1+votes2+votes3+votes4+votes5+votes6+votes7+votes8+votes9+votes10' => do_lang_tempcode('COUNT_TOTAL'),
        );
        if (((strtoupper($sort_order) != 'ASC') && (strtoupper($sort_order) != 'DESC')) || (!array_key_exists($sortable, $sortables))) {
            log_hack_attack_and_exit('ORDERBY_HACK');
        }

        $header_row = results_field_title(array(
            do_lang_tempcode('QUESTION'),
            do_lang_tempcode('ADDED'),
            do_lang_tempcode('CURRENT'),
            do_lang_tempcode('USED_PREVIOUSLY'),
            do_lang_tempcode('metadata:OWNER'),
            do_lang_tempcode('COUNT_VIEWS'),
            do_lang_tempcode('COUNT_TOTAL'),
            do_lang_tempcode('ACTIONS'),
        ), $sortables, 'sort', $sortable . ' ' . $sort_order);

        $fields = new Tempcode();

        $only_owned = has_privilege(get_member(), 'edit_midrange_content', 'cms_polls') ? null : get_member();
        list($rows, $max_rows) = $this->get_entry_rows(false, $current_ordering, (is_null($only_owned) ? array() : array('submitter' => $only_owned)));
        require_code('form_templates');
        foreach ($rows as $row) {
            $edit_link = build_url($url_map + array('id' => $row['id']), '_SELF');

            $username = protect_from_escaping($GLOBALS['FORUM_DRIVER']->member_profile_hyperlink($row['submitter']));

            $total_votes = $row['votes1'] + $row['votes2'] + $row['votes3'] + $row['votes4'] + $row['votes5'] + $row['votes6'] + $row['votes7'] + $row['votes8'] + $row['votes9'] + $row['votes10'];
            $used = ($total_votes != 0);
            $current = ($row['is_current'] == 1);

            $fields->attach(results_entry(array(
                protect_from_escaping(hyperlink(build_url(array('page' => 'polls', 'type' => 'view', 'id' => $row['id']), get_module_zone('polls')), get_translated_text($row['question']), false, true)),
                get_timezoned_date($row['add_time']),
                $current ? do_lang_tempcode('YES') : do_lang_tempcode('NO'),
                ($used || $current) ? do_lang_tempcode('YES') : do_lang_tempcode('NO'),
                $username,
                integer_format($row['poll_views']),
                do_lang_tempcode('VOTES', escape_html(integer_format($total_votes))),
                protect_from_escaping(hyperlink($edit_link, do_lang_tempcode('EDIT'), false, true, do_lang('EDIT') . ' #' . strval($row['id'])))
            ), true));
        }

        $search_url = build_url(array('page' => 'search', 'id' => 'polls'), get_module_zone('search'));
        $archive_url = build_url(array('page' => 'polls'), get_module_zone('polls'));

        return array(results_table(do_lang($this->menu_label), get_param_integer('start', 0), 'start', either_param_integer('max', 20), 'max', $max_rows, $header_row, $fields, $sortables, $sortable, $sort_order), false, $search_url, $archive_url);
    }

    /**
     * Standard crud_module list function.
     *
     * @return Tempcode The selection list
     */
    public function create_selection_list_entries()
    {
        $only_owned = has_privilege(get_member(), 'edit_midrange_content', 'cms_polls') ? null : get_member();
        $poll_list = create_selection_list_polls(null, $only_owned);
        return $poll_list;
    }

    /**
     * Get Tempcode for a poll adding/editing form.
     *
     * @param  ?AUTO_LINK $id The poll ID (null: new)
     * @param  SHORT_TEXT $question The question
     * @param  SHORT_TEXT $a1 The first answer
     * @param  SHORT_TEXT $a2 The second answer
     * @param  SHORT_TEXT $a3 The third answer
     * @param  SHORT_TEXT $a4 The fourth answer
     * @param  SHORT_TEXT $a5 The fifth answer
     * @param  SHORT_TEXT $a6 The sixth answer
     * @param  SHORT_TEXT $a7 The seventh answer
     * @param  SHORT_TEXT $a8 The eigth answer
     * @param  SHORT_TEXT $a9 The ninth answer
     * @param  SHORT_TEXT $a10 The tenth answer
     * @param  boolean $current Whether the poll is/will-be currently active
     * @param  ?BINARY $allow_rating Whether rating is allowed (null: decide statistically, based on existing choices)
     * @param  ?SHORT_INTEGER $allow_comments Whether comments are allowed (0=no, 1=yes, 2=review style) (null: decide statistically, based on existing choices)
     * @param  ?BINARY $allow_trackbacks Whether trackbacks are allowed (null: decide statistically, based on existing choices)
     * @param  LONG_TEXT $notes Notes for the poll
     * @return array A pair: The input fields, Hidden fields
     */
    public function get_form_fields($id = null, $question = '', $a1 = '', $a2 = '', $a3 = '', $a4 = '', $a5 = '', $a6 = '', $a7 = '', $a8 = '', $a9 = '', $a10 = '', $current = true, $allow_rating = 1, $allow_comments = 1, $allow_trackbacks = 1, $notes = '')
    {
        list($allow_rating, $allow_comments, $allow_trackbacks) = $this->choose_feedback_fields_statistically($allow_rating, $allow_comments, $allow_trackbacks);

        $fields = new Tempcode();
        require_code('form_templates');
        $fields->attach(form_input_line_comcode(do_lang_tempcode('QUESTION'), do_lang_tempcode('DESCRIPTION_QUESTION'), 'question', $question, true));
        $fields->attach(form_input_line_comcode(do_lang_tempcode('ANSWER_X', escape_html(integer_format(1))), do_lang_tempcode('DESCRIPTION_ANSWER'), 'option1', $a1, true));
        $fields->attach(form_input_line_comcode(do_lang_tempcode('ANSWER_X', escape_html(integer_format(2))), do_lang_tempcode('DESCRIPTION_ANSWER'), 'option2', $a2, true));
        $fields->attach(form_input_line_comcode(do_lang_tempcode('ANSWER_X', escape_html(integer_format(3))), do_lang_tempcode('DESCRIPTION_ANSWER'), 'option3', $a3, false));
        $fields->attach(form_input_line_comcode(do_lang_tempcode('ANSWER_X', escape_html(integer_format(4))), do_lang_tempcode('DESCRIPTION_ANSWER'), 'option4', $a4, false));
        $fields->attach(form_input_line_comcode(do_lang_tempcode('ANSWER_X', escape_html(integer_format(5))), do_lang_tempcode('DESCRIPTION_ANSWER'), 'option5', $a5, false));
        $fields->attach(form_input_line_comcode(do_lang_tempcode('ANSWER_X', escape_html(integer_format(6))), do_lang_tempcode('DESCRIPTION_ANSWER'), 'option6', $a6, false));
        $fields->attach(form_input_line_comcode(do_lang_tempcode('ANSWER_X', escape_html(integer_format(7))), do_lang_tempcode('DESCRIPTION_ANSWER'), 'option7', $a7, false));
        $fields->attach(form_input_line_comcode(do_lang_tempcode('ANSWER_X', escape_html(integer_format(8))), do_lang_tempcode('DESCRIPTION_ANSWER'), 'option8', $a8, false));
        $fields->attach(form_input_line_comcode(do_lang_tempcode('ANSWER_X', escape_html(integer_format(9))), do_lang_tempcode('DESCRIPTION_ANSWER'), 'option9', $a9, false));
        $fields->attach(form_input_line_comcode(do_lang_tempcode('ANSWER_X', escape_html(integer_format(10))), do_lang_tempcode('DESCRIPTION_ANSWER'), 'option10', $a10, false));
        if (has_privilege(get_member(), 'choose_poll')) {
            if ($question == '') {
                $test = $GLOBALS['SITE_DB']->query_select_value_if_there('poll', 'is_current', array('is_current' => 1));
                if (is_null($test)) {
                    $current = true;
                }
            }
            $fields->attach(form_input_tick(do_lang_tempcode('IMMEDIATE_USE'), do_lang_tempcode(($question == '') ? 'DESCRIPTION_IMMEDIATE_USE_ADD' : 'DESCRIPTION_IMMEDIATE_USE'), 'validated', $current));
        }

        // Metadata
        require_code('feedback2');
        $feedback_fields = feedback_fields($this->content_type, $allow_rating == 1, $allow_comments == 1, $allow_trackbacks == 1, false, $notes, $allow_comments == 2, false, true, false);
        $fields->attach(metadata_get_fields('poll', is_null($id) ? null : strval($id), false, null, ($feedback_fields->is_empty()) ? METADATA_HEADER_YES : METADATA_HEADER_FORCE));
        $fields->attach($feedback_fields);

        if (addon_installed('content_reviews')) {
            $fields->attach(content_review_get_fields('poll', is_null($id) ? null : strval($id)));
        }

        return array($fields, new Tempcode());
    }

    /**
     * Standard crud_module submitter getter.
     *
     * @param  ID_TEXT $id The entry for which the submitter is sought
     * @return array The submitter, and the time of submission (null submission time implies no known submission time)
     */
    public function get_submitter($id)
    {
        $rows = $GLOBALS['SITE_DB']->query_select('poll', array('submitter', 'date_and_time'), array('id' => intval($id)), '', 1);
        if (!array_key_exists(0, $rows)) {
            return array(null, null);
        }
        return array(intval($id), $rows[0]['submitter'], $rows[0]['date_and_time']);
    }

    /**
     * Standard crud_module edit form filler.
     *
     * @param  ID_TEXT $id The entry being edited
     * @return array A quartet: fields, hidden, delete-fields, text
     */
    public function fill_in_edit_form($id)
    {
        $rows = $GLOBALS['SITE_DB']->query_select('poll', array('*'), array('id' => intval($id)));
        if (!array_key_exists(0, $rows)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'poll'));
        }
        $myrow = $rows[0];

        return $this->get_form_fields(null, get_translated_text($myrow['question']), get_translated_text($myrow['option1']), get_translated_text($myrow['option2']), get_translated_text($myrow['option3']), get_translated_text($myrow['option4']), get_translated_text($myrow['option5']), get_translated_text($myrow['option6']), get_translated_text($myrow['option7']), get_translated_text($myrow['option8']), get_translated_text($myrow['option9']), get_translated_text($myrow['option10']), $myrow['is_current'], $myrow['allow_rating'], $myrow['allow_comments'], $myrow['allow_trackbacks'], $myrow['notes']);
    }

    /**
     * Standard crud_module add actualiser.
     *
     * @return ID_TEXT The entry added
     */
    public function add_actualisation()
    {
        $question = post_param_string('question');
        $option1 = post_param_string('option1');
        $option2 = post_param_string('option2');
        $option3 = post_param_string('option3');
        $option4 = post_param_string('option4');
        $option5 = post_param_string('option5');
        $option6 = post_param_string('option6');
        $option7 = post_param_string('option7');
        $option8 = post_param_string('option8');
        $option9 = post_param_string('option9');
        $option10 = post_param_string('option10');
        $allow_rating = post_param_integer('allow_rating', 0);
        $allow_comments = post_param_integer('allow_comments', 0);
        $allow_trackbacks = post_param_integer('allow_trackbacks', 0);
        $notes = post_param_string('notes', '');
        $num_options = 10;
        if ($option10 == '') {
            $num_options = 9;
        }
        if ($option9 == '') {
            $num_options = 8;
        }
        if ($option8 == '') {
            $num_options = 7;
        }
        if ($option7 == '') {
            $num_options = 6;
        }
        if ($option6 == '') {
            $num_options = 5;
        }
        if ($option5 == '') {
            $num_options = 4;
        }
        if ($option4 == '') {
            $num_options = 3;
        }
        if ($option3 == '') {
            $num_options = 2;
        }
        if ($option2 == '') {
            $num_options = 1;
        }

        $metadata = actual_metadata_get_fields('poll', null);

        $id = add_poll($question, $option1, $option2, $option3, $option4, $option5, $option6, $option7, $option8, $option9, $option10, $num_options, post_param_integer('validated', 0), $allow_rating, $allow_comments, $allow_trackbacks, $notes, $metadata['add_time'], $metadata['submitter'], null, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, $metadata['views'], $metadata['edit_time']);

        set_url_moniker('poll', strval($id));

        $current = post_param_integer('validated', 0);
        if ($current == 1) {
            if (!has_privilege(get_member(), 'choose_poll')) {
                log_hack_attack_and_exit('BYPASS_VALIDATION_HACK');
            }
            set_poll($id);
        }

        if ($current == 1) {
            if (has_actual_page_access(get_modal_user(), 'polls')) {
                require_code('activities');
                syndicate_described_activity('polls:ACTIVITY_ADD_POLL', $question, '', '', '_SEARCH:polls:view:' . strval($id), '', '', 'polls');
            }
        }

        if (addon_installed('content_reviews')) {
            content_review_set('poll', strval($id));
        }

        return strval($id);
    }

    /**
     * Standard crud_module edit actualiser.
     *
     * @param  ID_TEXT $id The entry being edited
     */
    public function edit_actualisation($id)
    {
        $rows = $GLOBALS['SITE_DB']->query_select('poll', array('is_current', 'submitter', 'num_options'), array('id' => intval($id)), '', 1);
        if (!array_key_exists(0, $rows)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'poll'));
        }
        $is_current = $rows[0]['is_current'];
        $submitter = $rows[0]['submitter'];

        check_edit_permission(($is_current == 1) ? 'high' : 'mid', $submitter);

        $question = post_param_string('question', STRING_MAGIC_NULL);
        $option1 = post_param_string('option1', STRING_MAGIC_NULL);
        $option2 = post_param_string('option2', STRING_MAGIC_NULL);
        $option3 = post_param_string('option3', STRING_MAGIC_NULL);
        $option4 = post_param_string('option4', STRING_MAGIC_NULL);
        $option5 = post_param_string('option5', STRING_MAGIC_NULL);
        $option6 = post_param_string('option6', STRING_MAGIC_NULL);
        $option7 = post_param_string('option7', STRING_MAGIC_NULL);
        $option8 = post_param_string('option8', STRING_MAGIC_NULL);
        $option9 = post_param_string('option9', STRING_MAGIC_NULL);
        $option10 = post_param_string('option10', STRING_MAGIC_NULL);
        $allow_rating = post_param_integer('allow_rating', fractional_edit() ? INTEGER_MAGIC_NULL : 0);
        $allow_comments = post_param_integer('allow_comments', fractional_edit() ? INTEGER_MAGIC_NULL : 0);
        $allow_trackbacks = post_param_integer('allow_trackbacks', fractional_edit() ? INTEGER_MAGIC_NULL : 0);
        $notes = post_param_string('notes', STRING_MAGIC_NULL);
        if (fractional_edit()) {
            $num_options = $rows[0]['num_options'];
        } else {
            $num_options = 10;
            if ($option10 == '') {
                $num_options = 9;
            }
            if ($option9 == '') {
                $num_options = 8;
            }
            if ($option8 == '') {
                $num_options = 7;
            }
            if ($option7 == '') {
                $num_options = 6;
            }
            if ($option6 == '') {
                $num_options = 5;
            }
            if ($option5 == '') {
                $num_options = 4;
            }
            if ($option4 == '') {
                $num_options = 3;
            }
            if ($option3 == '') {
                $num_options = 2;
            }
            if ($option2 == '') {
                $num_options = 1;
            }
        }

        $current = post_param_integer('validated', 0);

        if (($current == 1) && ($GLOBALS['SITE_DB']->query_select_value('poll', 'is_current', array('id' => $id)) == 0)) { // Just became validated, syndicate as just added
            $submitter = $GLOBALS['SITE_DB']->query_select_value('poll', 'submitter', array('id' => $id));

            if (has_actual_page_access(get_modal_user(), 'polls')) {
                require_code('activities');
                syndicate_described_activity('polls:ACTIVITY_ADD_POLL', $question, '', '', '_SEARCH:polls:view:' . strval($id), '', '', 'polls', 1, null/*$submitter*/);
            }
        }

        $metadata = actual_metadata_get_fields('poll', $id);

        edit_poll(intval($id), $question, $option1, $option2, $option3, $option4, $option5, $option6, $option7, $option8, $option9, $option10, $num_options, $allow_rating, $allow_comments, $allow_trackbacks, $notes, $metadata['edit_time'], $metadata['add_time'], $metadata['views'], $metadata['submitter'], true);

        if (!fractional_edit()) {
            if ($current == 1) {
                if ($is_current == 0) {
                    if (!has_privilege(get_member(), 'choose_poll')) {
                        log_hack_attack_and_exit('BYPASS_VALIDATION_HACK');
                    }

                    set_poll(intval($id));
                }
            }
        }

        if (addon_installed('content_reviews')) {
            content_review_set('poll', strval($id));
        }
    }

    /**
     * Standard crud_module delete actualiser.
     *
     * @param  ID_TEXT $id The entry being deleted
     */
    public function delete_actualisation($id)
    {
        $rows = $GLOBALS['SITE_DB']->query_select('poll', array('is_current', 'submitter'), array('id' => intval($id)), '', 1);
        if (!array_key_exists(0, $rows)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'poll'));
        }
        $is_current = $rows[0]['is_current'];
        $submitter = $rows[0]['submitter'];

        check_delete_permission(($is_current == 1) ? 'high' : 'mid', $submitter);

        delete_poll(intval($id));
    }
}
