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
 * @package    news
 */

/**
 * Block class.
 */
class Block_main_news_slider
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled)
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 1;
        $info['locked'] = false;
        $info['parameters'] = array('member_based', 'select', 'select_and', 'blogs', 'historic', 'zone', 'title', 'show_in_full', 'no_links', 'attach_to_url_filter', 'filter', 'start', 'max', 'interval', 'as_guest', 'optimise', 'check');
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
        $info['cache_on'] = <<<'PHP'
        array(
            array_key_exists('optimise', $map) ? $map['optimise'] : '0',
            array_key_exists('title', $map) ? escape_html($map['title']) : '(default title)',
            array_key_exists('as_guest', $map) ? ($map['as_guest'] == '1') : false,
            array_key_exists('start', $map) ? intval($map['start']) : 0,
            isset($map['max']) ? intval($map['max']) : 9,
            array_key_exists('filter', $map) ? $map['filter'] : '',
            array_key_exists('show_in_full', $map) ? $map['show_in_full'] : '0',
            (array_key_exists('attach_to_url_filter', $map) ? $map['attach_to_url_filter'] : '0') == '1',
            array_key_exists('no_links', $map) ? $map['no_links'] : 0, array_key_exists('title', $map) ? $map['title'] : '',
            array_key_exists('member_based', $map) ? $map['member_based'] : '0', array_key_exists('blogs', $map) ? $map['blogs'] : '-1',
            array_key_exists('historic', $map) ? $map['historic'] : '', 
            array_key_exists('select', $map) ? $map['select'] : '',
            array_key_exists('zone', $map) ? $map['zone'] : get_module_zone('news'),
            array_key_exists('select_and', $map) ? $map['select_and'] : '',
            array_key_exists('check', $map) ? ($map['check'] == '1') : true,
            !empty($map['interval']) ? intval($map['interval']) : 0,
        )
PHP;
        $info['special_cache_flags'] = CACHE_AGAINST_DEFAULT | CACHE_AGAINST_PERMISSIVE_GROUPS;
        if (addon_installed('content_privacy')) {
            $info['special_cache_flags'] |= CACHE_AGAINST_MEMBER;
        }
        $info['ttl'] = (get_value('disable_block_timeout') === '1') ? 60 * 60 * 24 * 365 * 5/*5 year timeout*/ : 60;
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
        if (!addon_installed__messaged('news', $error_msg)) {
            return $error_msg;
        }

        if (!addon_installed('news_shared')) {
            return do_template('RED_ALERT', array('_GUID' => 'a73d28b7a78540e89086cc806e382c80', 'TEXT' => do_lang_tempcode('MISSING_ADDON', escape_html('news_shared'))));
        }

        require_lang('cns');
        require_lang('news');
        require_css('news');
        require_code('news');
        require_code('images');

        $block_id = get_block_id($map);

        $check_perms = array_key_exists('check', $map) ? ($map['check'] == '1') : true;

        // Read in parameters
        $zone = isset($map['zone']) ? $map['zone'] : get_module_zone('news');
        $historic = isset($map['historic']) ? $map['historic'] : '';
        $filter = isset($map['filter']) ? $map['filter'] : '';
        $blogs = isset($map['blogs']) ? intval($map['blogs']) : -1;
        $member_based = (isset($map['member_based'])) && ($map['member_based'] == '1');
        $attach_to_url_filter = ((isset($map['attach_to_url_filter']) ? $map['attach_to_url_filter'] : '0') == '1');
        $optimise = (array_key_exists('optimise', $map)) && ($map['optimise'] == '1');
        $start = isset($map['start']) ? intval($map['start']) : 0;
        $max = isset($map['max']) ? intval($map['max']) : 9;
        // Slide change interval
        $interval = !empty($map['interval']) ? intval($map['interval']) : 0;

        // Read in news categories ahead, for performance
        global $NEWS_CATS_CACHE;
        if (!isset($NEWS_CATS_CACHE)) {
            $NEWS_CATS_CACHE = $GLOBALS['SITE_DB']->query_select('news_categories', array('*'), array('nc_owner' => null));
            $NEWS_CATS_CACHE = list_to_map('id', $NEWS_CATS_CACHE);
        }

        // News query
        $select = isset($map['select']) ? $map['select'] : '*';
        $select_and = isset($map['select_and']) ? $map['select_and'] : '';
        $q_filter = '1=1';
        if ($select != '*') {
            $q_filter .= ' AND ' . $this->generate_selectcode_sql($select);
        }
        if (($select_and != '') && ($select_and != '*')) {
            $q_filter .= ' AND ' . $this->generate_selectcode_sql($select_and);
        }
        if ($blogs === 0) {
            $q_filter .= ' AND nc_owner IS NULL';
        } elseif ($blogs === 1) {
            $q_filter .= ' AND (nc_owner IS NOT NULL)';
        }
        if ($blogs != -1) {
            $join = ' LEFT JOIN ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'news_categories c ON c.id=r.news_category';
        } else {
            $join = '';
        }

        // Filtercode
        if ($filter != '') {
            require_code('filtercode');
            list($filter_extra_select, $filter_extra_join, $filter_extra_where) = filtercode_to_sql($GLOBALS['SITE_DB'], parse_filtercode($filter), 'news');
            $extra_select_sql = implode('', $filter_extra_select);
            $join .= implode('', $filter_extra_join);
            $q_filter .= $filter_extra_where;
        } else {
            $extra_select_sql = '';
        }

        if (addon_installed('content_privacy')) {
            require_code('content_privacy');
            $as_guest = array_key_exists('as_guest', $map) ? ($map['as_guest'] == '1') : false;
            $viewing_member_id = $as_guest ? $GLOBALS['FORUM_DRIVER']->get_guest_id() : null;
            list($privacy_join, $privacy_where) = get_privacy_where_clause('news', 'r', $viewing_member_id);
            $join .= $privacy_join;
            $q_filter .= $privacy_where;
        }

        if (get_option('filter_regions') == '1') {
            require_code('locations');
            $q_filter .= sql_region_filter('news', 'r.id');
        }

        if ((!$GLOBALS['FORUM_DRIVER']->is_super_admin(get_member())) && ($check_perms)) {
            $join .= get_permission_join_clause('news', 'news_category');
            $q_filter .= get_permission_where_clause(get_member(), get_permission_where_clause_groups(get_member()));
        }

        // Read in rows
        if ($historic == '') {
            $rows = $GLOBALS['SITE_DB']->query('SELECT *,r.id AS p_id' . $extra_select_sql . ' FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'news r LEFT JOIN ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'news_category_entries d ON d.news_entry=r.id' . $join . ' WHERE ' . $q_filter . ((!has_privilege(get_member(), 'see_unvalidated')) ? ' AND validated=1' : '') . ($GLOBALS['DB_STATIC_OBJECT']->can_arbitrary_groupby() ? ' GROUP BY r.id' : '') . ' ORDER BY r.date_and_time DESC', $max, $start, false, false, array('title' => 'SHORT_TRANS', 'news' => 'LONG_TRANS', 'news_article' => 'LONG_TRANS'));
        } else {
            if (php_function_allowed('set_time_limit')) {
                @set_time_limit(100);
            }
            $rows = array();
            $search_start = 0;
            $okayed = 0;
            do {
                $_rows = $GLOBALS['SITE_DB']->query('SELECT *,r.id AS p_id' . $extra_select_sql . ' FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'news r LEFT JOIN ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'news_category_entries d ON r.id=d.news_entry' . $join . ' WHERE ' . $q_filter . ((!has_privilege(get_member(), 'see_unvalidated')) ? ' AND validated=1' : '') . ($GLOBALS['DB_STATIC_OBJECT']->can_arbitrary_groupby() ? ' GROUP BY r.id' : '') . ' ORDER BY r.date_and_time DESC', 200, $search_start, false, true);
                foreach ($_rows as $row) {
                    $ok = false;
                    switch ($historic) {
                        case 'month':
                            if ((date('m', utctime_to_usertime($row['date_and_time'])) == date('m', utctime_to_usertime())) && (date('Y', utctime_to_usertime($row['date_and_time'])) != date('Y', utctime_to_usertime()))) {
                                $ok = true;
                            }
                            break;

                        case 'week':
                            if ((date('W', utctime_to_usertime($row['date_and_time'])) == date('W', utctime_to_usertime())) && (date('Y', utctime_to_usertime($row['date_and_time'])) != date('Y', utctime_to_usertime()))) {
                                $ok = true;
                            }
                            break;

                        case 'day':
                            if ((date('d', utctime_to_usertime($row['date_and_time'])) == date('d', utctime_to_usertime())) && (date('m', utctime_to_usertime($row['date_and_time'])) == date('m', utctime_to_usertime())) && (date('Y', utctime_to_usertime($row['date_and_time'])) != date('Y', utctime_to_usertime()))) {
                                $ok = true;
                            }
                            break;
                    }
                    if ($ok) {
                        $okayed++;

                        if ($okayed > $start) {
                            $rows[] = $row;

                            if (count($rows) === $max) {
                                break 2;
                            }
                        }
                    }
                }
                $search_start += 200;
            } while ((count($_rows) === 200) && (count($rows) < $max));
            unset($_rows);
        }
        $rows = remove_duplicate_rows($rows, 'p_id');

        // Shared calculations
        $show_in_full = (isset($map['show_in_full'])) && ($map['show_in_full'] == '1');
        $show_author = (addon_installed('authors')) && (!$member_based);
        $prop_url = array();
        if ($attach_to_url_filter) {
            $prop_url += propagate_filtercode();
        }
        if ($select != '*') {
            $prop_url['select'] = $select;
        }
        if (($select_and != '*') && ($select_and != '')) {
            $prop_url['select_and'] = $select_and;
        }
        if ($blogs != -1) {
            $prop_url['blog'] = $blogs;
        }
        $allow_comments_shared = (get_option('is_on_comments') == '1') && (!has_no_forum());

        // Render loop
        $slide_items = array();
        foreach ($rows as $i => $news_row) {
            $just_news_row = db_map_restrict($news_row, array('id', 'title', 'news', 'news_article'));

            // Basic details
            $id = $news_row['p_id'];
            $date = get_timezoned_date_time_tempcode($news_row['date_and_time']);
            $news_title = get_translated_tempcode('news', $just_news_row, 'title');
            $news_title_plain = get_translated_text($news_row['title']);

            // Author
            $author_url = null;
            if ($show_author) {
                $url_map = array('page' => 'authors', 'type' => 'browse', 'id' => $news_row['author']);
                if ($attach_to_url_filter) {
                    $url_map += propagate_filtercode();
                }
                $author_url = build_url($url_map, get_module_zone('authors'));
            }
            $author = $news_row['author'];

            // Text
            $truncate = false;
            if ($optimise) {
                if ($show_in_full) {
                    $news_excerpt = get_translated_tempcode__and_simplify('news', $just_news_row, 'news_article');
                    if ($news_excerpt->is_empty()) {
                        $news_excerpt = get_translated_tempcode__and_simplify('news', $just_news_row, 'news');
                    }
                } else {
                    $news_excerpt = get_translated_tempcode__and_simplify('news', $just_news_row, 'news');
                    if ($news_excerpt->is_empty()) {
                        $news_excerpt = get_translated_tempcode__and_simplify('news', $just_news_row, 'news_article');
                        $truncate = true;
                    }
                }
            } else {
                if ($show_in_full) {
                    $news_excerpt = get_translated_tempcode('news', $just_news_row, 'news_article');
                    if ($news_excerpt->is_empty()) {
                        $news_excerpt = get_translated_tempcode('news', $just_news_row, 'news');
                    }
                } else {
                    $news_excerpt = get_translated_tempcode('news', $just_news_row, 'news');
                    if ($news_excerpt->is_empty()) {
                        $news_excerpt = get_translated_tempcode('news', $just_news_row, 'news_article');
                        $truncate = true;
                    }
                }
            }

            // URL
            $tmp = array('page' => ($zone == '_SELF' && running_script('index')) ? get_page_name() : 'news', 'type' => 'view', 'id' => $id) + $prop_url;
            $full_url = build_url($tmp, $zone);

            // Category
            if (!isset($NEWS_CATS_CACHE[$news_row['news_category']])) {
                $_news_cats = $GLOBALS['SITE_DB']->query_select('news_categories', array('*'), array('id' => $news_row['news_category']), '', 1);
                if (isset($_news_cats[0])) {
                    $NEWS_CATS_CACHE[$news_row['news_category']] = $_news_cats[0];
                } else {
                    $news_row['news_category'] = db_get_first_id();
                }
            }
            $news_cat_row = $NEWS_CATS_CACHE[$news_row['news_category']];

            $category = get_translated_text($news_cat_row['nc_title']);
            $category_url = null;

            if ($news_row['news_image'] != '') {
                $img = $news_row['news_image'];
                if (url_is_local($img)) {
                    $img = get_custom_base_url() . '/' . $img;
                }
            } else {
                $img = get_news_category_image_url($news_cat_row['nc_img']);
            }

            $img_large = null;
            if (!empty($img)) {
                if (substr($img, -4) === '.svg') {
                    $img_large = $img;
                } elseif ((substr($img, -4) === '.png') && starts_with($img, get_custom_base_url() . '/') && file_exists(substr($img, strlen(get_custom_base_url() . '/'), -4))) {
                    $img_large = substr($img, 0, -4); //(e.g., bridge.jpg.png -> bridge.jpg)
                }
            }

            // Render
            $slide_item = array(
                'ID' => strval($id),
                'BLOG' => $blogs === 1,
                'SUBMITTER' => strval($news_row['submitter']),
                'CATEGORY' => $category,
                'CATEGORY_URL' => $category_url,
                'IMG' => $img,
                'IMG_LARGE' => $img_large,
                'DATE' => $date,
                'DATE_RAW' => strval($news_row['date_and_time']),
                'NEWS_TITLE' => $news_title,
                'NEWS_TITLE_PLAIN' => $news_title_plain,
                'AUTHOR' => $author,
                'AUTHOR_URL' => $author_url,
                'SUMMARY' => $news_excerpt,
                'TRUNCATE' => $truncate,
                'FULL_URL' => $full_url,
            );
            if ($allow_comments_shared && ($news_row['allow_comments'] >= 1)) {
                $slide_item['COMMENT_COUNT'] = '1';
            }

            $slide_items[] = $slide_item;
        }

        $slides = new Tempcode();
        $slides_count = 0;

        for ($i = 0; $i < count($slide_items); $i += 3) {
            $news_items = array($slide_items[$i]);
            $items_count = 1;

            if (isset($slide_items[$i + 1])) {
                $news_items[] = $slide_items[$i + 1];
                $items_count++;
            }

            if (isset($slide_items[$i + 2])) {
                $news_items[] = $slide_items[$i + 2];
                $items_count++;
            }

            $slides->attach(do_template('BLOCK_MAIN_NEWS_SLIDER_SLIDE', array(
                '_GUID' => '854c5f329ba048968b307d2944f6c061',
                'BLOCK_ID' => $block_id,
                'ACTIVE' => $i === 0,
                'ITEMS_COUNT' => strval($items_count),
                'NEWS_ITEMS' => $news_items,
            )));
            $slides_count++;
        }

        return do_template('BLOCK_MAIN_NEWS_SLIDER', array(
            '_GUID' => '01f5fbd2b0c7c8f249023ecb4254366e',
            'BLOCK_ID' => $block_id,
            'BLOG' => $blogs === 1,
            'SLIDES' => $slides,
            'SLIDES_COUNT' => strval($slides_count),
            'SLIDES_COUNT_ARRAY' => ($slides_count > 1) ? range(1, $slides_count) : null,
            'INTERVAL' => strval($interval),
        ));
    }

    /**
     * Generate Selectcode SQL.
     *
     * @param  string $select The Selectcode
     * @return string The SQL
     */
    protected function generate_selectcode_sql($select)
    {
        require_code('selectcode');
        $selects_1 = selectcode_to_sqlfragment($select, 'r.id', 'news_categories', null, 'r.news_category', 'id');
        $selects_2 = selectcode_to_sqlfragment($select, 'r.id', 'news_categories', null, 'd.news_entry_category', 'id');
        if ((strpos($select, '~') === false) && (strpos($select, '!') === false)) {
            $q_filter = '(' . $selects_1 . ' OR ' . $selects_2 . ')';
        } else {
            $q_filter = '(' . $selects_1 . ' AND (' . $selects_2 . ' OR d.news_entry_category IS NULL))';
        }
        return $q_filter;
    }
}
