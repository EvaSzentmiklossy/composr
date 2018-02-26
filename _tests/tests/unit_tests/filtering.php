<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    testing_platform
 */

/**
 * Composr test case class (unit testing).
 */
class filtering_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testFiltercode()
    {
        $only = get_param_string('only', null);
        if (($only !== null) && ($only !== 'testFiltercode')) {
            return;
        }

        $this->cleanup_db();

        $guest_id = $GLOBALS['FORUM_DRIVER']->get_guest_id();
        $admin_id = $GLOBALS['FORUM_DRIVER']->get_guest_id() + 1;
        $test_id = $GLOBALS['FORUM_DRIVER']->get_guest_id() + 2;
        $guest_username = $GLOBALS['FORUM_DRIVER']->get_username($guest_id);

        $hook_contents = "
            <" . "?php

            class Hook_content_meta_aware_temp_test
            {
                public function info()
                {
                    return array(
                        'support_custom_fields' => true,

                        'content_type_label' => 'TITLE',
                        'content_type_universal_label' => 'Title',

                        'db' => \$GLOBALS['SITE_DB'],
                        'table' => 'temp_test',
                        'id_field' => 'id',
                        'id_field_numeric' => true,
                        'parent_category_field' => null,
                        'parent_category_meta_aware_type' => null,
                        'is_category' => false,
                        'is_entry' => true,
                        'category_field' => null,
                        'category_type' => null,
                        'parent_spec__table_name' => null,
                        'parent_spec__parent_name' => null,
                        'parent_spec__field_name' => null,
                        'category_is_string' => true,

                        'title_field' => 't_short_text',
                        'title_field_dereference' => false,
                        'title_field_supports_comcode' => false,
                        'description_field' => null,
                        'thumb_field' => null,
                        'thumb_field_is_theme_image' => false,
                        'alternate_icon_theme_image' => null,

                        'view_page_link_pattern' => null,
                        'edit_page_link_pattern' => null,
                        'view_category_page_link_pattern' => null,
                        'add_url' => null,
                        'archive_url' => null,

                        'support_url_monikers' => false,

                        'views_field' => null,
                        'order_field' => null,
                        'submitter_field' => null,
                        'author_field' => null,
                        'add_time_field' => null,
                        'edit_time_field' => null,
                        'date_field' => null,
                        'validated_field' => null,

                        'seo_type_code' => 'temp_test',

                        'feedback_type_code' => 'temp_test',

                        'permissions_type_code' => null,

                        'search_hook' => null,
                        'rss_hook' => null,
                        'attachment_hook' => null,
                        'unvalidated_hook' => null,
                        'notification_hook' => null,
                        'sitemap_hook' => null,

                        'addon_name' => null,

                        'cms_page' => null,
                        'module' => null,

                        'commandr_filesystem_hook' => null,
                        'commandr_filesystem__is_folder' => false,

                        'support_revisions' => false,

                        'support_privacy' => false,

                        'support_content_reviews' => false,

                        'actionlog_regexp' => null,

                        'filtercode_protected_fields' => array('t_secret'),
                    );
                }
            }
        ";
        file_put_contents(get_file_base() . '/sources_custom/hooks/systems/content_meta_aware/temp_test.php', trim(preg_replace('#^            #m', '', $hook_contents)) . "\n");

        $GLOBALS['SITE_DB']->create_table('temp_test', array(
            'id' => '*INTEGER',
            't_short_text' => 'SHORT_TEXT',
            't_short_trans' => 'SHORT_TRANS',
            't_member' => 'MEMBER',
            't_real' => 'REAL',
            't_time' => 'TIME',
            't_language_name' => 'LANGUAGE_NAME',
            't_id_text' => 'ID_TEXT',
            't_binary' => 'BINARY',
            't_secret' => 'SHORT_TEXT',
            't_linker' => 'INTEGER',
        ));
        $GLOBALS['SITE_DB']->create_index('temp_test', '#t_short_text', array('t_short_text'));
        $GLOBALS['SITE_DB']->create_index('temp_test', '#t_language_name', array('t_language_name'));
        $GLOBALS['SITE_DB']->create_index('temp_test', '#t_id_text', array('t_id_text'));

        $GLOBALS['SITE_DB']->query_insert('temp_test', array(
            'id' => 1,
            't_short_text' => 'axxxxx',
            't_member' => $guest_id,
            't_real' => 1.0,
            't_time' => 1000000,
            't_language_name' => 'EN',
            't_id_text' => 'axxxxx',
            't_binary' => 0,
            't_secret' => 'x',
            't_linker' => 1,
        ) + insert_lang('t_short_trans', 'axxxxx', 1));

        $GLOBALS['SITE_DB']->query_insert('temp_test', array(
            'id' => 2,
            't_short_text' => 'bxxxxx',
            't_member' => $admin_id,
            't_real' => 2.0,
            't_time' => 2000000,
            't_language_name' => 'ES',
            't_id_text' => 'bxxxxx',
            't_binary' => 1,
            't_secret' => 'x',
            't_linker' => 2,
        ) + insert_lang('t_short_trans', 'bxxxxx', 1));

        $GLOBALS['SITE_DB']->query_insert('temp_test', array(
            'id' => 3,
            't_short_text' => 'cxxxxx',
            't_member' => $test_id,
            't_real' => 3.0,
            't_time' => 3000000,
            't_language_name' => 'TA',
            't_id_text' => 'cxxxxx',
            't_binary' => 1,
            't_secret' => 'x',
            't_linker' => 3,
        ) + insert_lang('t_short_trans', 'cxxxxx', 1));

        $GLOBALS['SITE_DB']->query_delete('rating', array('rating_for_type' => 'temp_test__'));
        $GLOBALS['SITE_DB']->query_insert('rating', array('rating_for_type' => 'temp_test__', 'rating_for_id' => '1', 'rating_member' => get_member(), 'rating_ip' => get_ip_address(), 'rating_time' => time(), 'rating' => 4));
        $GLOBALS['SITE_DB']->query_insert('rating', array('rating_for_type' => 'temp_test__', 'rating_for_id' => '1', 'rating_member' => get_member(), 'rating_ip' => get_ip_address(), 'rating_time' => time(), 'rating' => 3));

        require_code('content2');
        seo_meta_set_for_explicit('temp_test', '1', 'abc,def', 'abc');

        $GLOBALS['SITE_DB']->create_table('temp_test_linked', array(
            'id' => '*INTEGER',
            'l_something' => 'INTEGER',
        ));

        $GLOBALS['SITE_DB']->query_insert('temp_test_linked', array(
            'id' => 1,
            'l_something' => 123,
        ));

        $GLOBALS['SITE_DB']->query_insert('temp_test_linked', array(
            'id' => 2,
            'l_something' => 124,
        ));

        $GLOBALS['SITE_DB']->query_insert('temp_test_linked', array(
            'id' => 3,
            'l_something' => 125,
        ));

        sleep(4); // Some DBs require some time to generate full-text index

        require_code('filtercode');

        $filter_tests = array();

        $filter_tests = array_merge(array(
            // Filtering on 'id'
            'id<2' => array(1),
            'id<=2' => array(1, 2),
            'id>2' => array(3),
            'id>=1' => array(1, 2, 3),
            'id=' => array(1, 2, 3), // because no filter
            'id=1' => array(1),
            //Depends on DB 'id==' => array(), // because nothing matches 0 after string coercion
            'id==1' => array(1),
            // No ~= for integers
            // No ~ for integers
            'id<>' => array(1, 2, 3), // because no filter
            'id<>1' => array(2, 3),
            //Depends on DB 'id!=' => array(1, 2, 3), // because nothing matches 0 after string coercion and negative
            'id!=1' => array(2, 3),
            'id@1-2' => array(1, 2),

            // Filtering on 't_short_text'
            // No < for strings
            // No <= for strings
            // No > for strings
            // No >= for strings
            't_short_text=' => array(1, 2, 3), // because no filter
            't_short_text=axxxxx' => array(1),
            't_short_text==' => array(), // because nothing matches
            't_short_text==axxxxx' => array(1),
            't_short_text~=ax' => array(1),
            't_short_text~axxxxx' => array(1),
            't_short_text<>' => array(1, 2, 3), // because no filter
            't_short_text<>axxxxx' => array(2, 3),
            't_short_text!=' => array(1, 2, 3), // because nothing matches and negative
            't_short_text!=axxxxx' => array(2, 3),
            // No @ for strings
        ));

        // Filtering on 't_member' using IDs
        $filter_tests['t_member<' . strval($admin_id)] = array($guest_id);
        $filter_tests['t_member<=' . strval($admin_id)] = array($guest_id, $admin_id);
        $filter_tests['t_member>' . strval($admin_id)] = array($test_id);
        $filter_tests['t_member>=' . strval($guest_id)] = array($guest_id, $admin_id, $test_id);
        $filter_tests['t_member='] = array($guest_id, $admin_id, $test_id); // because no filter
        $filter_tests['t_member=' . strval($guest_id)] = array($guest_id);
        //Depends on DB 't_member=='] = array(); // because nothing matches 0 after string coercion
        $filter_tests['t_member==' . strval($guest_id)] = array($guest_id);
        // No ~= for integers
        // No ~ for integers
        $filter_tests['t_member<>'] = array($guest_id, $admin_id, $test_id); // because no filter
        $filter_tests['t_member<>' . strval($guest_id)] = array($admin_id, $test_id);
        //Depends on DB 't_member!='] = array($guest_id, $admin_id, $test_id); // because nothing matches 0 after string coercion and negative
        $filter_tests['t_member!=' . strval($guest_id)] = array($admin_id, $test_id);
        $filter_tests['t_member@' . strval($guest_id) . '-' . strval($admin_id)] = array($guest_id, $admin_id);

        // Filtering on 't_member' using usernames
        $filter_tests['t_member=' . $guest_username] = array($guest_id);
        $filter_tests['t_member==' . $guest_username] = array($guest_id);
        $filter_tests['t_member~=' . $guest_username] = array($guest_id);
        $filter_tests['t_member~' . $guest_username] = array($guest_id);
        $filter_tests['t_member<>' . $guest_username] = array($admin_id, $test_id);
        $filter_tests['t_member!=' . $guest_username] = array($admin_id, $test_id);

        $filter_tests = array_merge(array(
            // Filtering on 't_real'
            't_real<2.0' => array(1),
            't_real<=2.0' => array(1, 2),
            't_real>2.0' => array(3),
            't_real>=1.1' => array(2, 3),
            't_real=' => array(1, 2, 3), // because no filter
            't_real=1.0' => array(1),
            //Depends on DB 't_real==' => array(), // because nothing matches 0.0 after string coercion
            't_real==1.0' => array(1),
            // No ~= for floats
            // No ~ for floats
            't_real<>' => array(1, 2, 3), // because no filter
            't_real<>1.0' => array(2, 3),
            //Depends on DB 't_real!=' => array(1, 2, 3), // because nothing matches 0.0 after string coercion and negative
            't_real!=1' => array(2, 3),
            't_real@1.1-2.0' => array(2),

            // Filtering on 't_time'
            't_time<2000000' => array(1),
            't_time<=2000000' => array(1, 2),
            't_time>2000000' => array(3),
            't_time>=1100000' => array(2, 3),
            't_time=' => array(1, 2, 3), // because no filter
            't_time=1000000' => array(1),
            //Depends on DB 't_time==' => array(), // because nothing matches 0 after string coercion
            't_time==1000000' => array(1),
            // No ~= for integers
            // No ~ for integers
            't_time<>' => array(1, 2, 3), // because no filter
            't_time<>1000000' => array(2, 3),
            //Depends on DB 't_time!=' => array(1, 2, 3), // because nothing matches 0 after string coercion and negative
            't_time!=1000000' => array(2, 3),
            't_time@1000000-2000000' => array(1, 2),

            // Filtering on 't_language_name'
            // No < for strings
            // No <= for strings
            // No > for strings
            // No >= for strings
            't_language_name=' => array(1, 2, 3), // because no filter
            't_language_name=EN' => array(1),
            't_language_name==' => array(), // because nothing matches
            't_language_name==EN' => array(1),
            't_language_name~=EN' => array(1),
            't_language_name~EN' => array(1),
            't_language_name<>' => array(1, 2, 3), // because no filter
            't_language_name<>EN' => array(2, 3),
            't_language_name!=' => array(1, 2, 3), // because nothing matches and negative
            't_language_name!=EN' => array(2, 3),
            // No @ for strings

            // Filtering on 't_id_text'
            // No < for strings
            // No <= for strings
            // No > for strings
            // No >= for strings
            't_id_text=' => array(1, 2, 3), // because no filter
            't_id_text=axxxxx' => array(1),
            't_id_text==' => array(), // because nothing matches
            't_id_text==axxxxx' => array(1),
            't_id_text~=axxxxx' => array(1),
            't_id_text~axxxxx' => array(1),
            't_id_text<>' => array(1, 2, 3), // because no filter
            't_id_text<>axxxxx' => array(2, 3),
            't_id_text!=' => array(1, 2, 3), // because nothing matches and negative
            't_id_text!=axxxxx' => array(2, 3),
            // No @ for strings

            // Filtering on 't_binary'
            't_binary<1' => array(1),
            't_binary<=1' => array(1, 2, 3),
            't_binary>0' => array(2, 3),
            't_binary>=0' => array(1, 2, 3),
            't_binary=' => array(1, 2, 3), // because no filter
            't_binary=0' => array(1),
            //Depends on DB 't_binary==' => array(1), // because 0 matches after string coercion
            't_binary==0' => array(1),
            // No ~= for integers
            // No ~ for integers
            't_binary<>' => array(1, 2, 3), // because no filter
            't_binary<>0' => array(2, 3),
            //Depends on DB 't_binary!=' => array(2, 3), // because 0 matches after string coercion and negative
            't_binary!=0' => array(2, 3),
            't_binary@0-1' => array(1, 2, 3),

            // Filtering on nothing
            '' => array(1, 2, 3),
            'junk' => array(1, 2, 3),

            // Filtering on multiple clauses
            'id=1,id=2' => array(),
            'id=1,t_binary=0' => array(1),

            // Filtering on multiple keys
            //Depends on DB 'id|t_id_text=1' => array(1),
            //Depends on DB 'id|t_id_text=axxxxx' => array(1),
            //Depends on DB 'id|t_id_text=zxxxxx' => array(),
            'id|t_member=4' => array(),

            // Filtering on multiple values
            'id=1|2' => array(1, 2),
            'id=4|5' => array(),
            't_id_text=axxxxx|bxxxxx' => array(1, 2),
            't_id_text#axxxxx|bxxxxx' => array(1, 2),
            't_id_text~=axxxxx|bxxxxx' => array(1, 2),
            't_id_text~axxxxx|bxxxxx' => array(1, 2),

            // Filtering on protected field
            't_secret=x' => array(1, 2, 3),
            't_secret=y' => array(1, 2, 3),

            // Filtering on rating
            'compound_rating=5' => array(1),
            'average_rating=1.75' => array(1),
            'fixed_random=123456789' => array(),

            // Filtering on SEO
            'meta_keywords~abc' => array(1),
            'meta_keywords~def' => array(1),
            'meta_keywords~ghi' => array(),
            'meta_keywords=' => array(1, 2, 3), // because no filter
            'meta_keywords==' => array(), // because nothing matches
            'meta_description=abc' => array(1),
            'meta_description=ghi' => array(),
            'meta_description=' => array(1, 2, 3), // because no filter
            'meta_description==' => array(), // because nothing matches

            // Linking tables
            '{t_linker=temp_test_linked.id},temp_test_linked.l_something=123' => array(1),
        ));

        foreach ($filter_tests as $filter => $filter_expected) {
            unset($filter_tests[$filter]);
            $filter = str_replace('Guest', $guest_username, $filter);
            $filter_tests[$filter] = $filter_expected;
        }

        foreach ($filter_tests as $filter => $filter_expected) {
            list($extra_select, $extra_join, $extra_where) = filtercode_to_sql($GLOBALS['SITE_DB'], parse_filtercode($filter), 'temp_test');
            $sql = 'SELECT r.id' . implode('', $extra_select) . ' FROM ' . get_table_prefix() . 'temp_test r' . implode('', $extra_join) . ' WHERE 1=1' . $extra_where;
            $results = collapse_1d_complexity('id', $GLOBALS['SITE_DB']->query($sql));
            $this->assertTrue($results == $filter_expected, 'Failed Filtercode check for: ' . $filter . ', got: ' . implode(',', array_map('strval', $results)) . '; query: ' . $sql);
        }

        // Test using POST environment
        $filter_tests = array(
            array('id<id_op><id>', 'id', '<', '2', array(1)),
            array('id<id_op><id>', 'id', '@', '1-2', array(1, 2)),
            array('t_short_text<t_short_text_op><t_short_text>', 't_short_text', '=', 'axxxxx', array(1)),
            array('t_member<t_member_op><t_member>', 't_member', '=', $guest_username, array(1)),
            array('t_real<t_real_op><t_real>', 't_real', '>=', '1.1', array(2, 3)),
            array('t_real<t_real_op><t_real>', 't_real', '@', '1.1-2.0', array(2)),
            array('t_time<t_time_op><t_time>', 't_time', '=', '2000000', array(2)),
            array('t_binary<t_binary_op><t_binary>', 't_binary', '>', '0', array(2, 3)),
        );
        foreach ($filter_tests as $filter_parts) {
            list($filter, $key, $op, $val, $filter_expected) = $filter_parts;

            if ($val == 2000000) {
                $_POST['filter_' . $key . '_day'] = 24;
                $_POST['filter_' . $key . '_month'] = 1;
                $_POST['filter_' . $key . '_year'] = 1970;
                $_POST['filter_' . $key . '_hour'] = 3;
                $_POST['filter_' . $key . '_minute'] = 33;
                $_POST['filter_' . $key . '_seconds'] = 20;
                $_POST['timezone'] = 'UTC';
            } else {
                $_POST['filter_' . $key] = $val;
            }

            $_POST[$key . '_op'] = $op;

            list($extra_select, $extra_join, $extra_where) = filtercode_to_sql($GLOBALS['SITE_DB'], parse_filtercode($filter), 'temp_test');
            $sql = 'SELECT r.id' . implode('', $extra_select) . ' FROM ' . get_table_prefix() . 'temp_test r' . implode('', $extra_join) . ' WHERE 1=1' . $extra_where;
            $results = collapse_1d_complexity('id', $GLOBALS['SITE_DB']->query($sql));
            $this->assertTrue($results == $filter_expected, 'Failed Filtercode check for: ' . $filter . ', got: ' . implode(',', array_map('strval', $results)) . ', expected: ' . implode(',', array_map('strval', $filter_expected)) . ', with: ' . $sql);
        }

        // Test automatic filter form seems okay
        list($form_fields, $filter, $_links) = form_for_filtercode('', array(), 'temp_test');
        $this->assertTrue(strpos($form_fields->evaluate(), '<input') !== false);
        $filter_expected = 'id<id_op><id>,t_binary<t_binary_op><t_binary>,t_id_text<t_id_text_op><t_id_text>,t_linker<t_linker_op><t_linker>,t_member<t_member_op><t_member>,t_real<t_real_op><t_real>,t_short_text<t_short_text_op><t_short_text>,t_short_trans<t_short_trans_op><t_short_trans>,t_time<t_time_op><t_time>,compound_rating<compound_rating_op><compound_rating>,average_rating<average_rating_op><average_rating>,meta_keywords<meta_keywords_op><meta_keywords>,meta_description<meta_description_op><meta_description>';
        $this->assertTrue($filter == $filter_expected);
    }

    protected $ids_and_parents;
    protected $selectcode;
    protected $expected;
    protected $expected_lazy;
    protected $expected_full;

    public function get_ids_and_parents()
    {
        return $this->ids_and_parents;
    }

    public function testSelectcode()
    {
        $only = get_param_string('only', null);
        if (($only !== null) && ($only !== 'testSelectcode')) {
            return;
        }

        $this->cleanup_db();

        $GLOBALS['SITE_DB']->create_table('temp_test_categories', array(
            'id' => '*INTEGER',
            'parent_id_of_cat' => 'INTEGER',
        ));
        $GLOBALS['SITE_DB']->create_table('temp_test_entries', array(
            'id' => '*INTEGER',
            'parent_id' => 'INTEGER',
        ));

        $this->ids_and_parents = array(
            1 => 2,
            2 => 2,
            3 => 2,
            4 => 3,
            5 => 3,
            6 => 3,
            7 => 3,
            8 => 3,
            100 => 12,
            101 => 13,
        );

        foreach (array_unique($this->ids_and_parents) as $parent_id) {
            $GLOBALS['SITE_DB']->query_insert('temp_test_categories', array(
                'id' => $parent_id,
                'parent_id_of_cat' => ($parent_id == 13) ? 12 : 1,
            ));
        }

        foreach ($this->ids_and_parents as $id => $parent_id) {
            $GLOBALS['SITE_DB']->query_insert('temp_test_entries', array(
                'id' => $id,
                'parent_id' => $parent_id,
            ));
        }

        require_code('selectcode');

        // Test scenario 1
        // ---------------

        $this->selectcode = '1,3-10,!6,12*';
        $this->expected_lazy = array(1, 3, 4, 5, 7, 8, 9, 10, 100, 101);
        $this->expected_full = array(1, 3, 4, 5, 7, 8, 100, 101);

        $sql = selectcode_to_sqlfragment($this->selectcode, 'id', 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        $results = collapse_1d_complexity('id', $GLOBALS['SITE_DB']->query_select('temp_test_entries', array('id'), array(), 'WHERE ' . $sql));
        sort($results);
        $this->assertTrue($results == $this->expected_full, 'Failed on this Selectcode: '. $this->selectcode . ' / ' . $sql);

        $results = selectcode_to_idlist_using_db($this->selectcode, 'id', 'temp_test_entries', 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected_lazy, 'Failed on this Selectcode: '. $this->selectcode);

        $results = selectcode_to_idlist_using_memory($this->selectcode, $this->ids_and_parents, 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected_full, 'Failed on this Selectcode: '. $this->selectcode);

        $results = selectcode_to_idlist_using_callback($this->selectcode, array(array($this, 'get_ids_and_parents'), array()), 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected_lazy, 'Failed on this Selectcode: '. $this->selectcode);

        // Test scenario 2
        // ---------------

        $this->selectcode = '*,!1';
        $this->expected = array(2, 3, 4, 5, 6, 7, 8, 100, 101);

        $sql = selectcode_to_sqlfragment($this->selectcode, 'id', 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        $results = collapse_1d_complexity('id', $GLOBALS['SITE_DB']->query_select('temp_test_entries', array('id'), array(), 'WHERE ' . $sql));
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode . ' / ' . $sql);

        $results = selectcode_to_idlist_using_db($this->selectcode, 'id', 'temp_test_entries', 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode);

        $results = selectcode_to_idlist_using_memory($this->selectcode, $this->ids_and_parents, 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode);

        $results = selectcode_to_idlist_using_callback($this->selectcode, array(array($this, 'get_ids_and_parents'), array()), 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode);

        // Test scenario 3
        // ---------------

        $this->selectcode = '8+';
        $this->expected = array(8, 100, 101);

        $sql = selectcode_to_sqlfragment($this->selectcode, 'id', 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        $results = collapse_1d_complexity('id', $GLOBALS['SITE_DB']->query_select('temp_test_entries', array('id'), array(), 'WHERE ' . $sql));
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode . ' / ' . $sql);

        $results = selectcode_to_idlist_using_db($this->selectcode, 'id', 'temp_test_entries', 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode);

        $results = selectcode_to_idlist_using_memory($this->selectcode, $this->ids_and_parents, 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode);

        $results = selectcode_to_idlist_using_callback($this->selectcode, array(array($this, 'get_ids_and_parents'), array()), 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode);

        // Test scenario 4
        // ---------------

        $this->selectcode = '2#';
        $this->expected = array(1, 2, 3);

        $sql = selectcode_to_sqlfragment($this->selectcode, 'id', 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        $results = collapse_1d_complexity('id', $GLOBALS['SITE_DB']->query_select('temp_test_entries', array('id'), array(), 'WHERE ' . $sql));
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode . ' / ' . $sql);

        $results = selectcode_to_idlist_using_db($this->selectcode, 'id', 'temp_test_entries', 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode);

        $results = selectcode_to_idlist_using_memory($this->selectcode, $this->ids_and_parents, 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode);

        $results = selectcode_to_idlist_using_callback($this->selectcode, array(array($this, 'get_ids_and_parents'), array()), 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode);

        // Test scenario 5
        // ---------------

        $this->selectcode = '12>';
        $this->expected = array(101);

        $sql = selectcode_to_sqlfragment($this->selectcode, 'id', 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        $results = collapse_1d_complexity('id', $GLOBALS['SITE_DB']->query_select('temp_test_entries', array('id'), array(), 'WHERE ' . $sql));
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode . ' / ' . $sql);

        $results = selectcode_to_idlist_using_db($this->selectcode, 'id', 'temp_test_entries', 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode);

        $results = selectcode_to_idlist_using_memory($this->selectcode, $this->ids_and_parents, 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode);

        $results = selectcode_to_idlist_using_callback($this->selectcode, array(array($this, 'get_ids_and_parents'), array()), 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode);

        // Test scenario 6
        // ---------------

        $this->selectcode = '12*,13~';
        $this->expected = array(100);

        $sql = selectcode_to_sqlfragment($this->selectcode, 'id', 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        $results = collapse_1d_complexity('id', $GLOBALS['SITE_DB']->query_select('temp_test_entries', array('id'), array(), 'WHERE ' . $sql));
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode . ' / ' . $sql);

        $results = selectcode_to_idlist_using_db($this->selectcode, 'id', 'temp_test_entries', 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode);

        $results = selectcode_to_idlist_using_memory($this->selectcode, $this->ids_and_parents, 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode);

        $results = selectcode_to_idlist_using_callback($this->selectcode, array(array($this, 'get_ids_and_parents'), array()), 'temp_test_categories', 'parent_id_of_cat', 'parent_id', 'id');
        sort($results);
        $this->assertTrue($results == $this->expected, 'Failed on this Selectcode: '. $this->selectcode);
    }

    protected function cleanup_db()
    {
        $GLOBALS['SITE_DB']->drop_table_if_exists('temp_test_categories');
        $GLOBALS['SITE_DB']->drop_table_if_exists('temp_test_entries');

        $GLOBALS['SITE_DB']->drop_table_if_exists('temp_test');
        $GLOBALS['SITE_DB']->drop_table_if_exists('temp_test_linked');

        $GLOBALS['SITE_DB']->query_delete('rating', array('rating_for_type' => 'temp_test__'));

        $GLOBALS['SITE_DB']->query_delete('seo_meta', array('meta_for_type' => 'temp_test'));
        $GLOBALS['SITE_DB']->query_delete('seo_meta_keywords', array('meta_for_type' => 'temp_test'));
    }

    public function tearDown()
    {
        if (get_param_integer('debug', 0) == 0) {
            $this->cleanup_db();
        }

        @unlink(get_file_base() . '/sources_custom/hooks/systems/content_meta_aware/temp_test.php');
        fix_permissions(get_file_base() . '/sources_custom/hooks/systems/content_meta_aware/temp_test.php');
        sync_file(get_file_base() . '/sources_custom/hooks/systems/content_meta_aware/temp_test.php');

        parent::tearDown();
    }
}
