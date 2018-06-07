<?php /*

Composr/ocProducts is free to use or incorporate this into Composr and assert any copyright.
This notification hook was created using the downloads notification hook as a template.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  None asserted
 * @package    downloads_followup_email
 */

/**
 * Hook class.
 */
class Hook_notification_downloads_followup_email extends Hook_Notification
{
    /**
     * Find whether a handled notification code supports categories.
     * (Content types, for example, will define notifications on specific categories, not just in general. The categories are interpreted by the hook and may be complex. E.g. it might be like a regexp match, or like FORUM:3 or TOPIC:100)
     *
     * @param  ID_TEXT $notification_code Notification code
     * @return boolean Whether it does
     */
    public function supports_categories($notification_code)
    {
        return true;
    }

    /**
     * Standard function to create the standardised category tree
     *
     * @param  ID_TEXT $notification_code Notification code
     * @param  ?ID_TEXT $id The ID of where we're looking under (null: N/A)
     * @return array Tree structure
     */
    public function create_category_tree($notification_code, $id)
    {
        require_code('downloads');

        if (is_null($id)) {
            $total = $GLOBALS['SITE_DB']->query_select_value_if_there('download_categories', 'COUNT(*)');
            if ($total > intval(get_option('general_safety_listing_limit'))/*reasonable limit*/) {
                return parent::create_category_tree($notification_code, $id); // Too many, so just allow removing UI
            }
        }

        $page_links = get_downloads_tree(null, is_null($id) ? null : intval($id), null, null, null, is_null($id) ? 0 : 1);
        $filtered = array();
        foreach ($page_links as $p) {
            if (strval($p['id']) !== $id) {
                $filtered[] = $p;
            }
        }
        return $filtered;
    }

    /**
     * Find the initial setting that members have for a notification code (only applies to the member_could_potentially_enable members).
     *
     * @param  ID_TEXT $notification_code Notification code
     * @param  ?SHORT_TEXT $category The category within the notification code (null: none)
     * @return integer Initial setting
     */
    public function get_initial_setting($notification_code, $category = null)
    {
        return A_INSTANT_EMAIL;
    }

    /**
     * Get a list of all the notification codes this hook can handle.
     * (Addons can define hooks that handle whole sets of codes, so hooks are written so they can take wide authority)
     *
     * @return array List of codes (mapping between code names, and a pair: section and labelling for those codes)
     */
    public function list_handled_codes()
    {
        $list = array();
        $list['downloads_followup_email'] = array(do_lang('CONTENT'), do_lang('downloads_followup_email:NOTIFICATION_TYPE_downloads_followup_email'));
        return $list;
    }

    /**
     * Get a list of members who have enabled this notification (i.e. have permission to AND have chosen to or are defaulted to).
     *
     * @param  ID_TEXT $notification_code Notification code
     * @param  ?SHORT_TEXT $category The category within the notification code (null: none)
     * @param  ?array $to_member_ids List of member IDs we are restricting to (null: no restriction). This effectively works as a intersection set operator against those who have enabled.
     * @param  ?integer $from_member_id The member ID doing the sending. Either a MEMBER or a negative number (e.g. A_FROM_SYSTEM_UNPRIVILEGED) (null: current member)
     * @param  integer $start Start position (for pagination)
     * @param  integer $max Maximum (for pagination)
     * @return array A pair: Map of members to their notification setting, and whether there may be more
     */
    public function list_members_who_have_enabled($notification_code, $category = null, $to_member_ids = null, $from_member_id = null, $start = 0, $max = 300)
    {
        $members = $this->_all_members_who_have_enabled($notification_code, $category, $to_member_ids, $start, $max);
        $members = $this->_all_members_who_have_enabled_with_page_access($members, 'downloads', 'download', $category, $to_member_ids, $start, $max);

        return $members;
    }
}
