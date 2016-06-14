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

/*
The version numbers here are not for interchange. sources/version2.php provides a way to turn these into the 'dotted' interchange format that the Composr platform recognises programatically.
*/

/**
 * Get the minor/patch version of your installation. This changes with each release, including bug fix releases. It generally consists of 'a' or 'a.b', where 'a' increments represent major changes and 'b' increments represent tiny changes or alpha/beta/RC numbering.
 *
 * @return string The minor/patch version number of your installation (e.g. 0.1 or 1.1 or RC1 or 1.RC1)
 */
function cms_version_minor()
{
    return 'RC12';
}

/**
 * Get the general version number of your installation (incorporates major and first [numeric] component of minor version number).
 *
 * @return float The general version number of your installation
 */
function cms_version_number()
{
    return 10.0;
}

/**
 * Get the timestamp at which this version was released.
 *
 * @return integer The timestamp at which this version was released.
 */
function cms_version_time()
{
    return 1211025869;
}

/**
 * Get the timestamp at which this MAJOR version was released.
 *
 * @return integer The timestamp at which this MAJOR version was released.
 */
function cms_version_time_major()
{
    return 1211025869;
}
