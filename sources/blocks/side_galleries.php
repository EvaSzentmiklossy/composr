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
 * @package    galleries
 */

/**
 * Block class.
 */
class Block_side_galleries
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
        $info['version'] = 2;
        $info['locked'] = false;
        $info['parameters'] = array('param', 'depth', 'zone', 'show_empty', 'check');
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
        $info['cache_on'] = 'array(array_key_exists(\'depth\',$map)?intval($map[\'depth\']):0,array_key_exists(\'param\',$map)?$map[\'param\']:\'root\',array_key_exists(\'zone\',$map)?$map[\'zone\']:\'\',array_key_exists(\'show_empty\',$map)?($map[\'show_empty\']==\'1\'):false,array_key_exists(\'check\',$map)?($map[\'check\']==\'1\'):true)';
        $info['special_cache_flags'] = CACHE_AGAINST_DEFAULT | CACHE_AGAINST_PERMISSIVE_GROUPS;
        $info['ttl'] = (get_value('no_block_timeout') === '1') ? 60 * 60 * 24 * 365 * 5/*5 year timeout*/ : 60 * 2;
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
        require_lang('galleries');
        require_code('galleries');
        require_css('galleries');

        $block_id = get_block_id($map);

        $check_perms = array_key_exists('check', $map) ? ($map['check'] == '1') : true;

        $parent_id = empty($map['param']) ? 'root' : $map['param'];

        $zone = array_key_exists('zone', $map) ? $map['zone'] : get_module_zone('galleries');

        $show_empty = array_key_exists('show_empty', $map) ? ($map['show_empty'] == '1') : false;

        $depth = array_key_exists('depth', $map) ? intval($map['depth']) : 0; // If depth is 1 then we go down 1 level. Only 0 or 1 is supported.

        $extra_join_sql = '';
        $where_sup = '';
        if ((!$GLOBALS['FORUM_DRIVER']->is_super_admin(get_member())) && ($check_perms)) {
            $extra_join_sql .= get_permission_join_clause('gallery', 'cat');
            $where_sup .= get_permission_where_clause(get_member(), get_permission_where_clause_groups(get_member()));
        }

        // For all galleries off the root gallery
        $query = 'SELECT name,fullname FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'galleries' . $extra_join_sql;
        $query .= ' WHERE ' . db_string_equal_to('parent_id', $parent_id) . ' AND name NOT LIKE \'' . db_encode_like('download\_%') . '\'' . $where_sup;
        $query .= ' ORDER BY add_date';
        $galleries = $GLOBALS['SITE_DB']->query($query, 300 /*reasonable limit*/);
        if ($depth == 0) {
            $content = $this->inside($zone, $galleries, 'BLOCK_SIDE_GALLERIES_LINE', $show_empty);
        } else {
            $content = new Tempcode();

            foreach ($galleries as $gallery) {
                if (($show_empty) || (gallery_has_content($gallery['name']))) {
                    $subgalleries = $GLOBALS['SITE_DB']->query_select('galleries', array('name', 'fullname'), array('parent_id' => $gallery['name']), 'ORDER BY add_date', 300 /*reasonable limit*/);
                    $nest = $this->inside($zone, $subgalleries, 'BLOCK_SIDE_GALLERIES_LINE_DEPTH', $show_empty);
                    $caption = get_translated_text($gallery['fullname']);
                    $content->attach(do_template('BLOCK_SIDE_GALLERIES_LINE_CONTAINER', array('_GUID' => 'e50b84369b5e2146c4fab4fddc84bf0a', 'ID' => $gallery['name'], 'CAPTION' => $caption, 'CONTENTS' => $nest)));
                }
            }
        }

        $_title = $GLOBALS['SITE_DB']->query_select_value_if_there('galleries', 'fullname', array('name' => $parent_id));
        if ($_title !== null) {
            $title = get_translated_text($_title);
        } else {
            $title = '';
        }

        return do_template('BLOCK_SIDE_GALLERIES', array(
            '_GUID' => 'ed420ce9d1b1dde95eb3fd8473090228',
            'BLOCK_ID' => $block_id,
            'TITLE' => $title,
            'ID' => $parent_id,
            'DEPTH' => $depth != 0,
            'CONTENT' => $content,
        ));
    }

    /**
     * Show a group of subgalleries for use in a compact tree structure.
     *
     * @param  ID_TEXT $zone The zone our gallery module is in
     * @param  array $galleries A list of gallery rows
     * @param  ID_TEXT $tpl The template to use to show each subgallery
     * @param  boolean $show_empty Whether to show empty galleries
     * @return Tempcode The shown galleries
     */
    public function inside($zone, $galleries, $tpl, $show_empty)
    {
        $content = new Tempcode();

        foreach ($galleries as $gallery) {
            if (($show_empty) || (gallery_has_content($gallery['name']))) {
                $url = build_url(array('page' => 'galleries', 'type' => 'browse', 'id' => $gallery['name']), $zone);
                $content->attach(do_template($tpl, array('TITLE' => get_translated_text($gallery['fullname']), 'URL' => $url)));
            }
        }

        return $content;
    }
}
