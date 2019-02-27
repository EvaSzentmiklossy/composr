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

/**
 * Remove CPF fields for GPS.
 * Assumes Conversr.
 */
function uninstall_gps_fields()
{
    $GLOBALS['FORUM_DRIVER']->install_delete_custom_field('latitude');
    $GLOBALS['FORUM_DRIVER']->install_delete_custom_field('longitude');
}

/**
 * Create CPF fields for GPS.
 * Assumes Conversr.
 */
function install_gps_fields()
{
    require_lang('cns');

    $GLOBALS['FORUM_DRIVER']->install_create_custom_field('latitude', 20, /*locked=*/0, /*viewable=*/0, /*settable=*/1, /*required=*/0, '', 'float');
    $GLOBALS['FORUM_DRIVER']->install_create_custom_field('longitude', 20, /*locked=*/0, /*viewable=*/0, /*settable=*/1, /*required=*/0, '', 'float');
}

/**
 * Remove CPF fields for names.
 * Assumes Conversr.
 */
function uninstall_name_fields()
{
    $GLOBALS['FORUM_DRIVER']->install_delete_custom_field('firstname');
    $GLOBALS['FORUM_DRIVER']->install_delete_custom_field('lastname');
}

/**
 * Create CPF fields for names.
 * Assumes Conversr.
 */
function install_name_fields()
{
    require_lang('cns');

    $GLOBALS['FORUM_DRIVER']->install_create_custom_field('firstname', 35, /*locked=*/0, /*viewable=*/1, /*settable=*/1, /*required=*/0, '', 'short_text');
    $GLOBALS['FORUM_DRIVER']->install_create_custom_field('lastname', 35, /*locked=*/0, /*viewable=*/1, /*settable=*/1, /*required=*/0, '', 'short_text');
}

/**
 * Remove CPF fields for address.
 * Assumes Conversr.
 */
function uninstall_address_fields()
{
    $GLOBALS['FORUM_DRIVER']->install_delete_custom_field('street_address');
    $GLOBALS['FORUM_DRIVER']->install_delete_custom_field('city');
    $GLOBALS['FORUM_DRIVER']->install_delete_custom_field('county');
    $GLOBALS['FORUM_DRIVER']->install_delete_custom_field('state');
    $GLOBALS['FORUM_DRIVER']->install_delete_custom_field('post_code');
    $GLOBALS['FORUM_DRIVER']->install_delete_custom_field('country');
}

/**
 * Create CPF fields for address.
 * Assumes Conversr.
 */
function install_address_fields()
{
    require_lang('cns');

    $GLOBALS['FORUM_DRIVER']->install_create_custom_field('street_address', 100, /*locked=*/0, /*viewable=*/0, /*settable=*/1, /*required=*/0, '', 'long_text'); // street address
    $GLOBALS['FORUM_DRIVER']->install_create_custom_field('city', 40, /*locked=*/0, /*viewable=*/0, /*settable=*/1, /*required=*/0, '', 'short_text');
    $GLOBALS['FORUM_DRIVER']->install_create_custom_field('county', 40, /*locked=*/0, /*viewable=*/0, /*settable=*/1, /*required=*/0, '', 'short_text');
    $GLOBALS['FORUM_DRIVER']->install_create_custom_field('state', 100, /*locked=*/0, /*viewable=*/0, /*settable=*/1, /*required=*/0, '', 'short_text');
    $GLOBALS['FORUM_DRIVER']->install_create_custom_field('post_code', 20, /*locked=*/0, /*viewable=*/0, /*settable=*/1, /*required=*/0, '', 'short_text');

    require_code('locations');
    $countries = '';
    $_countries = find_countries();
    foreach ($_countries as $code => $name) {
        $countries .= '|';
        $countries .= $code . '=' . $name;
    }
    $GLOBALS['FORUM_DRIVER']->install_create_custom_field('country', 5, /*locked=*/0, /*viewable=*/0, /*settable=*/1, /*required=*/0, '', 'list', 0, $countries);
}

/**
 * Remove CPF field for mobile phone.
 * Assumes Conversr.
 */
function uninstall_mobile_phone_field()
{
    $GLOBALS['FORUM_DRIVER']->install_delete_custom_field('mobile_phone_number');
}

/**
 * Create CPF field for mobile phone.
 * Assumes Conversr.
 */
function install_mobile_phone_field()
{
    require_lang('cns_special_cpf');
    $GLOBALS['FORUM_DRIVER']->install_create_custom_field('mobile_phone_number', 30, /*locked=*/0, /*viewable=*/0, /*settable=*/1, /*required=*/0, do_lang('SPECIAL_CPF__cms_mobile_phone_number_DESCRIPTION'), 'short_text');
}
