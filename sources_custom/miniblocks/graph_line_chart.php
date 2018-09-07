<?php

require_code('graphs');

$width = empty($map['width']) ? null : $map['width'];
$height = empty($map['height']) ? null : $map['height'];

$x_axis_label = empty($map['x_axis_label']) ? '' : $map['x_axis_label'];
$y_axis_label = empty($map['y_axis_label']) ? '' : $map['y_axis_label'];

$begin_at_zero = empty($map['begin_at_zero']) ? true : ($map['begin_at_zero'] == '1');
$show_data_labels = empty($map['show_data_labels']) ? true : ($map['show_data_labels'] == '1');

$color_pool = empty($map['color_pool']) ? array() : explode(',', $map['color_pool']);

$file = empty($map['file']) ? 'uploads/website_specific/graph_test/line_chart.csv' : $map['file'];

$myfile = fopen(get_custom_file_base() . '/' . $file, 'rb');
$x_labels = fgetcsv($myfile);
array_shift($x_labels); // Irrelevant corner
$datasets = array();
while (($line = fgetcsv($myfile)) !== false) {
    if (implode('', $line) == '') {
        continue;
    }

    if (count($line) < 2) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    $label = array_shift($line);
    $datasets[] = array(
        'label' => $label,
        'data' => $line,
    );
}
fclose($myfile);

$tpl = graph_line_chart($datasets, $x_labels, $x_axis_label, $y_axis_label, $begin_at_zero, $show_data_labels, $color_pool, $width, $height);
$tpl->evaluate_echo();