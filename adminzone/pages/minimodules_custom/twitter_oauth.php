<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    twitter_support
 */

i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

$error_msg = new Tempcode();
if (!addon_installed__autoinstall('twitter_support', $error_msg)) {
    return $error_msg;
}

require_code('developer_tools');
destrictify();

require_code('twitter');
require_lang('twitter');

$title = get_screen_title('TWITTER_OAUTH');

$api_key = get_option('twitter_api_key');
$api_secret = get_option('twitter_api_secret');

if ($api_key == '' || $api_secret == '') {
    $config_url = build_url(array('page' => 'admin_config', 'type' => 'category', 'id' => 'COMPOSR_APIS', 'redirect' => protect_url_parameter(SELF_REDIRECT)), get_module_zone('admin_config'), array(), false, false, false, 'group_TWITTER_SYNDICATION');
    $echo = redirect_screen($title, $config_url, do_lang_tempcode('TWITTER_SETUP_FIRST'));
    $echo->evaluate_echo();
    return;
}

require_code('hooks/systems/syndication/twitter');
$ob = new Hook_syndication_twitter();

try {
    $result = $ob->auth_set(null, get_self_url(false, false, array('oauth_in_progress' => 1)));
}
catch (Exception $e) {
    warn_exit($e->getMessage());
}

if ($result) {
    $out = do_lang_tempcode('TWITTER_OAUTH_SUCCESS');
} else {
    $out = do_lang_tempcode('SOME_ERRORS_OCCURRED');
}

$title->evaluate_echo();

$out->evaluate_echo();
