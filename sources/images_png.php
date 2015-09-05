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
 * @package    core
 */

/**
 * Try to further compress a PNG file, via palette tricks and maximum gzip compression.
 *
 * @param  PATH $path File path.
 * @param  boolean $lossy Whether to do a lossy convert.
 */
function png_compress($path, $lossy = false)
{
    if (!is_file($path)) {
        return;
    }

    $img = imagecreatefrompng($path);
    if (!imageistruecolor($img)) {
        if (function_exists('imagepalettetotruecolor')) {
            imagepalettetotruecolor($img);
        } else {
            imagedestroy($img);
            return;
        }
    }

    // Has alpha?
    $width = imagesx($img);
    $height = imagesy($img);
    $has_alpha = false;
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $at = imagecolorat($img, $x, $y);
            $parsed_colour = imagecolorsforindex($img, $at);
            if ((isset($parsed_colour['alpha'])) && ($parsed_colour['alpha'] != 0)) {
                $has_alpha = true;
                if ($parsed_colour['alpha'] != 127) {
                    // Blended alpha, cannot handle as anything other than a proper 32-bit PNG
                    imagedestroy($img);
                    return;
                }
            }
        }
    }

    // Product JPEG version, if relevant
    $trying_jpeg = (!$has_alpha) && ($lossy) && (get_value('save_jpegs_as_png') === '1');
    if ($trying_jpeg) {
        imagejpeg($img, $path . '.jpeg_tmp', intval(get_option('jpeg_quality'))); // We will ultimately save as a .png which is actually the JPEG. We rely on Composr, and browsers, doing their magic detection of images (not just relying on mime types)
        $jpeg_size = filesize($path . '.jpeg_tmp');
    }

    // Check we don't have too many colours for 8-bit
    $colours = array();
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $at = imagecolorat($img, $x, $y);
            if ($lossy) {
                $at = $at & ~bindec('00001111' . '00001111' . '00001111' . '00111111'); // Reduce to a colour resolution of 16 distinct values on each of RGB, and 4 on A
            }
            $colours[$at] = true;
            if (count($colours) > 300) { // Give some grace, but >300 is unworkable (at least 44 too many)
                // Too many colours for 8-bit...

                // Try as a JPEG?
                if ($trying_jpeg) {
                    $png_size = filesize($path);
                    if ($jpeg_size < $png_size) {
                        unlink($path);
                        rename($path . '.jpeg_tmp', $path);
                    } else {
                        unlink($path . '.jpeg_tmp');
                    }
                    fix_permissions($path . '.jpeg_tmp');
                    sync_file($path . '.jpeg_tmp');
                }

                // Return
                imagedestroy($img);
                return;
            }
        }
    }

    // Try as 8-bit...

    if ($has_alpha) {
        $alphabg = imagecolorallocatealpha($img, 255, 0, 255, 127);
        imagecolortransparent($img, $alphabg);
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $at = imagecolorat($img, $x, $y);
                $parsed_colour = imagecolorsforindex($img, $at);
                if ((isset($parsed_colour['alpha'])) && ($parsed_colour['alpha'] != 0)) {
                    imagesetpixel($img, $x, $y, $alphabg);
                }
            }
        }
    }

    imagetruecolortopalette($img, true, 256);

    imagesavealpha($img, false); // No alpha, only transparency

    imagepng($img, $path, 9);

    if ($trying_jpeg) {
        $png_size = filesize($path); // Find size of 8-bit PNG
        if ($jpeg_size < $png_size) {
            unlink($path);
            rename($path . '.jpeg_tmp', $path);
        } else {
            unlink($path . '.jpeg_tmp');
        }
        fix_permissions($path . '.jpeg_tmp');
        sync_file($path . '.jpeg_tmp');
    }

    fix_permissions($path);
    sync_file($path);

    imagedestroy($img);
}

/**
 * Try to further compress a PNG file, via palette tricks and maximum gzip compression.
 *
 * @param  resource $img GD image.
 * @param  boolean $lossy Whether to do a lossy convert.
 * @ignore
 */
function _png_compress(&$img, $lossy = false)
{
    // Has alpha?
    $width = imagesx($img);
    $height = imagesy($img);
    $has_alpha = false;
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $at = imagecolorat($img, $x, $y);
            $parsed_colour = imagecolorsforindex($img, $at);
            if ((isset($parsed_colour['alpha'])) && ($parsed_colour['alpha'] != 0)) {
                $has_alpha = true;
                if ($parsed_colour['alpha'] != 127) {
                    // Blended alpha, cannot handle as anything other than a proper 32-bit PNG
                    return;
                }
            }
        }
    }

    // Check we don't have too many colours for 8-bit
    $colours = array();
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $at = imagecolorat($img, $x, $y);
            if ($lossy) {
                $at = $at & ~bindec('00001111' . '00001111' . '00001111' . '00111111'); // Reduce to a colour resolution of 16 distinct values on each of RGB, and 4 on A
            }
            $colours[$at] = true;
            if (count($colours) > 300) { // Give some grace, but >300 is unworkable (at least 44 too many)
                // Too many colours for 8-bit...

                return;
            }
        }
    }

    // Try as 8-bit...

    if ($has_alpha) {
        $alphabg = imagecolorallocatealpha($img, 255, 0, 255, 127);
        imagecolortransparent($img, $alphabg);
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $at = imagecolorat($img, $x, $y);
                $parsed_colour = imagecolorsforindex($img, $at);
                if ((isset($parsed_colour['alpha'])) && ($parsed_colour['alpha'] != 0)) {
                    imagesetpixel($img, $x, $y, $alphabg);
                }
            }
        }
    }

    imagetruecolortopalette($img, true, 256);

    imagesavealpha($img, false); // No alpha, only transparency
}
