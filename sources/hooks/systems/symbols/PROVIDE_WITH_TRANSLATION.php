<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    translation
 */

/**
 * Hook class.
 */
class Hook_symbol_PROVIDE_WITH_TRANSLATION
{
    /**
     * Run function for symbol hooks. Searches for tasks to perform.
     *
     * @param  array $param Symbol parameters
     * @return string Result
     */
    public function run($param)
    {
        require_code('translation');

        $text = isset($param[0]) ? $param[0] : '';

        if (isset($param[1])) {
            $context = @constant('TRANS_TEXT_CONTEXT_' . $param[1]);
            if ($context === null) {
                $context = TRANS_TEXT_CONTEXT_html_block;
            }
        } else {
            $context = TRANS_TEXT_CONTEXT_html_block;
        }

        $from = empty($param[2]) ? null : $param[2];

        $text_translated = translate_text($text, $context, $from, user_lang());

        if ($text_translated === null) {
            return $text;
        }

        switch ($context) {
            case TRANS_TEXT_CONTEXT_plain:
                $text .= ' (' . $text_translated . ')';
                break;

            case TRANS_TEXT_CONTEXT_html_block:
                $text .= '<br /><div class="box box__translation"><div class="box_inner">' . $text_translated . '</div></div>'; // Comes with a "Powered by" message
                break;

            case TRANS_TEXT_CONTEXT_html_inline:
                $text .= ' (' . $text_translated . ')';
                break;
        }

        return $text;
    }
}
