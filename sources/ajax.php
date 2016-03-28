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
 * @package    core
 */

/*EXTRA FUNCTIONS: simplexml_load_string*/

/**
 * Prepare to inject COR headers.
 *
 * @ignore
 */
function cor_prepare()
{
    require_code('input_filter');
    $allowed_partners = get_allowed_partner_sites();
    if (in_array(preg_replace('#^.*://([^:/]*).*$#', '${1}', $_SERVER['HTTP_ORIGIN']), $allowed_partners)) {
        header('Access-Control-Allow-Origin: ' . str_replace("\n", '', str_replace("\r", '', $_SERVER['HTTP_ORIGIN'])));

        if ((isset($_SERVER['REQUEST_METHOD'])) && ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')) {
            header('Access-Control-Allow-Credentials: true');

            // Send pre-flight response
            if (isset($_SERVER['ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header('Access-Control-Allow-Headers: ' . str_replace("\n", '', str_replace("\r", '', $_SERVER['ACCESS_CONTROL_REQUEST_HEADERS'])));
            }
            $methods = 'GET,POST,PUT,HEAD,OPTIONS';
            if (isset($_SERVER['ACCESS_CONTROL_REQUEST_HEADERS'])) {
                $methods .= str_replace("\n", '', str_replace("\r", '', $_SERVER['ACCESS_CONTROL_REQUEST_METHOD']));
            }
            header('Access-Control-Allow-Methods: ' . $methods);

            exit();
        }
    }
}

/**
 * Script to generate a Flash crossdomain file.
 *
 * @ignore
 */
function crossdomain_script()
{
    prepare_for_known_ajax_response();

    require_code('xml');

    header('Content-Type: text/xml');

    echo '<' . '?xml version="1.0"?' . '>
<!DOCTYPE cross-domain-policy SYSTEM "http://www.macromedia.com/xml/dtds/cross-domain-policy.dtd">
<cross-domain-policy>';
    require_code('input_filter');
    $allowed_partners = get_allowed_partner_sites();
    foreach ($allowed_partners as $post_submitter) {
        $post_submitter = trim($post_submitter);
        if ($post_submitter != '') {
            echo '<allow-access-from domain="' . xmlentities($post_submitter) . '" />';
        }
    }
    echo '
</cross-domain-policy>';
}

/**
 * AJAX script for checking if a new username is valid.
 *
 * @ignore
 */
function username_check_script()
{
    prepare_for_known_ajax_response();

    header('Content-type: text/plain; charset=' . get_charset());

    require_code('cns_members_action');
    require_code('cns_members_action2');
    require_lang('cns');

    $username = post_param_string('username', null, true);
    if (!is_null($username)) {
        $username = trim($username);
    }
    $password = post_param_string('password', null);
    if (!is_null($password)) {
        $password = trim($password);
    }
    $error = cns_check_name_valid($username, null, $password, true);
    if (!is_null($error)) {
        $error->evaluate_echo();
    }
}

/**
 * AJAX script for checking if a username exists.
 *
 * @ignore
 */
function username_exists_script()
{
    prepare_for_known_ajax_response();

    header('Content-type: text/plain; charset=' . get_charset());

    $username = trim(get_param_string('username', false, true));
    $member_id = $GLOBALS['FORUM_DRIVER']->get_member_from_username($username);
    if (is_null($member_id)) {
        echo 'false';
    }
}

/**
 * AJAX script for allowing username/author/search-terms home-in.
 *
 * @ignore
 */
function namelike_script()
{
    prepare_for_known_ajax_response();

    $id = str_replace('*', '%', get_param_string('id', false, true));
    $special = get_param_string('special', '');

    safe_ini_set('ocproducts.xss_detect', '0');

    header('Content-Type: text/xml');
    echo '<?xml version="1.0" encoding="' . get_charset() . '"?' . '>';
    echo '<request><result>';

    if ($special == 'admin_search') {
        $names = array();
        if ($id != '') {
            require_all_lang();
            $hooks = find_all_hooks('systems', 'page_groupings');
            foreach (array_keys($hooks) as $hook) {
                require_code('hooks/systems/page_groupings/' . filter_naughty_harsh($hook));
                $object = object_factory('Hook_page_groupings_' . filter_naughty_harsh($hook), true);
                if (is_null($object)) {
                    continue;
                }
                $info = $object->run();
                foreach ($info as $i) {
                    if (is_null($i)) {
                        continue;
                    }
                    $n = $i[3];
                    $n_eval = is_object($n) ? $n->evaluate() : $n;
                    if ($n_eval == '') {
                        continue;
                    }
                    if ((stripos($n_eval, $id) !== false) && (has_actual_page_access(get_member(), $i[2][0], $i[2][2]))) {
                        $names[] = '"' . $n_eval . '"';
                    }
                }
            }
            if (count($names) > 10) {
                $names = array();
            }
            sort($names);
        }

        foreach ($names as $name) {
            echo '<option value="' . escape_html($name) . '" displayname="" />';
        }
    } elseif ($special == 'search') {
        require_code('search');
        $names = find_search_suggestions($id, get_param_string('search_type', ''));

        foreach ($names as $name) {
            echo '<option value="' . escape_html($name) . '" displayname="" />';
        }
    } else {
        if ((strlen($id) == 0) && (addon_installed('chat'))) {
            $rows = $GLOBALS['SITE_DB']->query_select('chat_friends', array('member_liked'), array('member_likes' => get_member()), 'ORDER BY date_and_time', 100);
            $names = array();
            foreach ($rows as $row) {
                $names[$row['member_liked']] = $GLOBALS['FORUM_DRIVER']->get_username($row['member_liked']);
            }

            foreach ($names as $name) {
                echo '<option value="' . escape_html($name) . '" displayname="" />';
            }
        } else {
            $names = array();
            if ((addon_installed('authors')) && ($special == 'author')) {
                $num_authors = $GLOBALS['SITE_DB']->query_select_value('authors', 'COUNT(*)');
                $like = ($num_authors < 1000) ? db_encode_like('%' . str_replace('_', '\_', $id) . '%') : db_encode_like(str_replace('_', '\_', $id) . '%'); // performance issue
                $rows = $GLOBALS['SITE_DB']->query('SELECT author FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'authors WHERE author LIKE \'' . $like . '\' ORDER BY author', 15);
                $names = collapse_1d_complexity('author', $rows);

                foreach ($names as $name) {
                    echo '<option value="' . escape_html($name) . '" displayname="" />';
                }
            } else {
                if ((!addon_installed('authors')) || ($special != 'author') || ($GLOBALS['FORUM_DRIVER']->get_members() < 5000)) {
                    $likea = $GLOBALS['FORUM_DRIVER']->get_matching_members($id . '%', 15);
                    if ((count($likea) == 15) && (addon_installed('chat')) && (!is_guest())) {
                        $likea = $GLOBALS['FORUM_DRIVER']->get_matching_members($id . '%', 15, true); // Limit to friends, if possible
                    }

                    foreach ($likea as $l) {
                        if (count($names) < 15) {
                            $names[$GLOBALS['FORUM_DRIVER']->mrow_id($l)] = $GLOBALS['FORUM_DRIVER']->mrow_username($l);
                        }
                    }
                }

                foreach ($names as $member_id => $name) {
                    echo '<option value="' . escape_html($name) . '" displayname="' . escape_html($GLOBALS['FORUM_DRIVER']->get_username($member_id, true)) . '" />';
                }
            }
        }

        sort($names);
        $names = array_unique($names);
    }

    echo '</result></request>';
}

/**
 * AJAX script for finding out privileges for the queried resource.
 *
 * @ignore
 */
function find_permissions_script()
{
    prepare_for_known_ajax_response();

    header('Content-type: text/plain; charset=' . get_charset());

    require_code('permissions2');

    $serverid = get_param_string('serverid');
    $x = get_param_string('x');
    $matches = array();
    preg_match('#^access_(\d+)_privilege_(.+)$#', $x, $matches);
    $group_id = intval($matches[1]);
    $privilege = $matches[2];
    require_all_lang();
    echo do_lang('PRIVILEGE_' . $privilege) . '=';
    if ($serverid == '_root') {
        echo has_privilege_group($group_id, $privilege) ? do_lang('YES') : do_lang('NO');
    } else {
        require_code('sitemap');

        $test = find_sitemap_object($serverid);
        if (!is_null($test)) {
            list($ob,) = $test;

            $privilege_page = $ob->get_privilege_page($serverid);
        } else {
            $privilege_page = '';
        }

        echo has_privilege_group($group_id, $privilege, $privilege_page) ? do_lang('YES') : do_lang('NO');
    }
}

/**
 * AJAX script to store an autosave.
 *
 * @ignore
 */
function store_autosave()
{
    prepare_for_known_ajax_response();

    $member_id = get_member();
    $time = time();

    foreach (array_keys($_POST) as $key) {
        $value = post_param_string($key);

        $GLOBALS['SITE_DB']->query_insert('autosave', array(
            // Will duplicate against a_member_id/a_key, but DB space is not an issue - better to have the back-archive of it
            'a_member_id' => $member_id,
            'a_key' => $key,
            'a_value' => $value,
            'a_time' => $time,
        ));
    }
}

/**
 * AJAX script to retrieve an autosave.
 *
 * @ignore
 */
function retrieve_autosave()
{
    prepare_for_known_ajax_response();

    header('Content-type: text/xml; charset=' . get_charset());

    $member_id = get_member();
    $stem = either_param_string('stem');

    require_code('xml');

    header('Content-Type: text/xml');
    echo '<?xml version="1.0" encoding="' . get_charset() . '"?' . '>';
    echo '<request><result>' . "\n";

    $rows = $GLOBALS['SITE_DB']->query_select(
        'autosave',
        array('a_key', 'a_value'),
        array('a_member_id' => $member_id),
        'AND a_key LIKE \'' . db_encode_like($stem) . '%\' ORDER BY a_time DESC'
    );

    $done = array();
    foreach ($rows as $row) {
        if (isset($done[$row['a_key']])) {
            continue;
        }
        echo '<field key="' . xmlentities($row['a_key']) . '" value="' . xmlentities($row['a_value']) . '" />' . "\n";
        $done[$row['a_key']] = true;
    }

    echo '</result></request>';
}

/**
 * AJAX script to make a fractional edit to some data.
 *
 * @ignore
 */
function fractional_edit_script()
{
    prepare_for_known_ajax_response();

    header('Content-type: text/plain; charset=' . get_charset());

    $_POST['fractional_edit'] = '1'; // FUDGE

    $zone = get_param_string('zone');
    $page = get_page_name();

    global $SESSION_CONFIRMED_CACHE;
    if ((!$SESSION_CONFIRMED_CACHE) && ($GLOBALS['SITE_DB']->query_select_value('zones', 'zone_require_session', array('zone_name' => $zone)) == 1)) {
        return;
    }

    if (!has_actual_page_access(get_member(), $page, $zone)) {
        access_denied('ZONE_ACCESS');
    }

    require_code('failure');
    global $WANT_TEXT_ERRORS;
    $WANT_TEXT_ERRORS = true;

    require_code('site');
    request_page($page, true);

    $supports_comcode = get_param_integer('supports_comcode', 0) == 1;
    $param_name = get_param_string('edit_param_name');
    if (isset($_POST[$param_name . '__altered_rendered_output'])) {
        $edited = $_POST[$param_name . '__altered_rendered_output'];
    } else {
        $edited = post_param_string($param_name);
        if ($supports_comcode) {
            $_edited = comcode_to_tempcode($edited, get_member());
            $edited = $_edited->evaluate();
        } else {
            $edited = escape_html($edited);
        }
    }
    safe_ini_set('ocproducts.xss_detect', '0');
    echo $edited;
}

/**
 * AJAX script to tell if data has been changed.
 *
 * @ignore
 */
function change_detection_script()
{
    prepare_for_known_ajax_response();

    header('Content-type: text/plain; charset=' . get_charset());

    $page = get_page_name();

    require_code('hooks/systems/change_detection/' . filter_naughty($page), true);

    $refresh_if_changed = either_param_string('refresh_if_changed');
    $object = object_factory('Hook_change_detection_' . $page);
    $result = $object->run($refresh_if_changed);
    echo $result ? '1' : '0';
}

/**
 * AJAX script for recording that something is currently being edited.
 *
 * @ignore
 */
function edit_ping_script()
{
    prepare_for_known_ajax_response();

    header('Content-type: text/plain; charset=' . get_charset());

    $GLOBALS['SITE_DB']->query('DELETE FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'edit_pings WHERE the_time<' . strval(time() - 200));

    $GLOBALS['SITE_DB']->query_delete('edit_pings', array(
        'the_page' => get_page_name(),
        'the_type' => get_param_string('type'),
        'the_id' => get_param_string('id', false, true),
        'the_member' => get_member()
    ));

    $GLOBALS['SITE_DB']->query_insert('edit_pings', array(
        'the_page' => get_page_name(),
        'the_type' => get_param_string('type'),
        'the_id' => get_param_string('id', false, true),
        'the_time' => time(),
        'the_member' => get_member()
    ));

    echo '1';
}

/**
 * AJAX script for dynamically extended selection tree.
 *
 * @ignore
 */
function ajax_tree_script()
{
    // Closed site
    $site_closed = get_option('site_closed');
    if (($site_closed == '1') && (!has_privilege(get_member(), 'access_closed_site')) && (!$GLOBALS['IS_ACTUALLY_ADMIN'])) {
        header('Content-type: text/plain; charset=' . get_charset());
        @exit(get_option('closed'));
    }

    prepare_for_known_ajax_response();

    // NB: We use ajax_tree hooks to power this. Those hooks may or may not use the Sitemap API to get the tree structure. However, the default ones are hard-coded, for better performance.

    require_code('xml');
    header('Content-Type: text/xml');
    $hook = filter_naughty_harsh(get_param_string('hook'));
    require_code('hooks/systems/ajax_tree/' . $hook);
    $object = object_factory('Hook_' . $hook);
    $id = get_param_string('id', '', true);
    if ($id == '') {
        $id = null;
    }
    safe_ini_set('ocproducts.xss_detect', '0');
    $html_mask = get_param_integer('html_mask', 0) == 1;
    if (!$html_mask) {
        echo '<?xml version="1.0" encoding="' . get_charset() . '"?' . '>';
    }
    echo($html_mask ? '<html>' : '<request>');
    $_options = get_param_string('options', '', true);
    if ($_options == '') {
        $_options = serialize(array());
    }
    secure_serialized_data($_options);
    $options = @unserialize($_options);
    if ($options === false) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }
    $val = $object->run($id, $options, get_param_string('default', null, true));
    echo str_replace('</body>', '<br id="ended" /></body>', $val);
    echo($html_mask ? '</html>' : '</request>');
}

/**
 * AJAX script for confirming a session is active.
 *
 * @ignore
 */
function confirm_session_script()
{
    prepare_for_known_ajax_response();

    safe_ini_set('ocproducts.xss_detect', '0');

    header('Content-type: text/plain; charset=' . get_charset());
    global $SESSION_CONFIRMED_CACHE;
    if (!$SESSION_CONFIRMED_CACHE) {
        echo $GLOBALS['FORUM_DRIVER']->get_username(get_member());
    }
    echo '';
}

/**
 * AJAX script for getting the text of a template, as used by a certain theme.
 *
 * @ignore
 */
function load_template_script()
{
    prepare_for_known_ajax_response();

    if (!has_actual_page_access(get_member(), 'admin_themes', 'adminzone')) {
        exit();
    }

    safe_ini_set('ocproducts.xss_detect', '0');

    $theme = filter_naughty(get_param_string('theme'));
    $id = filter_naughty(basename(get_param_string('id')));
    $directory = filter_naughty(get_param_string('directory', dirname(get_param_string('id'))));

    $x = get_custom_file_base() . '/themes/' . $theme . '/' . $directory . '_custom/' . $id;
    if (!file_exists($x)) {
        $x = get_file_base() . '/themes/' . $theme . '/' . $directory . '/' . $id;
    }
    if (!file_exists($x)) {
        $x = get_custom_file_base() . '/themes/default/' . $directory . '_custom/' . $id;
    }
    if (!file_exists($x)) {
        $x = get_file_base() . '/themes/default/' . $directory . '/' . $id;
    }

    if (file_exists($x)) {
        echo file_get_contents($x);
    }
}

/**
 * AJAX script for dynamic inclusion of CSS.
 *
 * @ignore
 */
function sheet_script()
{
    prepare_for_known_ajax_response();

    header('Content-Type: text/css');
    $sheet = get_param_string('sheet');
    if ($sheet != '') {
        echo str_replace('../../../', '', file_get_contents(css_enforce(filter_naughty_harsh($sheet))));
    }
}

/**
 * AJAX script for dynamic inclusion of XHTML snippets.
 *
 * @ignore
 */
function snippet_script()
{
    prepare_for_known_ajax_response();

    header('Content-Type: text/plain; charset=' . get_charset());
    convert_data_encodings(true);
    $hook = filter_naughty_harsh(get_param_string('snippet'));
    require_code('hooks/systems/snippets/' . $hook, true);
    $object = object_factory('Hook_snippet_' . $hook);
    $tempcode = $object->run();
    $tempcode->handle_symbol_preprocessing();
    $out = $tempcode->evaluate();

    if ((strpos($out, "\n") !== false) && (strpos($hook, '__text') === false)) { // Is HTML
        if ((!function_exists('simplexml_load_string')) || ((function_exists('simplexml_load_string')) && (@simplexml_load_string('<wrap>' . preg_replace('#&\w+;#', '', $out) . '</wrap>') === false))) { // Optimisation-- check first via optimised native PHP function if possible
            require_code('xhtml');
            $out = xhtmlise_html($out, true);
        }
    }

    echo $out;
}
