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
 * @package    realtime_rain
 */

/**
 * Hook class.
 */
class Hook_addon_registry_realtime_rain
{
    /**
     * Get a list of file permissions to set.
     *
     * @param  boolean $runtime Whether to include wildcards represented runtime-created chmoddable files
     * @return array File permissions to set
     */
    public function get_chmod_array($runtime = false)
    {
        return array();
    }

    /**
     * Get the version of Composr this addon is for.
     *
     * @return float Version number
     */
    public function get_version()
    {
        return cms_version_number();
    }

    /**
     * Get the description of the addon.
     *
     * @return string Description of the addon
     */
    public function get_description()
    {
        return 'Real-time/historic display of website activity.';
    }

    /**
     * Get a list of tutorials that apply to this addon.
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_statistics',
        );
    }

    /**
     * Get a mapping of dependency types.
     *
     * @return array File permissions to set
     */
    public function get_dependencies()
    {
        return array(
            'requires' => array(
                'stats',
            ),
            'recommends' => array(),
            'conflicts_with' => array(),
        );
    }

    /**
     * Explicitly say which icon should be used.
     *
     * @return URLPATH Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/menu/adminzone/audit/realtime_rain.svg';
    }

    /**
     * Get a list of files that belong to this addon.
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/menu/adminzone/audit/realtime_rain.svg',
            'themes/default/images/icons/tool_buttons/realtime_rain_off.svg',
            'themes/default/images/icons/tool_buttons/realtime_rain_on.svg',

            'adminzone/pages/modules/admin_realtime_rain.php',
            'sources/realtime_rain.php',
            'data/realtime_rain.php',
            'sources/hooks/systems/snippets/realtime_rain_load.php',
            'sources/hooks/systems/page_groupings/realtime_rain.php',
            'themes/default/css/realtime_rain.css',
            'themes/default/templates/REALTIME_RAIN_OVERLAY.tpl',
            'themes/default/templates/REALTIME_RAIN_BUBBLE.tpl',
            'themes/default/javascript/realtime_rain.js',
            'themes/default/javascript/button_realtime_rain.js',
            'lang/EN/realtime_rain.ini',
            'themes/default/images/realtime_rain/index.html',
            'sources/hooks/systems/realtime_rain/.htaccess',
            'sources_custom/hooks/systems/realtime_rain/.htaccess',
            'sources/hooks/systems/realtime_rain/index.html',
            'sources_custom/hooks/systems/realtime_rain/index.html',
            'sources/hooks/systems/addon_registry/realtime_rain.php',
            'sources/hooks/systems/config/bottom_show_realtime_rain_button.php',

            'themes/default/images/icons/realtime_rain/email.svg',
            'themes/default/images/icons/realtime_rain/news.svg',
            'themes/default/images/icons/realtime_rain/phone.svg',
            'themes/default/images/icons/realtime_rain/searchengine.svg',
            'themes/default/images/icons/realtime_rain/index.html',

            'themes/default/images/icons/realtime_rain/next_but.svg',
            'themes/default/images/icons/realtime_rain/pause_but.svg',
            'themes/default/images/icons/realtime_rain/previous_but.svg',

            'themes/default/images/realtime_rain/news_bg.svg',
            'themes/default/images/realtime_rain/news_bot.svg',
            'themes/default/images/realtime_rain/news_header.svg',
            'themes/default/images/realtime_rain/realtime_indicator.svg',
            'themes/default/images/realtime_rain/time_line.svg',
            'themes/default/images/realtime_rain/timer_bg.svg',

            'themes/default/images/realtime_rain/actionlog-avatar.svg',
            'themes/default/images/realtime_rain/actionlog-bubble.svg',
            'themes/default/images/realtime_rain/banners-avatar.svg',
            'themes/default/images/realtime_rain/banners-bubble.svg',
            'themes/default/images/realtime_rain/calendar-avatar.svg',
            'themes/default/images/realtime_rain/calendar-bubble.svg',
            'themes/default/images/realtime_rain/chat-avatar.svg',
            'themes/default/images/realtime_rain/chat-bubble.svg',
            'themes/default/images/realtime_rain/ecommerce-avatar.svg',
            'themes/default/images/realtime_rain/ecommerce-bubble.svg',
            'themes/default/images/realtime_rain/join-avatar.svg',
            'themes/default/images/realtime_rain/join-bubble.svg',
            'themes/default/images/realtime_rain/news-avatar.svg',
            'themes/default/images/realtime_rain/news-bubble.svg',
            'themes/default/images/realtime_rain/point_charges-avatar.svg',
            'themes/default/images/realtime_rain/point_charges-bubble.svg',
            'themes/default/images/realtime_rain/point_gifts-avatar.svg',
            'themes/default/images/realtime_rain/point_gifts-bubble.svg',
            'themes/default/images/realtime_rain/polls-avatar.svg',
            'themes/default/images/realtime_rain/polls-bubble.svg',
            'themes/default/images/realtime_rain/post-avatar.svg',
            'themes/default/images/realtime_rain/post-bubble.svg',
            'themes/default/images/realtime_rain/recommend-avatar.svg',
            'themes/default/images/realtime_rain/recommend-bubble.svg',
            'themes/default/images/realtime_rain/search-avatar.svg',
            'themes/default/images/realtime_rain/search-bubble.svg',
            'themes/default/images/realtime_rain/security-avatar.svg',
            'themes/default/images/realtime_rain/security-bubble.svg',
            'themes/default/images/realtime_rain/stats-avatar.svg',
            'themes/default/images/realtime_rain/stats-bubble.svg',

            'themes/default/images/realtime_rain/sun_effect.svg',
            'themes/default/images/realtime_rain/halo_effect.svg',
            'themes/default/images/realtime_rain/horns_effect.svg',
            'themes/default/images/realtime_rain/shadow_effect.svg',

            'themes/default/images/flags/AD.gif',
            'themes/default/images/flags/AE.gif',
            'themes/default/images/flags/AF.gif',
            'themes/default/images/flags/AG.gif',
            'themes/default/images/flags/AL.gif',
            'themes/default/images/flags/AM.gif',
            'themes/default/images/flags/AO.gif',
            'themes/default/images/flags/AR.gif',
            'themes/default/images/flags/AT.gif',
            'themes/default/images/flags/AU.gif',
            'themes/default/images/flags/AZ.gif',
            'themes/default/images/flags/BA.gif',
            'themes/default/images/flags/BB.gif',
            'themes/default/images/flags/BD.gif',
            'themes/default/images/flags/BE.gif',
            'themes/default/images/flags/BF.gif',
            'themes/default/images/flags/BG.gif',
            'themes/default/images/flags/BH.gif',
            'themes/default/images/flags/BI.gif',
            'themes/default/images/flags/BJ.gif',
            'themes/default/images/flags/BN.gif',
            'themes/default/images/flags/BO.gif',
            'themes/default/images/flags/BR.gif',
            'themes/default/images/flags/BS.gif',
            'themes/default/images/flags/BT.gif',
            'themes/default/images/flags/BW.gif',
            'themes/default/images/flags/BY.gif',
            'themes/default/images/flags/BZ.gif',
            'themes/default/images/flags/CA.gif',
            'themes/default/images/flags/CD.gif',
            'themes/default/images/flags/CF.gif',
            'themes/default/images/flags/CH.gif',
            'themes/default/images/flags/CI.gif',
            'themes/default/images/flags/CL.gif',
            'themes/default/images/flags/CM.gif',
            'themes/default/images/flags/CN.gif',
            'themes/default/images/flags/CO.gif',
            'themes/default/images/flags/CR.gif',
            'themes/default/images/flags/CU.gif',
            'themes/default/images/flags/CV.gif',
            'themes/default/images/flags/CY.gif',
            'themes/default/images/flags/CZ.gif',
            'themes/default/images/flags/DE.gif',
            'themes/default/images/flags/DJ.gif',
            'themes/default/images/flags/DK.gif',
            'themes/default/images/flags/DM.gif',
            'themes/default/images/flags/DO.gif',
            'themes/default/images/flags/DZ.gif',
            'themes/default/images/flags/EC.gif',
            'themes/default/images/flags/EE.gif',
            'themes/default/images/flags/EG.gif',
            'themes/default/images/flags/EH.gif',
            'themes/default/images/flags/ER.gif',
            'themes/default/images/flags/ES.gif',
            'themes/default/images/flags/ET.gif',
            'themes/default/images/flags/FI.gif',
            'themes/default/images/flags/FJ.gif',
            'themes/default/images/flags/FM.gif',
            'themes/default/images/flags/FR.gif',
            'themes/default/images/flags/GA.gif',
            'themes/default/images/flags/GB.gif',
            'themes/default/images/flags/GD.gif',
            'themes/default/images/flags/GE.gif',
            'themes/default/images/flags/GH.gif',
            'themes/default/images/flags/GI.gif',
            'themes/default/images/flags/GL.gif',
            'themes/default/images/flags/GM.gif',
            'themes/default/images/flags/GN.gif',
            'themes/default/images/flags/GP.gif',
            'themes/default/images/flags/GQ.gif',
            'themes/default/images/flags/GR.gif',
            'themes/default/images/flags/GT.gif',
            'themes/default/images/flags/GW.gif',
            'themes/default/images/flags/GY.gif',
            'themes/default/images/flags/HN.gif',
            'themes/default/images/flags/HR.gif',
            'themes/default/images/flags/HT.gif',
            'themes/default/images/flags/HU.gif',
            'themes/default/images/flags/ID.gif',
            'themes/default/images/flags/IE.gif',
            'themes/default/images/flags/IL.gif',
            'themes/default/images/flags/IN.gif',
            'themes/default/images/flags/IQ.gif',
            'themes/default/images/flags/IR.gif',
            'themes/default/images/flags/IS.gif',
            'themes/default/images/flags/IT.gif',
            'themes/default/images/flags/JM.gif',
            'themes/default/images/flags/JO.gif',
            'themes/default/images/flags/JP.gif',
            'themes/default/images/flags/KE.gif',
            'themes/default/images/flags/KG.gif',
            'themes/default/images/flags/KH.gif',
            'themes/default/images/flags/KI.gif',
            'themes/default/images/flags/KM.gif',
            'themes/default/images/flags/KN.gif',
            'themes/default/images/flags/KP.gif',
            'themes/default/images/flags/KR.gif',
            'themes/default/images/flags/KW.gif',
            'themes/default/images/flags/KZ.gif',
            'themes/default/images/flags/LA.gif',
            'themes/default/images/flags/LB.gif',
            'themes/default/images/flags/LC.gif',
            'themes/default/images/flags/LI.gif',
            'themes/default/images/flags/LK.gif',
            'themes/default/images/flags/LR.gif',
            'themes/default/images/flags/LS.gif',
            'themes/default/images/flags/LT.gif',
            'themes/default/images/flags/LU.gif',
            'themes/default/images/flags/LV.gif',
            'themes/default/images/flags/LY.gif',
            'themes/default/images/flags/MA.gif',
            'themes/default/images/flags/MC.gif',
            'themes/default/images/flags/MD.gif',
            'themes/default/images/flags/ME.gif',
            'themes/default/images/flags/MG.gif',
            'themes/default/images/flags/MH.gif',
            'themes/default/images/flags/MK.gif',
            'themes/default/images/flags/ML.gif',
            'themes/default/images/flags/MM.gif',
            'themes/default/images/flags/MN.gif',
            'themes/default/images/flags/MQ.gif',
            'themes/default/images/flags/MR.gif',
            'themes/default/images/flags/MT.gif',
            'themes/default/images/flags/MU.gif',
            'themes/default/images/flags/MV.gif',
            'themes/default/images/flags/MW.gif',
            'themes/default/images/flags/MX.gif',
            'themes/default/images/flags/MY.gif',
            'themes/default/images/flags/MZ.gif',
            'themes/default/images/flags/NA.gif',
            'themes/default/images/flags/NE.gif',
            'themes/default/images/flags/NG.gif',
            'themes/default/images/flags/NI.gif',
            'themes/default/images/flags/NL.gif',
            'themes/default/images/flags/NO.gif',
            'themes/default/images/flags/NP.gif',
            'themes/default/images/flags/NR.gif',
            'themes/default/images/flags/NZ.gif',
            'themes/default/images/flags/OM.gif',
            'themes/default/images/flags/PA.gif',
            'themes/default/images/flags/PE.gif',
            'themes/default/images/flags/PF.gif',
            'themes/default/images/flags/PG.gif',
            'themes/default/images/flags/PH.gif',
            'themes/default/images/flags/PK.gif',
            'themes/default/images/flags/PL.gif',
            'themes/default/images/flags/PR.gif',
            'themes/default/images/flags/PS.gif',
            'themes/default/images/flags/PT.gif',
            'themes/default/images/flags/PW.gif',
            'themes/default/images/flags/PY.gif',
            'themes/default/images/flags/QA.gif',
            'themes/default/images/flags/RO.gif',
            'themes/default/images/flags/RS.gif',
            'themes/default/images/flags/RU.gif',
            'themes/default/images/flags/RW.gif',
            'themes/default/images/flags/SA.gif',
            'themes/default/images/flags/SB.gif',
            'themes/default/images/flags/SC.gif',
            'themes/default/images/flags/SD.gif',
            'themes/default/images/flags/SE.gif',
            'themes/default/images/flags/SG.gif',
            'themes/default/images/flags/SI.gif',
            'themes/default/images/flags/SK.gif',
            'themes/default/images/flags/SL.gif',
            'themes/default/images/flags/SM.gif',
            'themes/default/images/flags/SN.gif',
            'themes/default/images/flags/SO.gif',
            'themes/default/images/flags/SR.gif',
            'themes/default/images/flags/ST.gif',
            'themes/default/images/flags/SV.gif',
            'themes/default/images/flags/SY.gif',
            'themes/default/images/flags/SZ.gif',
            'themes/default/images/flags/TD.gif',
            'themes/default/images/flags/TG.gif',
            'themes/default/images/flags/TH.gif',
            'themes/default/images/flags/TJ.gif',
            'themes/default/images/flags/TM.gif',
            'themes/default/images/flags/TN.gif',
            'themes/default/images/flags/TO.gif',
            'themes/default/images/flags/TP.gif',
            'themes/default/images/flags/TR.gif',
            'themes/default/images/flags/TT.gif',
            'themes/default/images/flags/TV.gif',
            'themes/default/images/flags/TW.gif',
            'themes/default/images/flags/TZ.gif',
            'themes/default/images/flags/UA.gif',
            'themes/default/images/flags/UG.gif',
            'themes/default/images/flags/US.gif',
            'themes/default/images/flags/UY.gif',
            'themes/default/images/flags/UZ.gif',
            'themes/default/images/flags/VA.gif',
            'themes/default/images/flags/VC.gif',
            'themes/default/images/flags/VE.gif',
            'themes/default/images/flags/VG.gif',
            'themes/default/images/flags/VI.gif',
            'themes/default/images/flags/VN.gif',
            'themes/default/images/flags/VU.gif',
            'themes/default/images/flags/WS.gif',
            'themes/default/images/flags/YE.gif',
            'themes/default/images/flags/ZA.gif',
            'themes/default/images/flags/ZM.gif',
            'themes/default/images/flags/ZW.gif',
            'themes/default/images/flags/index.html',
        );
    }

    /**
     * Get mapping between template names and the method of this class that can render a preview of them.
     *
     * @return array The mapping
     */
    public function tpl_previews()
    {
        return array(
            'templates/REALTIME_RAIN_OVERLAY.tpl' => 'administrative__realtime_rain_overlay',
            'templates/REALTIME_RAIN_BUBBLE.tpl' => 'administrative__realtime_rain_bubble',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declarative.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__realtime_rain_overlay()
    {
        return array(
            lorem_globalise(do_lorem_template('REALTIME_RAIN_OVERLAY', array(
                'MIN_TIME' => placeholder_id(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declarative.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__realtime_rain_bubble()
    {
        return array(
            lorem_globalise(do_lorem_template('REALTIME_RAIN_BUBBLE', array(
                'TITLE' => lorem_phrase(),
                'URL' => placeholder_url(),
                'IMAGE' => placeholder_image_url(),
                'GROUP_ID' => placeholder_id(),
                'RELATIVE_TIMESTAMP' => placeholder_date_raw(),
                'TICKER_TEXT' => lorem_phrase(),
                'TYPE' => lorem_word(),
                'IS_POSITIVE' => true,
                'IS_NEGATIVE' => false,
            )), null, '', true)
        );
    }
}
