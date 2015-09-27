<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2015

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    commandr
 */

/**
 * Hook class.
 */
class Hook_commandr_command_mysql_table_sizes
{
    /**
     * Run function for Commandr hooks.
     *
     * @param  array $options The options with which the command was called
     * @param  array $parameters The parameters with which the command was called
     * @param  object $commandr_fs A reference to the Commandr filesystem object
     * @return array Array of stdcommand, stdhtml, stdout, and stderr responses
     */
    public function run($options, $parameters, &$commandr_fs)
    {
        if ((array_key_exists('h', $options)) || (array_key_exists('help', $options))) {
            return array('', do_command_help('mysql_table_sizes', array('h'), array(true, true)), '', '');
        } else {
            require_code('files');

            $out = '<div class="box box___commandr_box inline_block"><div class="box_inner"><div class="website_body">'; // XHTMLXHTML

            $db = $GLOBALS['SITE_DB'];
            require_code('files');
            $sizes = list_to_map('Name', $db->query('SHOW TABLE STATUS WHERE Name LIKE \'' . db_encode_like($db->get_table_prefix() . '%') . '\''));
            foreach ($sizes as $key => $vals) {
                $sizes[$key] = $vals['Data_length'] + $vals['Index_length'] - $vals['Data_free'];
            }
            asort($sizes);
            $out .= '<table class="results_table"><thead><tr><th>' . do_lang('NAME') . '</th><th>' . do_lang('SIZE') . '</th></tr></thead>';
            $out .= '<tbody>';
            foreach ($sizes as $key => $val) {
                $out .= '<tr><td>' . escape_html(preg_replace('#^' . preg_quote(get_table_prefix(), '#') . '#', '', $key)) . '</td><td>' . escape_html(clean_file_size($val)) . '</td></tr>';
            }
            $out .= '</tbody>';
            $out .= '</table>';

            if (count($parameters) != 0) {
                foreach ($parameters as $p) {
                    if (substr($p, 0, strlen(get_table_prefix())) == get_table_prefix()) {
                        $p = substr($p, strlen(get_table_prefix()));
                    }
                    $out .= '<h2>' . escape_html($p) . '</h2>';
                    if (array_key_exists(get_table_prefix() . $p, $sizes)) {
                        $num_rows = $db->query_select_value($p, 'COUNT(*)');
                        if ($num_rows > 0) {
                            $row = $db->query_select($p, array('*'), null, '', 1, mt_rand(0, $num_rows - 1));
                            $out .= '<table class="results_table"><tbody>';
                            $val = mixed();
                            foreach ($row[0] as $key => $val) {
                                if (!is_string($val)) {
                                    $val = strval($val);
                                }
                                $out .= '<tr><td>' . escape_html($key) . '</td><td>' . escape_html($val) . '</td></tr>';
                            }
                            $out .= '</tbody></table>';
                        } else {
                            $out .= '<p>' . do_lang('NONE') . '</p>';
                        }
                    } else {
                        $out .= '<p>' . do_lang('UNKNOWN') . '</p>';
                    }
                }
            }

            $out .= '</div></div></div>';

            return array('', $out, '', '');
        }
    }
}
