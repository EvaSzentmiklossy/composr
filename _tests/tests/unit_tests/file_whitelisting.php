<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    testing_platform
 */

/**
 * Composr test case class (unit testing).
 */
class file_whitelisting_test_set extends cms_test_case
{
    protected $file_types = array();

    public function setUp()
    {
        parent::setUp();

        $path = get_file_base() . '/sources/mime_types.php';
        $c = file_get_contents($path);

        $this->file_types = array();
        $matches = array();
        $num_matches = preg_match_all('#\'(\w{1,10})\'#', $c, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $this->file_types[] = $matches[1][$i];
        }
    }

    public function testTrackerValidTypes()
    {
        $path = get_file_base() . '/tracker/config_inc.php';
        $c = file_get_contents($path);

        $file_types = array();
        $matches = array();
        preg_match('#\$g_allowed_files = \'(.*)\';#', $c, $matches);
        $file_types = explode(',', $matches[1]);
        sort($file_types);

        $file_types_expected = $this->file_types;
        $file_types_expected = array_diff($file_types_expected, array('exe', 'dmg', 'php', 'htm', 'html', 'svg', 'css', 'js', 'json', 'woff', 'xml', 'xsd', 'xsl', 'rss', 'atom')); // No executable or web formats should be uploaded by non-admins
        sort($file_types_expected);

        $this->assertTrue($file_types == $file_types_expected);
    }

    public function testConfigValidTypes()
    {
        $path = get_file_base() . '/sources/hooks/systems/config/valid_types.php';
        $c = file_get_contents($path);

        $file_types = array();
        $matches = array();
        preg_match('#return \'([^\']+)\';#', $c, $matches);
        $file_types = explode(',', $matches[1]);
        sort($file_types);

        $file_types_expected = $this->file_types;
        $file_types_expected = array_diff($file_types_expected, array('exe', 'dmg')); // No executables as users may try and get people to run on own machine (separately internally we filter web formats)
        sort($file_types_expected);

        $this->assertTrue($file_types == $file_types_expected);
    }

    public function testAppYaml()
    {
        $path = get_file_base() . '/app.yaml';
        $c = file_get_contents($path);

        // --

        $file_types = array();
        $matches = array();
        preg_match('#- url: \/\(\.\*\\\.\((.*)\)\)#m', $c, $matches);
        $file_types = explode('|', $matches[1]);
        sort($file_types);

        $file_types_expected = $this->file_types;
        $file_types_expected = array_diff($file_types_expected, array('php', 'htm')); // No files which may be web-processed/web-generated
        sort($file_types_expected);

        $this->assertTrue($file_types == $file_types_expected);

        // --

        $file_types = array();
        $matches = array();
        preg_match('#  upload: \.\*\\\.\((.*)\)#m', $c, $matches);
        $file_types = explode('|', $matches[1]);
        sort($file_types);

        $file_types_expected = $this->file_types;
        $file_types_expected = array_diff($file_types_expected, array('php', 'htm')); // No files which may be web-processed/web-generated
        sort($file_types_expected);

        $this->assertTrue($file_types == $file_types_expected);
    }

    public function testCodebookRef()
    {
        $path = get_file_base() . '/docs/pages/comcode_custom/EN/codebook_3.txt';
        $c = file_get_contents($path);

        $file_types = array();
        $matches = array();
        preg_match('#\.\*\\\\\.\((.*)\)\\\\\?\?\'\);\[\/tt\]#', $c, $matches);
        $file_types = explode('|', $matches[1]);
        sort($file_types);

        $file_types_expected = $this->file_types;
        $file_types_expected = array_diff($file_types_expected, array('php', 'htm')); // No files which may be web-processed/web-generated
        sort($file_types_expected);

        $this->assertTrue($file_types == $file_types_expected);
    }

    public function testHtaccess()
    {
        $path = get_file_base() . '/recommended.htaccess';
        $c = file_get_contents($path);

        $file_types = array();
        $matches = array();
        preg_match('#RewriteCond \$1 \\\\\.\((.*)\) \[OR\]#', $c, $matches);
        $file_types = explode('|', $matches[1]);
        sort($file_types);

        $file_types_expected = $this->file_types;
        $file_types_expected = array_diff($file_types_expected, array('htm')); // No .htm files which may be web-generated
        sort($file_types_expected);

        $this->assertTrue($file_types == $file_types_expected);
    }

    public function testOtherValidTypes()
    {
        require_code('images');

        foreach (array('valid_images', 'valid_videos', 'valid_audios') as $f) {
            $path = get_file_base() . '/sources/hooks/systems/config/' . $f . '.php';
            $c = file_get_contents($path);

            $file_types = array();
            $matches = array();
            preg_match('#return \'([^\']+)\';#', $c, $matches);
            $file_types = explode(',', $matches[1]);

            foreach ($file_types as $file_type) {
                $this->assertTrue(in_array($file_type, $this->file_types));

                if ($f == 'valid_images') {
                    $this->assertTrue(is_image('example.' . $file_type, IMAGE_CRITERIA_WEBSAFE, true), $file_type . ' not websafe?');
                }
            }
        }
    }
}
