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
 * @package    import
 */

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__import()
{
    require_code('urls2');
}

/**
 * Load lots that the importer needs to run.
 */
function load_import_deps()
{
    require_all_lang();
    require_code('cns_groups');
    require_code('cns_members');
    require_code('cns_moderation_action');
    require_code('cns_posts_action');
    require_code('cns_polls_action');
    require_code('cns_members_action');
    require_code('cns_groups_action');
    require_code('cns_general_action');
    require_code('cns_forums_action');
    require_code('cns_topics_action');
    require_code('cns_moderation_action2');
    require_code('cns_posts_action2');
    require_code('cns_polls_action2');
    require_code('cns_members_action2');
    require_code('cns_groups_action2');
    require_code('cns_general_action2');
    require_code('cns_forums_action2');
    require_code('cns_topics_action2');
    require_css('importing');
    require_all_core_cms_code();
}

/**
 * Switch Conversr to run over the local site-DB connector. Useful when importing and our forum driver is actually connected to a forum other than Conversr.
 */
function cns_over_local()
{
    $GLOBALS['MSN_DB'] = $GLOBALS['FORUM_DB'];
    $GLOBALS['FORUM_DB'] = $GLOBALS['SITE_DB'];
}

/**
 * Undo cns_over_local.
 */
function cns_over_msn()
{
    $GLOBALS['FORUM_DB'] = $GLOBALS['MSN_DB'];
    $GLOBALS['MSN_DB'] = null;
}

/**
 * Returns the NEW ID of an imported old ID, for the specified importation type. Whether it returns null or gives an error message depends on $fail_ok.
 *
 * @param  ID_TEXT $type An importation type code, from those Composr has defined (E.g. 'download', 'news', ...)
 * @param  string $id_old The source (old, original) ID of the mapping
 * @param  boolean $fail_ok If it is okay to fail to find a mapping
 * @return ?AUTO_LINK The new ID (null: not found)
 */
function import_id_remap_get($type, $id_old, $fail_ok = false)
{
    static $remap_cache = array();
    if ((array_key_exists($type, $remap_cache)) && (array_key_exists($id_old, $remap_cache[$type]))) {
        return $remap_cache[$type][$id_old];
    }

    $value = $GLOBALS['SITE_DB']->query_select_value_if_there('import_id_remap', 'id_new', array('id_session' => get_session_id(), 'id_type' => $type, 'id_old' => $id_old));
    if ($value === null) {
        if ($fail_ok) {
            return null;
        }
        warn_exit(do_lang_tempcode('IMPORT_NOT_IMPORTED', escape_html($type), escape_html($id_old)));
    }
    $remap_cache[$type][$id_old] = $value;
    return $value;
}

/**
 * Check to see if the given ID of the given type has been imported (if it has a mapping).
 *
 * @param  ID_TEXT $type An importation type code, from those Composr has defined
 * @param  string $id_old The source (old, original) ID of the mapping
 * @return boolean Whether it has been imported
 */
function import_check_if_imported($type, $id_old)
{
    $test = $GLOBALS['SITE_DB']->query_select_value_if_there('import_id_remap', 'id_new', array('id_session' => get_session_id(), 'id_type' => $type, 'id_old' => $id_old));
    return $test !== null;
}

/**
 * Set the NEW ID for an imported old ID, which also tacitly indicates completion of importing an item of some type of content. This mapping (old ID to new ID) may be used later for importing related content that requires the new identifier. import_id_remap_get is the inverse of this function.
 *
 * @param  ID_TEXT $type An importation type code, from those Composr has defined
 * @param  string $id_old The source (old, original) ID of the mapping
 * @param  AUTO_LINK $id_new The destination (new) ID of the mapping
 */
function import_id_remap_put($type, $id_old, $id_new)
{
    $GLOBALS['SITE_DB']->query_insert('import_id_remap', array('id_session' => get_session_id(), 'id_type' => $type, 'id_old' => $id_old, 'id_new' => $id_new));
}

/**
 * Add a word to the word-filter.
 *
 * @param  SHORT_TEXT $word Word to add to the word-filter
 * @param  SHORT_TEXT $replacement Replacement (blank: block entirely)
 * @param  BINARY $substr Whether to perform a substring match
 */
function add_wordfilter_word($word, $replacement = '', $substr = 0)
{
    $test = $GLOBALS['SITE_DB']->query_select_value_if_there('wordfilter', 'word', array('word' => $word));
    if ($test === null) {
        $GLOBALS['SITE_DB']->query_insert('wordfilter', array('word' => $word, 'w_replacement' => $replacement, 'w_substr' => $substr));
    }
}

/**
 * Force a page refresh due to maximum execution timeout.
 */
function i_force_refresh()
{
    if (array_key_exists('I_REFRESH_URL', $GLOBALS)) {
        if ((strpos($GLOBALS['I_REFRESH_URL'], "\n") !== false) || (strpos($GLOBALS['I_REFRESH_URL'], "\r") !== false)) {
            log_hack_attack_and_exit('HEADER_SPLIT_HACK');
        }

        require_code('site2');
        redirect_exit($GLOBALS['I_REFRESH_URL']);
    }
}

/**
 * Cleanup after an import has finished.
 */
function post_import_cleanup()
{
    // Quick and simple decaching. No need to be smart about this.
    delete_value('cns_member_count');
    delete_value('cns_topic_count');
    delete_value('cns_post_count');
}

/**
 * Turn index maintenance off to help speed import, or back on.
 *
 * @param  boolean $on Whether index maintenance should be on
 */
function set_database_index_maintenance($on)
{
    if (strpos(get_db_type(), 'mysql') !== false) {
        push_db_scope_check(false);

        $tables = $GLOBALS['SITE_DB']->query_select('db_meta', array('DISTINCT m_table'));
        foreach ($tables as $table) {
            $tbl = $table['m_table'];
            $GLOBALS['SITE_DB']->query('ALTER TABLE ' . $GLOBALS['SITE_DB']->get_table_prefix() . $tbl . ' ' . ($on ? 'ENABLE' : 'DISABLE') . ' KEYS');
        }

        pop_db_scope_check();
    }
}
