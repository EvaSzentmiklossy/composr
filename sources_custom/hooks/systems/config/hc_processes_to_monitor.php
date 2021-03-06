<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    health_check
 */

/**
 * Hook class.
 */
class Hook_config_hc_processes_to_monitor
{
    /**
     * Gets the details relating to the config option.
     *
     * @return ?array The details (null: disabled)
     */
    public function get_details()
    {
        return array(
            'human_name' => 'HC_PROCESSES_TO_MONITOR',
            'type' => 'line',
            'category' => 'HEALTH_CHECK',
            'group' => 'PERFORMANCE',
            'explanation' => 'CONFIG_OPTION_hc_processes_to_monitor',
            'shared_hosting_restricted' => '0',
            'list_options' => '',
            'order_in_category_group' => 8,
            'required' => false,

            'addon' => 'health_check',
        );
    }

    /**
     * Gets the default value for the config option.
     *
     * @return ?string The default value (null: option is disabled)
     */
    public function get_default()
    {
        $processes = array(
            'php\d*(\.dSYM)?',
            'php\d*-cgi(\.dSYM)?',
            //'php\d*-fpm(\.dSYM)?',    Might be FastCGI
        );
        return implode('|', $processes);
    }
}
