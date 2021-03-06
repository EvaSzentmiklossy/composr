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
 * @package    commandr
 */

/**
 * Hook class.
 */
class Hook_commandr_command_db_search
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
            return array('', do_command_help('db_search', array('h'), array(true, true)), '', '');
        } else {
            if (!array_key_exists(0, $parameters)) {
                return array('', '', '', do_lang('MISSING_PARAM', '1', 'db_search'));
            }
            if (!array_key_exists(1, $parameters)) {
                return array('', '', '', do_lang('MISSING_PARAM', '2', 'db_search'));
            }

            $value = $parameters[0];

            $out = '';

            $i = 1;
            $fields = array();
            while (array_key_exists($i, $parameters)) {
                $type = $parameters[$i];
                $fields = array_merge(
                    $fields,
                    $GLOBALS['SITE_DB']->query_select('db_meta', array('m_name', 'm_table'), array('m_type' => $type)),
                    $GLOBALS['SITE_DB']->query_select('db_meta', array('m_name', 'm_table'), array('m_type' => '?' . $type)),
                    $GLOBALS['SITE_DB']->query_select('db_meta', array('m_name', 'm_table'), array('m_type' => '*' . $type))
                );
                $i++;
            }
            if (count($fields) == 0) {
                $fields = $GLOBALS['SITE_DB']->query_select('db_meta', array('m_name', 'm_table'));
            }
            foreach ($fields as $field) {
                $db = ((substr($field['m_table'], 0, 2) == 'f_') && ($field['m_table'] != 'f_welcome_emails') && ($GLOBALS['FORUM_DB'] !== null)) ? $GLOBALS['FORUM_DB'] : $GLOBALS['SITE_DB'];
                $ofs = $db->query_select($field['m_table'], array('*'), array($field['m_name'] => $value));
                foreach ($ofs as $of) {
                    $out .= '<h2>' . escape_html($field['m_table']) . '</h2>';

                    $out .= '<table class="results_table">';
                    $val = mixed();
                    foreach ($of as $key => $val) {
                        if (!is_string($val)) {
                            $val = strval($val);
                        }
                        $out .= '<tr><td>' . escape_html($key) . '</td><td>' . escape_html($val) . '</td></tr>';
                    }
                    $out .= '</table>';
                }
            }

            if ($out == '') {
                $out = do_lang('NONE');
            }

            return array('', $out, '', '');
        }
    }
}
