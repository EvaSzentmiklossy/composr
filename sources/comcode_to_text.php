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
 * @package    core_rich_media
 */

/**
 * Make some Comcode more readable by humans.
 *
 * @param  string $in Comcode text to change
 * @param  boolean $for_extract Whether this is for generating an extract that does not need to be fully comprehended (i.e. favour brevity)
 * @param  array $tags_to_preserve List of tags to preserve
 * @return string Clean text
 */
function _strip_comcode($in, $for_extract = false, $tags_to_preserve = array())
{
    $text = $in;

    //$text = str_replace("\n", '', $text);

    // Very simple case
    if ((strpos($text, '[') === false) && (strpos($text, '{') === false)) {
        return trim($text);
    }

    // Strip resource loader
    if (stripos($text, '[require') !== false) {
        $text = preg_replace('#\[require_css(\s[^"\[\]]*)?\][^\[\]]*\[/require_css\]#', '', $text);
        $text = preg_replace('#\[require_javascript(\s[^"\[\]]*)?\][^\[\]]*\[/require_javascript\]#', '', $text);
    }

    // If it is just HTML encapsulated in Comcode, force our best HTML to text conversion first
    if (stripos($text, 'html]') !== false) {
        $match = array();
        if ((substr($text, 0, 10) == '[semihtml]') && (substr(trim($text), -11) == '[/semihtml]')) {
            require_code('comcode_from_html');
            $text = comcode_preg_replace('semihtml', '#^\[semihtml\](.*)\[\/semihtml\]$#si', array('_semihtml_to_comcode_callback'), $text);
        }
        if ((substr($text, 0, 6) == '[html]') && (substr(trim($text), -7) == '[/html]')) {
            require_code('comcode_from_html');
            $text = comcode_preg_replace('html', '#^\[html\](.*)\[\/html\]$#si', array('_semihtml_to_comcode_callback'), $text);
        }
    }

    // Now is a very simple case (after we converted HTML to Comcode)
    if ((strpos($text, '[') === false) && (strpos($text, '{') === false)) {
        return trim($text);
    }

    require_code('tempcode_compiler');
    if ((strpos($text, '[code') === false) && (strpos($text, '[no_parse') === false) && (strpos($text, '[tt') === false)) {
        // Change username links to plain username namings
        if (stripos($text, '{{') !== false) {
            $text = preg_replace('#\{\{([^|\}\{]*)\}\}#', '\1', $text);
        }

        $text = str_replace('{$SITE_NAME}', get_site_name(), $text);
        $text = str_replace('{$SITE_NAME*}', escape_html(get_site_name()), $text);

        if (stripos($text, '{') !== false) {
            // Remove directives etc
            do {
                $before = $text;
                $text = preg_replace('#\{([^\|\}\{]*)\}#', '', $text);
            } while ($text != $before);
        }

        if (strpos($text, '{') !== false) {
            $text = static_evaluate_tempcode(template_to_tempcode($text, 0, false, '', null, null, true));
        }
    }

    $match = array();

    if (stripos($text, 'html]') !== false) {
        if (!in_array('semihtml', $tags_to_preserve)) {
            require_code('comcode_from_html');
            $text = comcode_preg_replace('semihtml', '#^\[semihtml\](.*)\[\/semihtml\]$#si', array('_semihtml_to_comcode_callback'), $text);
        }
        if (!in_array('html', $tags_to_preserve)) {
            require_code('comcode_from_html');
            $text = comcode_preg_replace('html', '#^\[html\](.*)\[\/html\]$#si', array('_semihtml_to_comcode_callback'), $text);
        }
    }

    // Convert certain tags to 'url' tags. These may then be converted to text entirely, depending on if 'url' is being preserved
    if (stripos($text, '[page') !== false) {
        if (!in_array('page', $tags_to_preserve)) {
            $text = preg_replace_callback("#\[page=\"([^\"]*)\"[^\[\]]*\](.*)\[/page\]#Usi", '_page_callback', $text);
        }
    }
    if (stripos($text, '[attachment') !== false) {
        if (!in_array('attachment', $tags_to_preserve)) {
            $text = preg_replace("#\[attachment[^\[\]]* description=\"([^\"]*)\"[^\[\]]*\](\d*)\[/attachment[^\[\]]*\]#Usi", '[url="' . find_script('attachment') . '?id=\2"]\1[/url]', $text);
            $text = preg_replace("#\[attachment[^\[\]]*\](\d*)\[/attachment[^\[\]]*\]#Usi", '[url="' . find_script('attachment') . '?id=\1"]' . do_lang('VIEW') . '[/url]', $text);
        }
    }
    if (stripos($text, '[media') !== false) {
        if (!in_array('media', $tags_to_preserve)) {
            $text = preg_replace("#\[media=\"([^\"]*)\"[^\[\]]*\](.*)\[/media\]#Usi", '[url="\2"]\1[/url]', $text);
            $text = preg_replace("#\[media[^\[\]]*\](.*)\[/media\]#Usi", '[url="\1"]' . do_lang('VIEW') . '[/url]', $text);
        }
    }
    $text = simplify_static_tempcode($text);
    if (!in_array('thumb', $tags_to_preserve)) {
        $text = str_replace('[/thumb', '[/img', str_replace('[thumb', '[img', $text));
    }
    if (stripos($text, '[img') !== false) {
        if (!in_array('img', $tags_to_preserve)) {
            $text = preg_replace("#\[img[^\[\]]*\]\s*d\s*a\s*t\s*a\s*:[^\[\]]*\[/img\]#Usi", '', $text);

            $text = preg_replace("#\[img( param)?=\"([^\"]*)\"[^\[\]]*\](.*)\[/img\]#Usi", '[url="\3"]\2[/url] ', $text);
            $text = preg_replace("#\[img[^\[\]]*\](.*)\[/img\]#Usi", '[url="\1"]' . do_lang('VIEW') . '[/url] ', $text);
        }
    }
    if (stripos($text, '[email') !== false) {
        if (!in_array('email', $tags_to_preserve)) {
            $text = preg_replace("#\[email[^\[\]]*\](.*)\[/email\]#Usi", '[url="mailto:\1"]\1[/url]', $text);
        }
    }

    if (stripos($text, '[url') !== false) {
        if (!in_array('url', $tags_to_preserve)) {
            $text = preg_replace("#\[url( param)?=\"([^\"]*)\"[^\[\]]*\]\\1\[/url\]#", '\2', $text);
            $text = preg_replace("#\(\[url( param)?=\"(https?://[^\"]*)\"([^\]]*)\]([^\[\]]*)\[/url\]\)#", '\2', $text);
            $text = preg_replace("#\[url( param)?=\"(https?://[^\"]*)\"([^\]]*)\]([^\[\]]*)\[/url\]#", $for_extract ? '\4' : '\4 (\2)', $text);
            $text = preg_replace("#\[url( param)?=\"([^\"]*)\"[^\[\]]*\]([^\[\]]*)\[/url\]#", '\2 (\4)', $text);
            $text = preg_replace("#\[url( param)?=\"([^\"]*)\"([^\]]*)\]([^\[\]]*)\[/url\]#", $for_extract ? '\2' : '\2 (\4)', $text);
            $text = preg_replace("#(.*) \(\\1\)#", '\1', $text);
        }
    }

    if (!in_array('html', $tags_to_preserve)) {
        $text = strip_html($text);
    }

    if (stripos($text, '[random') !== false) {
        if (!in_array('random', $tags_to_preserve)) {
            $text = preg_replace_callback('#\[random(( [^=]*="([^"]*)")*)\].*\[/random\]#Usi', '_random_callback', $text);
        }
    }

    if (stripos($text, '[shocker') !== false) {
        if (!in_array('shocker', $tags_to_preserve)) {
            $text = preg_replace_callback('#\[shocker(( [^=]*="([^"]*)")*)\].*\[/shocker\]#Usi', '_shocker_callback', $text);
        }
    }

    if (stripos($text, '[jumping') !== false) {
        if (!in_array('jumping', $tags_to_preserve)) {
            $text = preg_replace_callback('#\[jumping(( [^=]*="([^"]*)")*)\].*\[/jumping\]#Usi', '_shocker_callback', $text);
        }
    }

    if (stripos($text, '[abbr') !== false) {
        if (!in_array('abbr', $tags_to_preserve)) {
            $text = preg_replace('#\[abbr="([^"]*)"[^\]]*\](.*)\[/abbr\]#Usi', '\2 (\1)', $text);
        }
    }
    if (stripos($text, '[acronym') !== false) {
        if (!in_array('acronym', $tags_to_preserve)) {
            $text = preg_replace('#\[acronym="([^"]*)"[^\]]*\](.*)\[/acronym\]#Usi', '\2 (\1)', $text);
        }
    }
    if (stripos($text, '[tooltip') !== false) {
        if (!in_array('tooltip', $tags_to_preserve)) {
            $text = preg_replace('#\[tooltip="([^"]*)"[^\]]*\](.*)\[/tooltip\]#Usi', '\2 (\1)', $text);
        }
    }

    if (addon_installed('ecommerce')) {
        if (stripos($text, '[currency') !== false) {
            if (!in_array('currency', $tags_to_preserve)) {
                $text = preg_replace('#\[currency\](.*)\[/currency\]#Usi', get_option('currency') . ' \1', $text);
                $text = preg_replace('#\[currency="([^"]*)"[^\]]*\](.*)\[/currency\]#Usi', '\1 \2', $text);
            }
        }
    }

    if (stripos($text, '[hide') !== false) {
        if (!in_array('hide', $tags_to_preserve)) {
            $text = preg_replace('#\[hide\](.*)\[/hide\]#Usi', do_lang('comcode:SPOILER_WARNING') . ':' . "\n" . '\1', $text);
            $text = preg_replace('#\[hide="([^"]*)"[^\]]*\](.*)\[/hide\]#Usi', '\1:' . "\n" . '\2', $text);
        }
    }

    if (stripos($text, '[indent') !== false) {
        if (!in_array('indent', $tags_to_preserve)) {
            $text = preg_replace_callback('#\[indent[^\]]*\](.*)\[/indent\]#Usi', '_indent_callback', $text);
        }
    }

    if (stripos($text, '[title') !== false) {
        if (!in_array('title', $tags_to_preserve)) {
            $text = preg_replace_callback('#(\s*)\[title([^\]]*)\](.*)\[/title\]#Usi', '_title_callback', $text);
        }
    }

    if (stripos($text, '[box') !== false) {
        if (!in_array('box', $tags_to_preserve)) {
            $text = preg_replace_callback('#\[box="([^"]*)"[^\]]*\](.*)\[/box\]#Usi', '_box_callback', $text);
        }
    }

    $tags_to_strip_entirely = array_diff(array(
        'snapback',
        'post',
        'thread',
        'topic',
        'include',
        'staff_note',
        'contents',
        'block',
        'section_controller',
        'big_tab_controller',
        'concepts',
        'menu',

        // These are handled earlier for normal attachments, this strips what may be left
        'attachment',
        'attachment_safe',
    ), $tags_to_preserve);
    foreach ($tags_to_strip_entirely as $s) {
        if (stripos($text, '[' . $s) !== false) {
            $text = preg_replace('#\[' . $s . '[^\]]*\].*\[/' . $s . '\]#Usi', '', $text);
        }
    }

    if (stripos($text, '[surround') !== false) {
        if (!in_array('surround', $tags_to_preserve)) {
            $text = preg_replace('#\[surround="[\w ]+"\](.*)\[/surround\]#Usi', '$1', $text);
        }
    }

    if (stripos($text, '[if_in_group') !== false) {
        if (!in_array('if_in_group', $tags_to_preserve)) {
            $text = preg_replace_callback('#(\[if_in_group="[^"]*"\])(.*)(\[/if_in_group\])#Usi', '_comcode_callback', $text);
        }
    }

    $tags_to_strip_just_tags = array_diff(array(
        'surround',
        'ticker',
        'right',
        'center',
        'left',
        'align',
        'list',
        'html',
        'semihtml',
        'concept',
        'size',
        'color',
        'font',
        'tt',
        'address',
        'sup',
        'sub',
        'box',
        'samp',
        'q',
        'var',
        'overlay',
        'section',
        'big_tab',
        'tabs',
        'tab',
        'carousel',
        'pulse',
        'php',
        'codebox',
        'sql',
        'no_parse',
        'code',

        // Intentional metadata in these actually, so just leave them in
        //'reference',
        //'cite',
        //'quote',
        //'ins',
        //'s',
        //'del',
        //'dfn',
    ), $tags_to_preserve);
    foreach ($tags_to_strip_just_tags as $s) {
        if (stripos($text, '[' . $s) !== false) {
            $text = preg_replace('#\[' . $s . '[^\]]*\](.*)\[/' . $s . '\]#U', '\1', $text);
        }
    }

    $reps = array();
    if (!in_array('list', $tags_to_preserve)) {
        $reps += array(
            '[/*]' => '',
            '[*]' => ' - ',
            "[list]\n" => '',
            "\n[/list]" => '',
            '[list]' => '',
            '[/list]' => '',
        );
    }
    if (!in_array('b', $tags_to_preserve)) {
        $reps += array(
            '[b]' => '**',
            '[/b]' => '**',
        );
    }
    if (!in_array('i', $tags_to_preserve)) {
        $reps += array(
            '[i]' => '*',
            '[/i]' => '*',
        );
    }
    if (!in_array('u', $tags_to_preserve)) {
        $reps += array(
            '[u]' => '__',
            '[/u]' => '__',
        );
    }
    if (!in_array('highlight', $tags_to_preserve)) {
        $reps += array(
            '[highlight]' => '***',
            '[/highlight]' => '***',
        );
    }
    $text = str_replace(array_keys($reps), array_values($reps), $text);
    if (!in_array('list', $tags_to_preserve)) {
        $text = preg_replace('#\[list[^\[\]]*\]#', '', $text);
    }

    if (stripos($text, '{$') !== false) {
        $text = preg_replace('#\{\$,[^\{\}]*\}#', '', $text);
    }

    if (stripos($text, "\n\n") !== false) {
        $text = preg_replace('#\n\n+#', "\n\n", $text);
    }

    if (strpos($text, '&') !== false) {
        if (get_charset() != 'utf-8') {
            $text = str_replace(array('&ndash;', '&mdash;', '&hellip;', '&middot;', '&ldquo;', '&rdquo;', '&lsquo;', '&rsquo;'), array('-', '-', '...', '|', '"', '"', "'", "'"), $text);
        }
        $text = @html_entity_decode($text, ENT_QUOTES);
    }

    return trim($text);
}

/**
 * Indent text lines. Callback for preg_replace_callback.
 *
 * @param  array $matches Matches
 * @return string Replacement
 *
 * @ignore
 */
function _indent_callback($matches)
{
    return '      ' . str_replace("\n", "\n" . '      ', $matches[1]);
}

/**
 * Make titles readable. Callback for preg_replace_callback.
 *
 * @param  array $matches Matches
 * @return string Replacement
 *
 * @ignore
 */
function _title_callback($matches)
{
    $symbol = '-';
    if (strpos($matches[2], '1') !== false || $matches[2] == '') {
        $symbol = '=';
    }

    $ret = $matches[1];
    if (substr_count($matches[1], "\n") == 0) {
        $ret .= "\n";
    }
    $ret .= $matches[3] . "\n" . str_repeat($symbol, strlen($matches[3]));
    return $ret;
}

/**
 * Make boxes readable. Callback for preg_replace_callback.
 *
 * @param  array $matches Matches
 * @return string Replacement
 *
 * @ignore
 */
function _box_callback($matches)
{
    return $matches[1] . "\n" . str_repeat('-', strlen($matches[1])) . "\n" . $matches[2];
}

/**
 * Make page tags into url tags. Callback for preg_replace_callback.
 *
 * @param  array $matches Matches
 * @return string Replacement
 *
 * @ignore
 */
function _page_callback($matches)
{
    list($zone, $attributes, $hash) = page_link_decode($matches[1]);
    $url = static_evaluate_tempcode(build_url($attributes, $zone, array(), false, false, true, $hash));
    return '[url="' . addslashes($url) . '"]' . $matches[2] . '[/url]';
}

/**
 * Extract some random. Callback for preg_replace_callback.
 *
 * @param  array $matches Matches
 * @return string Replacement
 *
 * @ignore
 */
function _random_callback($matches)
{
    $parts = array();
    $num_parts = preg_match_all('# [^=]*="([^"]*)"#', $matches[1], $parts);
    return $parts[1][mt_rand(0, $num_parts - 1)];
}

/**
 * Extract all shocker/jumping text. Callback for preg_replace_callback.
 *
 * @param  array $matches Matches
 * @return string Replacement
 *
 * @ignore
 */
function _shocker_callback($matches)
{
    $parts = array();
    $num_parts = preg_match_all('# [^=]*="([^"]*)"#', $matches[1], $parts);
    $out = '';
    for ($i = 0; $i < $num_parts; $i++) {
        if ($out != '') {
            $out .= ', ';
        }
        $out .= $parts[1][$i];
    }
    return $out;
}

/**
 * Pass tag through Comcode parser. Callback for preg_replace_callback.
 *
 * @param  array $matches Matches
 * @return string Replacement
 *
 * @ignore
 */
function _comcode_callback($matches)
{
    return str_replace('xxx', $matches[2], static_evaluate_tempcode(comcode_to_tempcode($matches[1] . 'xxx' . $matches[3])));
}

/**
 * Pass tag through semihtml_to_comcode. Callback for preg_replace_callback.
 *
 * @param  array $matches Matches
 * @return string Replacement
 *
 * @ignore
 */
function _semihtml_to_comcode_callback($matches)
{
    return semihtml_to_comcode($matches[1], true, true);
}
