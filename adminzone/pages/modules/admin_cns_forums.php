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

require_code('crud_module');
require_javascript('cns_forum');

/**
 * Module page class.
 */
class Module_admin_cns_forums extends Standard_crud_module
{
    protected $lang_type = 'FORUM';
    protected $select_name = 'NAME';
    protected $protect_first = 1;
    protected $archive_entry_point = '_SEARCH:forumview';
    protected $archive_label = 'SECTION_FORUMS';
    protected $view_entry_point = '_SEARCH:forumview:id=_ID';
    protected $special_edit_frontend = true;
    protected $privilege_page = 'topics';
    protected $permission_module = 'forums';
    protected $content_type = 'forum';
    protected $functions = 'moduleAdminCnsForums';
    protected $menu_label = 'SECTION_FORUMS';
    protected $do_preview = null;
    protected $donext_entry_content_type = 'forum';
    protected $donext_category_content_type = null;

    /**
     * Find entry-points available within this module.
     *
     * @param  boolean $check_perms Whether to check permissions
     * @param  ?MEMBER $member_id The member to check permissions as (null: current user)
     * @param  boolean $support_crosslinks Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name)
     * @param  boolean $be_deferential Whether to avoid any entry-point (or even return null to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "browse" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
     * @return ?array A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled)
     */
    public function get_entry_points($check_perms = true, $member_id = null, $support_crosslinks = true, $be_deferential = false)
    {
        if (!addon_installed('cns_forum')) {
            return null;
        }

        if (get_forum_type() != 'cns') {
            return null;
        }

        $ret = array(
            'browse' => array('MANAGE_FORUMS', 'menu/social/forum/forums'),
        ) + parent::get_entry_points();

        if ($support_crosslinks) {
            $ret['_SEARCH:admin_cns_forum_groupings:add'] = array('ADD_FORUM_GROUPING', 'admin/add_one_category');
            $ret['_SEARCH:admin_cns_forum_groupings:edit'] = array(do_lang_tempcode('menus:ITEMS_HERE', do_lang_tempcode('EDIT_FORUM_GROUPING'), make_string_tempcode(escape_html(integer_format($GLOBALS['FORUM_DB']->query_select_value('f_forum_groupings', 'COUNT(*)'))))), 'admin/edit_one_category');
            if (addon_installed('cns_post_templates')) {
                require_lang('cns_post_templates');
                $ret['_SEARCH:admin_cns_post_templates:browse'] = array(do_lang_tempcode('menus:ITEMS_HERE', do_lang_tempcode('POST_TEMPLATES'), make_string_tempcode(escape_html(integer_format($GLOBALS['FORUM_DB']->query_select_value('f_post_templates', 'COUNT(*)'))))), 'menu/adminzone/structure/forum/post_templates');
            }
            if (addon_installed('cns_multi_moderations')) {
                require_lang('cns_multi_moderations');
                $ret['_SEARCH:admin_cns_multi_moderations:browse'] = array(do_lang_tempcode('menus:ITEMS_HERE', do_lang_tempcode('MULTI_MODERATIONS'), make_string_tempcode(escape_html(integer_format($GLOBALS['FORUM_DB']->query_select_value('f_multi_moderations', 'COUNT(*)'))))), 'menu/adminzone/structure/forum/multi_moderations');
            }

            require_code('fields');
            $ret += manage_custom_fields_entry_points('post') + manage_custom_fields_entry_points('topic') + manage_custom_fields_entry_points('forum');
        }

        return $ret;
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @param  boolean $top_level Whether this is running at the top level, prior to having sub-objects called
     * @param  ?ID_TEXT $type The screen type to consider for metadata purposes (null: read from environment)
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none)
     */
    public function pre_run($top_level = true, $type = null)
    {
        $error_msg = new Tempcode();
        if (!addon_installed__autoinstall('cns_forum', $error_msg)) {
            return $error_msg;
        }

        $type = get_param_string('type', 'browse');

        require_lang('cns');
        require_css('cns_admin');

        inform_non_canonical_parameter('parent_forum');
        inform_non_canonical_parameter('forum_grouping_id');

        set_helper_panel_tutorial('tut_forums');

        if ($type == 'reorder' || $type == 'edit') {
            $this->title = get_screen_title('EDIT_FORUM');
        }

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
        $this->add_one_label = do_lang_tempcode('ADD_FORUM');
        $this->edit_this_label = do_lang_tempcode('EDIT_THIS_FORUM');
        $this->edit_one_label = do_lang_tempcode('EDIT_FORUM');

        global $C_TITLE;
        $C_TITLE = null;

        if (get_forum_type() != 'cns') {
            warn_exit(do_lang_tempcode('NO_CNS'));
        } else {
            cns_require_all_forum_stuff();
        }
        require_code('cns_forums_action');
        require_code('cns_forums_action2');
        require_code('cns_forums2');
        require_css('cns');
        require_css('cns_editor');

        load_up_all_module_category_permissions($GLOBALS['FORUM_DRIVER']->get_guest_id(), 'forums');

        if ($type == 'browse') {
            return $this->browse();
        }
        if ($type == 'reorder') {
            return $this->reorder();
        }

        return new Tempcode();
    }

    /**
     * The do-next manager for before content management.
     *
     * @return Tempcode The UI
     */
    public function browse()
    {
        $menu_links = array(
            array('admin/add_one_category', array('admin_cns_forum_groupings', array('type' => 'add'), get_module_zone('admin_cns_forum_groupings')), do_lang('ADD_FORUM_GROUPING')),
            array('admin/edit_one_category', array('admin_cns_forum_groupings', array('type' => 'edit'), get_module_zone('admin_cns_forum_groupings')), do_lang('EDIT_FORUM_GROUPING')),
            array('admin/add', array('_SELF', array('type' => 'add'), '_SELF'), do_lang('ADD_FORUM')),
            array('admin/edit', array('_SELF', array('type' => 'edit'), '_SELF'), do_lang('EDIT_FORUM')),
        );

        if (addon_installed('cns_post_templates')) {
            require_lang('cns_post_templates');
            $menu_links[] = array('menu/adminzone/structure/forum/post_templates', array('admin_cns_post_templates', array('type' => 'browse'), get_module_zone('admin_cns_post_templates')), do_lang_tempcode('POST_TEMPLATES'), 'DOC_POST_TEMPLATES');
        }
        if (addon_installed('cns_multi_moderations')) {
            require_lang('cns_multi_moderations');
            $menu_links[] = array('menu/adminzone/structure/forum/multi_moderations', array('admin_cns_multi_moderations', array('type' => 'browse'), get_module_zone('admin_cns_multi_moderations')), do_lang_tempcode('MULTI_MODERATIONS'), 'DOC_MULTI_MODERATIONS');
        }

        require_code('templates_donext');
        require_code('fields');
        return do_next_manager(
            get_screen_title('MANAGE_FORUMS'),
            comcode_to_tempcode(do_lang('DOC_FORUMS') . "\n\n" . do_lang('DOC_FORUM_GROUPINGS'), null, true),
            array_merge($menu_links, manage_custom_fields_donext_link('post'), manage_custom_fields_donext_link('topic'), manage_custom_fields_donext_link('forum')),
            do_lang('MANAGE_FORUMS')
        );
    }

    /**
     * Get Tempcode for a forum adding/editing form.
     *
     * @param  ?AUTO_LINK $id The ID of the forum being edited (null: adding, not editing)
     * @param  SHORT_TEXT $name The name of the forum
     * @param  LONG_TEXT $description The description of the forum
     * @param  ?AUTO_LINK $forum_grouping_id The ID of the forum grouping for the forum (null: first)
     * @param  ?AUTO_LINK $parent_forum The parent forum (null: root)
     * @param  ?integer $position The position (null: next)
     * @param  BINARY $post_count_increment Whether post counts are incremented in this forum
     * @param  BINARY $order_sub_alpha Whether subforums are ordered alphabetically (instead of manually)
     * @param  LONG_TEXT $intro_question Introductory question posed to all newcomers to the forum
     * @param  LONG_TEXT $intro_answer Answer to the introductory question (or blank if it was just an 'ok')
     * @param  SHORT_TEXT $redirection Redirection code (blank implies a normal forum, not a redirector)
     * @param  ID_TEXT $order The order the topics are shown in, by default
     * @param  BINARY $is_threaded Whether the forum is threaded
     * @param  BINARY $allows_anonymous_posts Whether anonymous posts are allowed
     * @return array A pair: The input fields, Hidden fields
     */
    public function get_form_fields($id = null, $name = '', $description = '', $forum_grouping_id = null, $parent_forum = null, $position = null, $post_count_increment = 1, $order_sub_alpha = 0, $intro_question = '', $intro_answer = '', $redirection = '', $order = 'last_post', $is_threaded = 0, $allows_anonymous_posts = 1)
    {
        if ($forum_grouping_id === null) {
            $forum_grouping_id = get_param_integer('forum_grouping_id', db_get_first_id());
        }

        if ($parent_forum === null) {
            $parent_forum = get_param_integer('parent_forum', null);
        }

        $fields = new Tempcode();
        $hidden = new Tempcode();

        $fields->attach(form_input_line(do_lang_tempcode('NAME'), do_lang_tempcode('DESCRIPTION_NAME'), 'name', $name, true));
        $fields->attach(form_input_line_comcode(do_lang_tempcode('DESCRIPTION'), do_lang_tempcode('DESCRIPTION_DESCRIPTION'), 'description', $description, false));
        $list = cns_create_selection_list_forum_groupings(null, $forum_grouping_id);
        $fields->attach(form_input_list(do_lang_tempcode('FORUM_GROUPING'), do_lang_tempcode('DESCRIPTION_FORUM_GROUPING'), 'forum_grouping_id', $list));
        if (($id === null) || (($id !== null) && ($id != db_get_first_id()))) {
            $fields->attach(form_input_tree_list(do_lang_tempcode('PARENT'), do_lang_tempcode('DESCRIPTION_PARENT_FORUM'), 'parent_forum', null, 'choose_forum', array(), true, ($parent_forum === null) ? '' : strval($parent_forum)));
        }

        $fields->attach(get_order_field('forum', null, $position, null, null, 'position', do_lang_tempcode('DESCRIPTION_FORUM_ORDER')));

        $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => 'cb47ed06695dc2cd99211772fe4c5643', 'SECTION_HIDDEN' => $post_count_increment == 1 && $order_sub_alpha == 0 && ($intro_question == '') && ($intro_answer == '') && ($redirection == '') && ($order == 'last_post'), 'TITLE' => do_lang_tempcode('ADVANCED'))));
        $fields->attach(form_input_tick(do_lang_tempcode('POST_COUNT_INCREMENT'), do_lang_tempcode('DESCRIPTION_POST_COUNT_INCREMENT'), 'post_count_increment', $post_count_increment == 1));
        $fields->attach(form_input_tick(do_lang_tempcode('ORDER_SUB_ALPHA'), do_lang_tempcode('DESCRIPTION_ORDER_SUB_ALPHA'), 'order_sub_alpha', $order_sub_alpha == 1));
        $fields->attach(form_input_text_comcode(do_lang_tempcode('INTRO_QUESTION'), do_lang_tempcode('DESCRIPTION_INTRO_QUESTION'), 'intro_question', $intro_question, false));
        $fields->attach(form_input_line(do_lang_tempcode('INTRO_ANSWER'), do_lang_tempcode('DESCRIPTION_INTRO_ANSWER'), 'intro_answer', $intro_answer, false));
        $fields->attach(form_input_line(do_lang_tempcode('REDIRECTING'), do_lang_tempcode('DESCRIPTION_FORUM_REDIRECTION'), 'redirection', $redirection, false));
        $list = new Tempcode();
        $list->attach(form_input_list_entry('last_post', $order == 'last_post', do_lang_tempcode('FORUM_ORDER_BY_LAST_POST')));
        $list->attach(form_input_list_entry('first_post', $order == 'first_post', do_lang_tempcode('FORUM_ORDER_BY_FIRST_POST')));
        $list->attach(form_input_list_entry('title', $order == 'title', do_lang_tempcode('FORUM_ORDER_BY_TITLE')));
        $fields->attach(form_input_list(do_lang_tempcode('TOPIC_ORDER'), do_lang_tempcode('DESCRIPTION_TOPIC_ORDER'), 'topic_order', $list));
        $fields->attach(form_input_tick(do_lang_tempcode('IS_THREADED'), do_lang_tempcode('DESCRIPTION_IS_THREADED'), 'is_threaded', $is_threaded == 1));
        if (get_option('is_on_anonymous_posts') == '1') {
            $fields->attach(form_input_tick(do_lang_tempcode('ALLOWS_ANONYMOUS_POSTS'), do_lang_tempcode('DESCRIPTION_ALLOWS_ANONYMOUS_POSTS'), 'allows_anonymous_posts', $allows_anonymous_posts == 1));
        }

        $fields->attach(metadata_get_fields('forum', ($id === null) ? null : strval($id)));

        if (addon_installed('content_reviews')) {
            $fields->attach(content_review_get_fields('forum', ($id === null) ? null : strval($id)));
        }

        // Permissions
        $fields->attach($this->get_permission_fields(($id === null) ? null : strval($id), null, ($id === null)));
        if (addon_installed('ecommerce')) {
            require_code('ecommerce_permission_products');
            $fields->attach(permission_product_form('forum', ($id === null) ? null : strval($id)));
        }

        return array($fields, $hidden);
    }

    /**
     * Get a UI to choose a forum to edit.
     *
     * @param  AUTO_LINK $id The ID of the forum we are generating the tree below (start recursion with db_get_first_id())
     * @param  SHORT_TEXT $forum The name of the forum $id
     * @param  array $all_forums A list of rows of all forums, or array() if the function is to get the list itself
     * @param  integer $position The relative position of this forum wrt the others on the same level/branch in the UI
     * @param  integer $sub_num_in_parent_forum_grouping The number of forums in the parent forum grouping
     * @param  ?BINARY $order_sub_alpha Whether to order own subcategories alphabetically (null: ask the DB)
     * @param  ?BINARY $parent_order_sub_alpha Whether to order subcategories alphabetically (null: ask the DB)
     * @param  boolean $huge Whether we are dealing with a huge forum structure
     * @return Tempcode The UI
     */
    public function get_forum_tree($id, $forum, &$all_forums, $position = 0, $sub_num_in_parent_forum_grouping = 1, $order_sub_alpha = null, $parent_order_sub_alpha = null, $huge = false)
    {
        $forum_groupings = new Tempcode();

        if ($huge) {
            $all_forums = $GLOBALS['FORUM_DB']->query_select('f_forums', array('id', 'f_name', 'f_position', 'f_forum_grouping_id', 'f_order_sub_alpha', 'f_parent_forum'), array('f_parent_forum' => $id), 'ORDER BY f_parent_forum,f_position', intval(get_option('general_safety_listing_limit')));
            if (count($all_forums) == intval(get_option('general_safety_listing_limit'))) {
                return paragraph(do_lang_tempcode('TOO_MANY_TO_CHOOSE_FROM'), 'tozu1if5yx6og9lmfx7jc0eczhnzahx1', 'red-alert');
            }
        } else {
            if (count($all_forums) == 0) {
                $all_forums = $GLOBALS['FORUM_DB']->query_select('f_forums', array('id', 'f_name', 'f_position', 'f_forum_grouping_id', 'f_order_sub_alpha', 'f_parent_forum'), array(), 'ORDER BY f_parent_forum,f_position');
            }
        }

        if ($order_sub_alpha === null) {
            $parent_order_sub_alpha = 0;
            $order_sub_alpha = $GLOBALS['FORUM_DB']->query_select_value('f_forums', 'f_order_sub_alpha', array('id' => $id));
        }

        global $C_TITLE;
        if ($C_TITLE === null) {
            $C_TITLE = collapse_2d_complexity('id', 'c_title', $GLOBALS['FORUM_DB']->query_select('f_forum_groupings', array('id', 'c_title')));
        }

        $_forum_groupings = array();
        foreach ($all_forums as $_forum) {
            if ($_forum['f_parent_forum'] == $id) {
                $_forum_groupings[$_forum['f_forum_grouping_id']] = 1;
            }
        }
        $num_forum_groupings = count($_forum_groupings);

        $order = ($order_sub_alpha == 1) ? 'f_name' : 'f_position';
        $subforums = array();
        foreach ($all_forums as $_forum) {
            if ($_forum['f_parent_forum'] == $id) {
                $subforums[$_forum['id']] = $_forum;
            }
        }
        if ($order == 'f_name') {
            sort_maps_by($subforums, 'f_name');
        }
        $forum_grouping_id = null;
        $position_in_cat = 0;
        $forum_grouping_position = 0;
        $forums = null;
        $orderings = '';
        while (count($subforums) != 0) {
            $i = null;
            if ($forum_grouping_id !== null) {
                foreach ($subforums as $j => $subforum) {
                    if ($subforum['f_forum_grouping_id'] == $forum_grouping_id) {
                        $i = $j;
                        break;
                    }
                }
            }

            if ($i === null) {
                if ($forums !== null) {
                    $forum_groupings->attach(do_template('CNS_EDIT_FORUM_SCREEN_GROUPING', array('_GUID' => '889173769e237b917b7e06eda0fb4350', 'ORDERINGS' => $orderings, 'GROUPING' => isset($C_TITLE[$forum_grouping_id]) ? $C_TITLE[$forum_grouping_id] : do_lang('UNKNOWN'), 'SUBFORUMS' => $forums)));
                    $forum_grouping_position++;
                }
                $forums = new Tempcode();
                $i = 0;
                foreach ($subforums as $j => $subforum) {
                    $i = $j;
                    break;
                }
                $forum_grouping_id = $subforums[$i]['f_forum_grouping_id'];
                $position_in_cat = 0;
                $sub_num_in_forum_grouping = 0;
                foreach ($subforums as $subforum) {
                    if ($subforum['f_forum_grouping_id'] == $forum_grouping_id) {
                        $sub_num_in_forum_grouping++;
                    }
                }
            }

            $subforum = $subforums[$i];

            $orderings = '';
            if (($order_sub_alpha == 0) && (!$huge)) {
                for ($_i = 0; $_i < $num_forum_groupings; $_i++) {
                    $orderings .= '<option ' . (($_i == $forum_grouping_position) ? 'selected="selected"' : '') . '>' . strval($_i + 1) . '</option>';
                }
                $orderings = '<label for="forum_grouping_order_' . strval($id) . '_' . strval($forum_grouping_id) . '">' . do_lang('ORDER') . ' <span class="accessibility-hidden"> (' . (array_key_exists($forum_grouping_id, $C_TITLE) ? escape_html($C_TITLE[$forum_grouping_id]) : '') . ')</span> <select id="forum_grouping_order_' . strval($id) . '_' . strval($forum_grouping_id) . '" name="forum_grouping_order_' . strval($id) . '_' . strval($forum_grouping_id) . '">' . $orderings . '</select></label>'; // XHTMLXHTML
            }

            $forums->attach($this->get_forum_tree($subforum['id'], $subforum['f_name'], $all_forums, $position_in_cat, $sub_num_in_forum_grouping, $subforum['f_order_sub_alpha'], $order_sub_alpha, $huge));

            $position_in_cat++;
            unset($subforums[$i]);
        }
        if ($forum_grouping_id !== null) {
            $forum_groupings->attach(do_template('CNS_EDIT_FORUM_SCREEN_GROUPING', array('_GUID' => '6cb30ec5189f75a9631b2bb430c89fd0', 'ORDERINGS' => $orderings, 'GROUPING' => $C_TITLE[$forum_grouping_id], 'SUBFORUMS' => $forums)));
        }

        $edit_url = build_url(array('page' => '_SELF', 'type' => '_edit', 'id' => $id), '_SELF');
        $view_map = array('page' => 'forumview');
        if ($id != db_get_first_id()) {
            $view_map['id'] = $id;
        }
        $view_url = build_url($view_map, get_module_zone('forumview'));

        $class = (!has_category_access($GLOBALS['FORUM_DRIVER']->get_guest_id(), 'forums', strval($id))) ? 'access-restricted-in-list' : '';

        $orderings = '';
        if ($parent_order_sub_alpha == 0) {
            for ($i = 0; $i < $sub_num_in_parent_forum_grouping; $i++) {
                $orderings .= '<option ' . (($i == $position) ? 'selected="selected"' : '') . '>' . strval($i + 1) . '</option>';
            }
            $orderings = '<label for="order_' . strval($id) . '">' . do_lang('ORDER') . ' <span class="accessibility-hidden"> (' . escape_html($forum) . ')</span> <select id="order_' . strval($id) . '" name="order_' . strval($id) . '">' . $orderings . '</select></label>'; // XHTMLXHTML
        }

        if ($GLOBALS['XSS_DETECT']) {
            ocp_mark_as_escaped($orderings);
        }

        return do_template('CNS_EDIT_FORUM_SCREEN_FORUM', array(
            '_GUID' => '35fdeb9848919b5c30b069eb5df603d5',
            'ID' => strval($id),
            'ORDERINGS' => $orderings,
            'FORUM_GROUPINGS' => $forum_groupings,
            'CLASS' => $class,
            'FORUM' => $forum,
            'VIEW_URL' => $view_url,
            'EDIT_URL' => $edit_url,
        ));
    }

    /**
     * The UI to choose a forum to edit (relies on get_forum_tree to do almost all the work).
     *
     * @return Tempcode The UI
     */
    public function edit()
    {
        $huge = ($GLOBALS['FORUM_DB']->query_select_value('f_forums', 'COUNT(*)') > intval(get_option('general_safety_listing_limit')));

        $all_forums = array();
        $forums = $this->get_forum_tree(db_get_first_id(), $GLOBALS['FORUM_DB']->query_select_value('f_forums', 'f_name', array('id' => db_get_first_id())), $all_forums, 0, 1, null, null, $huge);

        if ($huge) {
            $reorder_url = new Tempcode();
        } else {
            $reorder_url = build_url(array('page' => '_SELF', 'type' => 'reorder'), '_SELF');
        }

        return do_template('CNS_EDIT_FORUM_SCREEN', array('_GUID' => '762810dcff9acfa51995984d2c008fef', 'REORDER_URL' => $reorder_url, 'TITLE' => $this->title, 'ROOT_FORUM' => $forums));
    }

    /**
     * The actualiser to reorder forums.
     *
     * @return Tempcode The UI
     */
    public function reorder()
    {
        $all = $GLOBALS['FORUM_DB']->query_select('f_forums', array('id', 'f_parent_forum', 'f_forum_grouping_id'));
        $ordering = array();
        foreach ($all as $forum) {
            $cat_order = post_param_integer('forum_grouping_order_' . (($forum['f_parent_forum'] === null) ? '' : strval($forum['f_parent_forum'])) . '_' . (($forum['f_forum_grouping_id'] === null) ? '' : strval($forum['f_forum_grouping_id'])), null);
            $order = post_param_integer('order_' . strval($forum['id']), null);
            if (($cat_order !== null) && ($order !== null)) { // Should only be null if since created
                if (!array_key_exists($forum['f_parent_forum'], $ordering)) {
                    $ordering[$forum['f_parent_forum']] = array();
                }
                if (!array_key_exists($cat_order, $ordering[$forum['f_parent_forum']])) {
                    $ordering[$forum['f_parent_forum']][$cat_order] = array();
                }
                while (array_key_exists($order, $ordering[$forum['f_parent_forum']][$cat_order])) {
                    $order++;
                }

                $ordering[$forum['f_parent_forum']][$cat_order][$order] = $forum['id'];
            }
        }

        foreach ($ordering as $_ordering) {
            ksort($_ordering);
            $order = 0;
            foreach ($_ordering as $forums) {
                ksort($forums);
                foreach ($forums as $forum_id) {
                    $GLOBALS['FORUM_DB']->query_update('f_forums', array('f_position' => $order), array('id' => $forum_id), '', 1);
                    $order++;
                }
            }
        }

        $url = build_url(array('page' => '_SELF', 'type' => 'edit'), '_SELF');
        return redirect_screen($this->title, $url, do_lang_tempcode('SUCCESS'));
    }

    /**
     * Standard crud_module delete possibility checker.
     *
     * @param  ID_TEXT $_id The entry being potentially deleted
     * @return boolean Whether it may be deleted
     */
    public function may_delete_this($_id)
    {
        $id = intval($_id);

        if ($id == db_get_first_id()) {
            return false;
        }

        $f_name = $GLOBALS['FORUM_DB']->query_select_value('f_forums', 'f_name', array('id' => $id));

        $cnt = $GLOBALS['FORUM_DB']->query_select_value('f_forums', 'COUNT(*)', array('f_name' => $f_name));
        if ($cnt > 1) {
            // We have duplication
            return true;
        }

        $hooks = find_all_hooks('systems', 'config');
        foreach (array_keys($hooks) as $hook) {
            $value = get_option($hook, true);
            if (($value === $f_name) || ($value === $_id)) {
                require_code('hooks/systems/config/' . filter_naughty_harsh($hook));
                $ob = object_factory('Hook_config_' . filter_naughty_harsh($hook));

                $option = $ob->get_details();
                if ($option['type'] == 'forum') {
                    if (($GLOBALS['CURRENT_SHARE_USER'] === null) || ($option['shared_hosting_restricted'] == 0)) {
                        require_code('config2');
                        require_all_lang();
                        $edit_url = config_option_url($hook);
                        $message = do_lang_tempcode(
                            'CANNOT_DELETE_FORUM_OPTION',
                            escape_html($edit_url),
                            escape_html(do_lang_tempcode($option['human_name']))
                        );
                        attach_message($message, 'notice');
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Standard crud_module edit form filler.
     *
     * @param  ID_TEXT $id The entry being edited
     * @return array A tuple: fields, hidden-fields, delete-fields, N/A, N/A, N/A, action fields
     */
    public function fill_in_edit_form($id)
    {
        $test = $GLOBALS['FORUM_DB']->query_select_value_if_there('group_privileges p JOIN ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_groups g ON g.id=group_id', 'g.id', array('module_the_name' => 'forums', 'category_name' => $id, 'the_value' => '1', 'g_is_private_club' => 1));
        if ($test !== null) {
            attach_message(do_lang_tempcode('THIS_CLUB_FORUM'), 'notice');
        }

        $m = $GLOBALS['FORUM_DB']->query_select('f_forums', array('*'), array('id' => intval($id)), '', 1);
        if (!array_key_exists(0, $m)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'forum'));
        }
        $r = $m[0];

        $fields = $this->get_form_fields($r['id'], $r['f_name'], get_translated_text($r['f_description'], $GLOBALS['FORUM_DB']), $r['f_forum_grouping_id'], $r['f_parent_forum'], $r['f_position'], $r['f_post_count_increment'], $r['f_order_sub_alpha'], get_translated_text($r['f_intro_question'], $GLOBALS['FORUM_DB']), $r['f_intro_answer'], $r['f_redirection'], $r['f_order'], $r['f_is_threaded'], $r['f_allows_anonymous_posts']);

        $delete_fields = new Tempcode();
        if (intval($id) != db_get_first_id()) {
            $delete_fields->attach(form_input_tree_list(do_lang_tempcode('TARGET'), do_lang_tempcode('DESCRIPTION_TOPIC_MOVE_TARGET'), 'target_forum', null, 'choose_forum', array(), true, $id));
            $delete_fields->attach(form_input_tick(do_lang_tempcode('DELETE_TOPICS'), do_lang_tempcode('DESCRIPTION_DELETE_TOPICS'), 'delete_topics', false));
        }

        $action_fields = new Tempcode();
        $action_fields->attach(form_input_tick(do_lang_tempcode('RESET_INTRO_ACCEPTANCE'), do_lang_tempcode('DESCRIPTION_RESET_INTRO_ACCEPTANCE'), 'reset_intro_acceptance', false));

        return array($fields[0], $fields[1], $delete_fields, null, false, null, $action_fields);
    }

    /**
     * Standard crud_module add actualiser.
     *
     * @return ID_TEXT The entry added
     */
    public function add_actualisation()
    {
        require_code('cns_forums_action2');

        $parent_forum = post_param_integer('parent_forum');
        $name = post_param_string('name');

        $metadata = actual_metadata_get_fields('forum', null);

        $id = strval(cns_make_forum($name, post_param_string('description'), post_param_integer('forum_grouping_id'), null, $parent_forum, post_param_order_field(), post_param_integer('post_count_increment', 0), post_param_integer('order_sub_alpha', 0), post_param_string('intro_question'), post_param_string('intro_answer'), post_param_string('redirection', false, INPUT_FILTER_URL_GENERAL), post_param_string('topic_order'), post_param_integer('is_threaded', 0), post_param_integer('allows_anonymous_posts', 0)));

        set_url_moniker('forum', $id);

        // Warning if there is full access to this forum, but not to the parent
        $admin_groups = $GLOBALS['FORUM_DRIVER']->get_super_admin_groups();
        $groups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list(true, true);
        $full_access = true;
        foreach (array_keys($groups) as $gid) {
            if (!in_array($gid, $admin_groups)) {
                if (post_param_integer('access_' . strval($gid), 0) == 0) {
                    $full_access = false;
                    break;
                }
            }
        }
        if ($full_access) {
            $parent_has_full_access = true;
            $access_rows = $GLOBALS['FORUM_DB']->query_select('group_category_access', array('group_id'), array('module_the_name' => 'forums', 'category_name' => strval($parent_forum)));
            $access = array();
            foreach ($access_rows as $row) {
                $access[$row['group_id']] = 1;
            }
            foreach (array_keys($groups) as $gid) {
                if (!in_array($gid, $admin_groups)) {
                    if (!array_key_exists($gid, $access)) {
                        $parent_has_full_access = false;
                        break;
                    }
                }
            }
            if (!$parent_has_full_access) {
                attach_message(do_lang_tempcode('ANOMALOUS_FORUM_ACCESS'), 'notice');
            }
        }

        $this->set_permissions($id);
        if (addon_installed('ecommerce')) {
            require_code('ecommerce_permission_products');
            permission_product_save('forum', $id);
        }

        if (addon_installed('content_reviews')) {
            content_review_set('forum', $id);
        }

        if ((has_actual_page_access(get_modal_user(), 'forumview')) && (has_category_access(get_modal_user(), 'forums', $id))) {
            require_code('activities');
            syndicate_described_activity('cns:ACTIVITY_ADD_FORUM', $name, '', '', '_SEARCH:forumview:browse:' . $id, '', '', 'cns_forum');
        }

        return $id;
    }

    /**
     * Standard crud_module edit actualiser.
     *
     * @param  ID_TEXT $id The entry being edited
     */
    public function edit_actualisation($id)
    {
        $metadata = actual_metadata_get_fields('forum', $id);

        cns_edit_forum(
            intval($id),
            post_param_string('name'),
            post_param_string('description', STRING_MAGIC_NULL),
            post_param_integer('forum_grouping_id', fractional_edit() ? INTEGER_MAGIC_NULL : false),
            post_param_integer('parent_forum', fractional_edit() ? INTEGER_MAGIC_NULL : null/*root forum*/),
            fractional_edit() ? INTEGER_MAGIC_NULL : post_param_order_field(),
            post_param_integer('post_count_increment', fractional_edit() ? INTEGER_MAGIC_NULL : 0),
            post_param_integer('order_sub_alpha', fractional_edit() ? INTEGER_MAGIC_NULL : 0),
            post_param_string('intro_question', STRING_MAGIC_NULL),
            post_param_string('intro_answer', STRING_MAGIC_NULL),
            post_param_string('redirection', STRING_MAGIC_NULL, INPUT_FILTER_URL_GENERAL),
            post_param_string('topic_order', STRING_MAGIC_NULL),
            post_param_integer('is_threaded', fractional_edit() ? INTEGER_MAGIC_NULL : 0),
            post_param_integer('allows_anonymous_posts', fractional_edit() ? INTEGER_MAGIC_NULL : 0),
            post_param_integer('reset_intro_acceptance', 0) == 1
        );

        if (!fractional_edit()) {
            require_code('cns_groups2');

            $old_access_mapping = collapse_1d_complexity('group_id', $GLOBALS['FORUM_DB']->query_select('group_category_access', array('group_id'), array('module_the_name' => 'forums', 'category_name' => $id)));

            require_code('cns_groups_action');
            require_code('cns_groups_action2');

            $lost_groups = array();
            foreach ($old_access_mapping as $group_id) {
                if (post_param_integer('access_' . strval($group_id), 0) == 0) { // Lost access
                    $lost_groups[] = $group_id;
                }
            }

            $this->set_permissions($id);
            if (addon_installed('ecommerce')) {
                require_code('ecommerce_permission_products');
                permission_product_save('forum', $id);
            }

            if (addon_installed('content_reviews')) {
                content_review_set('forum', $id);
            }
        }
    }

    /**
     * Standard crud_module delete actualiser.
     *
     * @param  ID_TEXT $id The entry being deleted
     */
    public function delete_actualisation($id)
    {
        cns_delete_forum(intval($id), post_param_integer('target_forum'), post_param_integer('delete_topics', 0));
    }
}
