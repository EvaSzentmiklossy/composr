<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    google_analytics
 */

i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

$error_msg = new Tempcode();
if (!addon_installed__messaged('google_analytics', $error_msg)) {
    return $error_msg;
}

if (!empty($map['param'])) {
    $property_id = $map['param'];
    set_value('ga_property_id', $property_id, true);
}

$metric = empty($map['metric']) ? null : $map['metric'];

$id = empty($map['id']) ? null : $map['id'];

$days = empty($map['days']) ? 31 : intval($map['days']);

require_code('google_analytics');
return render_google_analytics($metric, $id, $days);