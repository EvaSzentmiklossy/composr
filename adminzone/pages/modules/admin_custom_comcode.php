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
 * @package    custom_comcode
 */

require_code('crud_module');
require_javascript('custom_comcode');

/**
 * Module page class.
 */
class Module_admin_custom_comcode extends Standard_crud_module
{
    public $table_prefix = 'tag_';
    public $array_key = 'tag_tag';
    public $lang_type = 'CUSTOM_COMCODE_TAG';
    public $select_name = 'TITLE';
    public $non_integer_id = true;
    public $menu_label = 'CUSTOM_COMCODE';
    public $functions = 'moduleAdminCustomComcode';
    public $orderer = 'tag_title';
    public $title_is_multi_lang = true;
    public $donext_entry_content_type = 'custom_comcode_tag';
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
        return array(
            'browse' => array('CUSTOM_COMCODE', 'menu/adminzone/setup/custom_comcode'),
        ) + parent::get_entry_points();
    }

    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = true;
        return $info;
    }

    /**
     * Uninstall the module.
     */
    public function uninstall()
    {
        $GLOBALS['SITE_DB']->drop_table_if_exists('custom_comcode');
    }

    /**
     * Install the module.
     *
     * @param  ?integer $upgrade_from What version we're upgrading from (null: new install)
     * @param  ?integer $upgrade_from_hack What hack version we're upgrading from (null: new-install/not-upgrading-from-a-hacked-version)
     */
    public function install($upgrade_from = null, $upgrade_from_hack = null)
    {
        $GLOBALS['SITE_DB']->create_table('custom_comcode', array(
            'tag_tag' => '*ID_TEXT',
            'tag_title' => 'SHORT_TRANS',
            'tag_description' => 'SHORT_TRANS',
            'tag_replace' => 'LONG_TEXT',
            'tag_example' => 'LONG_TEXT',
            'tag_parameters' => 'SHORT_TEXT',
            'tag_enabled' => 'BINARY',
            'tag_dangerous_tag' => 'BINARY',
            'tag_block_tag' => 'BINARY',
            'tag_textual_tag' => 'BINARY'
        ));
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

        require_lang('custom_comcode');
        require_lang('comcode');

        set_helper_panel_tutorial('tut_adv_comcode');

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
        require_code('custom_comcode');

        $this->add_one_label = do_lang_tempcode('ADD_CUSTOM_COMCODE_TAG');
        $this->edit_this_label = do_lang_tempcode('EDIT_THIS_CUSTOM_COMCODE_TAG');
        $this->edit_one_label = do_lang_tempcode('EDIT_CUSTOM_COMCODE_TAG');

        if ($type == 'add') {
            require_javascript('custom_comcode');
            $this->functions = $this->functions ? ($this->functions . ',moduleAdminCustomComcodeRunStart') : 'moduleAdminCustomComcodeRunStart';
        }

        if ($type == 'browse') {
            return $this->browse();
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
        require_code('templates_donext');
        return do_next_manager(
            get_screen_title('CUSTOM_COMCODE'),
            comcode_lang_string('DOC_CUSTOM_COMCODE'),
            array(
                array('menu/_generic_admin/add_one', array('_SELF', array('type' => 'add'), '_SELF'), do_lang('ADD_CUSTOM_COMCODE_TAG')),
                array('menu/_generic_admin/edit_one', array('_SELF', array('type' => 'edit'), '_SELF'), do_lang('EDIT_CUSTOM_COMCODE_TAG')),
            ),
            do_lang('CUSTOM_COMCODE')
        );
    }

    /**
     * Standard crud_module table function.
     *
     * @param  array $url_map Details to go to build_url for link to the next screen.
     * @return array A pair: The choose table, Whether re-ordering is supported from this screen.
     */
    public function create_selection_list_choose_table($url_map)
    {
        require_code('templates_results_table');

        $current_ordering = get_param_string('sort', 'tag_tag ASC', INPUT_FILTER_GET_COMPLEX);
        if (strpos($current_ordering, ' ') === false) {
            warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
        }
        list($sortable, $sort_order) = explode(' ', $current_ordering, 2);
        $sortables = array(
            'tag_tag' => do_lang_tempcode('COMCODE_TAG'),
            'tag_title' => do_lang_tempcode('TITLE'),
            'tag_dangerous_tag' => do_lang_tempcode('DANGEROUS_TAG'),
            'tag_block_tag' => do_lang_tempcode('BLOCK_TAG'),
            'tag_textual_tag' => do_lang_tempcode('TEXTUAL_TAG'),
            'tag_enabled' => do_lang_tempcode('ENABLED'),
        );

        $header_row = results_field_title(array(
            do_lang_tempcode('COMCODE_TAG'),
            do_lang_tempcode('TITLE'),
            do_lang_tempcode('DANGEROUS_TAG'),
            do_lang_tempcode('BLOCK_TAG'),
            do_lang_tempcode('TEXTUAL_TAG'),
            do_lang_tempcode('ENABLED'),
            do_lang_tempcode('ACTIONS'),
        ), $sortables, 'sort', $sortable . ' ' . $sort_order);
        if (((strtoupper($sort_order) != 'ASC') && (strtoupper($sort_order) != 'DESC')) || (!array_key_exists($sortable, $sortables))) {
            log_hack_attack_and_exit('ORDERBY_HACK');
        }

        $fields = new Tempcode();

        require_code('form_templates');
        list($rows, $max_rows) = $this->get_entry_rows(false, $current_ordering);
        foreach ($rows as $row) {
            $edit_url = build_url($url_map + array('id' => $row['tag_tag']), '_SELF');

            $fields->attach(results_entry(array($row['tag_tag'], get_translated_text($row['tag_title']), ($row['tag_dangerous_tag'] == 1) ? do_lang_tempcode('YES') : do_lang_tempcode('NO'), ($row['tag_block_tag'] == 1) ? do_lang_tempcode('YES') : do_lang_tempcode('NO'), ($row['tag_textual_tag'] == 1) ? do_lang_tempcode('YES') : do_lang_tempcode('NO'), ($row['tag_enabled'] == 1) ? do_lang_tempcode('YES') : do_lang_tempcode('NO'), protect_from_escaping(hyperlink($edit_url, do_lang_tempcode('EDIT'), false, false, '#' . $row['tag_tag']))), true));
        }

        return array(results_table(do_lang($this->menu_label), get_param_integer('start', 0), 'start', either_param_integer('max', 20), 'max', $max_rows, $header_row, $fields, $sortables, $sortable, $sort_order), false);
    }

    /**
     * Get Tempcode for a Custom Comcode tag adding/editing form.
     *
     * @param  SHORT_TEXT $title The title (name) of the Custom Comcode tag
     * @param  LONG_TEXT $description The description of the tag
     * @param  BINARY $enabled Whether the tag is enabled
     * @param  ID_TEXT $tag The actual tag code
     * @param  LONG_TEXT $replace What to replace the tag with
     * @param  LONG_TEXT $example Example usage
     * @param  SHORT_TEXT $parameters Comma-separated list of accepted parameters
     * @param  BINARY $dangerous_tag Whether it is a dangerous tag
     * @param  BINARY $block_tag Whether it is a block tag
     * @param  BINARY $textual_tag Whether it is a textual tag
     * @return array A pair: The input fields, Hidden fields
     */
    public function get_form_fields($title = '', $description = '', $enabled = 1, $tag = 'this', $replace = '<span class="example" style="color: {color}">{content}</span>', $example = '[this color="red"]blah[/this]', $parameters = 'color=black', $dangerous_tag = 0, $block_tag = 0, $textual_tag = 1)
    {
        $fields = new Tempcode();
        require_code('comcode_compiler');
        $fields->attach(form_input_codename(do_lang_tempcode('COMCODE_TAG'), do_lang_tempcode('DESCRIPTION_COMCODE_TAG'), 'tag', $tag, true, null, MAX_COMCODE_TAG_LOOK_AHEAD_LENGTH));
        $fields->attach(form_input_line(do_lang_tempcode('TITLE'), do_lang_tempcode('DESCRIPTION_TAG_TITLE'), 'title', $title, true));
        $fields->attach(form_input_line(do_lang_tempcode('DESCRIPTION'), do_lang_tempcode('DESCRIPTION_DESCRIPTION'), 'description', $description, true));
        $fields->attach(form_input_line_multi(do_lang_tempcode('PARAMETERS'), do_lang_tempcode('DESCRIPTION_COMCODE_PARAMETERS'), 'parameters', explode(',', $parameters), 0));
        $fields->attach(form_input_text(do_lang_tempcode('COMCODE_REPLACE'), do_lang_tempcode('DESCRIPTION_COMCODE_REPLACE'), 'replace', $replace, true));
        $fields->attach(form_input_line(do_lang_tempcode('EXAMPLE'), do_lang_tempcode('DESCRIPTION_COMCODE_EXAMPLE'), 'example', $example, true));
        $fields->attach(form_input_tick(do_lang_tempcode('DANGEROUS_TAG'), do_lang_tempcode('DESCRIPTION_DANGEROUS_TAG'), 'dangerous_tag', $dangerous_tag == 1));
        $fields->attach(form_input_tick(do_lang_tempcode('BLOCK_TAG'), do_lang_tempcode('DESCRIPTION_BLOCK_TAG'), 'block_tag', $block_tag == 1));
        $fields->attach(form_input_tick(do_lang_tempcode('TEXTUAL_TAG'), do_lang_tempcode('DESCRIPTION_TEXTUAL_TAG'), 'textual_tag', $textual_tag == 1));
        $fields->attach(form_input_tick(do_lang_tempcode('ENABLED'), '', 'enabled', $enabled == 1));

        return array($fields, new Tempcode());
    }

    /**
     * Standard crud_module edit form filler.
     *
     * @param  ID_TEXT $id The entry being edited
     * @return array A pair: The input fields, Hidden fields
     */
    public function fill_in_edit_form($id)
    {
        $m = $GLOBALS['SITE_DB']->query_select('custom_comcode', array('*'), array('tag_tag' => $id), '', 1);
        if (!array_key_exists(0, $m)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'custom_comcode_tag'));
        }
        $r = $m[0];

        return $this->get_form_fields(get_translated_text($r['tag_title']), get_translated_text($r['tag_description']), $r['tag_enabled'], $r['tag_tag'], $r['tag_replace'], $r['tag_example'], $r['tag_parameters'], $r['tag_dangerous_tag'], $r['tag_block_tag'], $r['tag_textual_tag']);
    }

    /**
     * Standard crud_module add actualiser.
     *
     * @return ID_TEXT The entry added
     */
    public function add_actualisation()
    {
        $tag = post_param_string('tag');

        $parameters = '';
        foreach ($_POST as $key => $val) {
            if (substr($key, 0, 11) != 'parameters_') {
                continue;
            }
            if ($val == '') {
                continue;
            }
            if ($parameters != '') {
                $parameters .= ',';
            }
            $parameters .= $val;
        }

        $title = post_param_string('title');
        $description = post_param_string('description');
        $replace = post_param_string('replace');
        $example = post_param_string('example');
        $enabled = post_param_integer('enabled', 0);
        $dangerous_tag = post_param_integer('dangerous_tag', 0);
        $block_tag = post_param_integer('block_tag', 0);
        $textual_tag = post_param_integer('textual_tag', 0);

        $this->check_parameters_all_there(($parameters == '') ? array() : explode(',', $parameters), $replace);

        add_custom_comcode_tag($tag, $title, $description, $replace, $example, $parameters, $enabled, $dangerous_tag, $block_tag, $textual_tag);

        return $tag;
    }

    /**
     * Standard crud_module edit actualiser.
     *
     * @param  ID_TEXT $id The entry being edited
     */
    public function edit_actualisation($id)
    {
        $tag = post_param_string('tag');

        $parameters = '';
        foreach ($_POST as $key => $val) {
            if (substr($key, 0, 11) != 'parameters_') {
                continue;
            }
            if ($val == '') {
                continue;
            }
            if ($parameters != '') {
                $parameters .= ',';
            }
            $parameters .= $val;
        }

        $title = post_param_string('title');
        $description = post_param_string('description');
        $replace = post_param_string('replace');
        $example = post_param_string('example');
        $enabled = post_param_integer('enabled', 0);
        $dangerous_tag = post_param_integer('dangerous_tag', 0);
        $block_tag = post_param_integer('block_tag', 0);
        $textual_tag = post_param_integer('textual_tag', 0);

        $this->check_parameters_all_there(($parameters == '') ? array() : explode(',', $parameters), $replace);

        edit_custom_comcode_tag($id, $tag, $title, $description, $replace, $example, $parameters, $enabled, $dangerous_tag, $block_tag, $textual_tag);

        $this->new_id = $tag;
    }

    /**
     * Check defined parameters are consistent with replace text.
     *
     * @param  array  $_parameters Parameters configured
     * @param  string $replace Text to replace within
     */
    private function check_parameters_all_there($_parameters, $replace)
    {
        $parameters = array();
        foreach ($_parameters as $param) {
            $parameters[] = strtolower(preg_replace('#=.*$#', '', $param));
        }
        $parameters[] = 'content'; // implied

        $matches = array();
        $num_matches = preg_match_all('#\{(\w+)[^\w\}]*\}#', $replace, $matches);
        $parameters_in_replace = array();
        for ($i = 0; $i < $num_matches; $i++) {
            $parameters_in_replace[] = strtolower($matches[1][$i]);
        }

        foreach (array_unique($parameters) as $param) {
            if (!in_array($param, $parameters_in_replace)) {
                attach_message(do_lang_tempcode('PARAMETER_DEFINED_NOT_USED', escape_html($param)), 'warn');
            }
        }

        foreach (array_unique($parameters_in_replace) as $param) {
            if (!in_array($param, $parameters)) {
                attach_message(do_lang_tempcode('PARAMETER_USED_NOT_DEFINED', escape_html($param)), 'warn');
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
        delete_custom_comcode_tag($id);
    }
}
