<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    confluence
 */

/**
 * Hook class.
 */
class Hook_sitemap_confluence extends Hook_sitemap_content
{
    /**
     * Find whether the hook is active.
     *
     * @return boolean Whether the hook is active.
     */
    public function is_active()
    {
        return ((get_option('confluence_subdomain') != '') && (get_option('confluence_space') != ''));
    }

    /**
     * Find if a page-link will be covered by this node.
     *
     * @param  ID_TEXT $page_link The page-link.
     * @return integer A SITEMAP_NODE_* constant.
     */
    public function handles_page_link($page_link)
    {
        $matches = array();
        if (preg_match('#^([^:]*):([^:]*)#', $page_link, $matches) != 0) {
            $zone = $matches[1];
            $page = $matches[2];

            $_page = 'docs';
            $_zone = get_page_zone($_page);

            if (($_zone == $zone) && ($_page == $page)) {
                if ($page_link == $_zone . ':' . $_page) {
                    return SITEMAP_NODE_HANDLED_VIRTUALLY; // Root node
                }

                return SITEMAP_NODE_HANDLED;
            }
        }
        return SITEMAP_NODE_NOT_HANDLED;
    }

    /**
     * Find details of a virtual position in the sitemap. Virtual positions have no structure of their own, but can find child structures to be absorbed down the tree. We do this for modularity reasons.
     *
     * @param  ID_TEXT $page_link The page-link we are finding.
     * @param  ?string $callback Callback function to send discovered page-links to (null: return).
     * @param  ?array $valid_node_types List of node types we will return/recurse-through (null: no limit)
     * @param  ?integer $child_cutoff Maximum number of children before we cut off all children (null: no limit).
     * @param  ?integer $max_recurse_depth How deep to go from the sitemap root (null: no limit).
     * @param  integer $recurse_level Our recursion depth (used to limit recursion, or to calculate importance of page-link, used for instance by Google sitemap [deeper is typically less important]).
     * @param  integer $options A bitmask of SITEMAP_GEN_* options.
     * @param  ID_TEXT $zone The zone we will consider ourselves to be operating in (needed due to transparent redirects feature)
     * @param  integer $meta_gather A bitmask of SITEMAP_GATHER_* constants, of extra data to include.
     * @param  boolean $return_anyway Whether to return the structure even if there was a callback. Do not pass this setting through via recursion due to memory concerns, it is used only to gather information to detect and prevent parent/child duplication of default entry points.
     * @return ?array List of node structures (null: working via callback).
     */
    public function get_virtual_nodes($page_link, $callback = null, $valid_node_types = null, $child_cutoff = null, $max_recurse_depth = null, $recurse_level = 0, $options = 0, $zone = '_SEARCH', $meta_gather = 0, $return_anyway = false)
    {
        $nodes = ($callback === null || $return_anyway) ? array() : null;

        if (($valid_node_types !== null) && (!in_array($this->content_type, $valid_node_types))) {
            return $nodes;
        }

        $page = 'docs';
        $zone = get_page_zone($page);

        if (!has_page_access(get_member(), $page, $zone)) {
            return $nodes;
        }

        require_code('confluence');
        $mappings = confluence_get_mappings();

        foreach ($mappings as $mapping) {
            if ($mapping['parent_id'] === null) {
                $child_page_link = $zone . ':' . $page . ':' . urlencode($mapping['slug']);
                $node = $this->get_node($child_page_link, $callback, $valid_node_types, $child_cutoff, $max_recurse_depth, $recurse_level, $options, $zone, $meta_gather);
                if (($callback === null || $return_anyway) && ($node !== null)) {
                    $nodes[] = $node;
                }
            }
        }

        return $nodes;
    }

    /**
     * Find details of a position in the Sitemap.
     *
     * @param  ID_TEXT $page_link The page-link we are finding.
     * @param  ?string $callback Callback function to send discovered page-links to (null: return).
     * @param  ?array $valid_node_types List of node types we will return/recurse-through (null: no limit)
     * @param  ?integer $child_cutoff Maximum number of children before we cut off all children (null: no limit).
     * @param  ?integer $max_recurse_depth How deep to go from the Sitemap root (null: no limit).
     * @param  integer $recurse_level Our recursion depth (used to limit recursion, or to calculate importance of page-link, used for instance by XML Sitemap [deeper is typically less important]).
     * @param  integer $options A bitmask of SITEMAP_GEN_* options.
     * @param  ID_TEXT $zone The zone we will consider ourselves to be operating in (needed due to transparent redirects feature)
     * @param  integer $meta_gather A bitmask of SITEMAP_GATHER_* constants, of extra data to include.
     * @param  ?array $row Database row (null: lookup).
     * @param  boolean $return_anyway Whether to return the structure even if there was a callback. Do not pass this setting through via recursion due to memory concerns, it is used only to gather information to detect and prevent parent/child duplication of default entry points.
     * @return ?array Node structure (null: working via callback / error).
     */
    public function get_node($page_link, $callback = null, $valid_node_types = null, $child_cutoff = null, $max_recurse_depth = null, $recurse_level = 0, $options = 0, $zone = '_SEARCH', $meta_gather = 0, $row = null, $return_anyway = false)
    {
        $page = 'docs';
        $zone = get_page_zone($page);

        $parts = explode(':', $page_link, 3);
        $slug = urldecode($parts[2]);

        require_code('confluence');

        $mappings = confluence_get_mappings();

        if (is_numeric($slug)) {
            $mappings_by_complex_id = $mappings;
        } else {
            $mappings_by_complex_id = list_to_map('slug', $mappings);
            if (!isset($mappings_by_complex_id[$slug])) {
                $mappings_by_complex_id = list_to_map('title', $mappings);
            }
        }
        if (!isset($mappings_by_complex_id[$slug])) {
            return null;
        }

        $mapping = $mappings_by_complex_id[$slug];
        $id = $mapping['id'];

        $title = make_string_tempcode($mapping['title']);

        $struct = array(
            'title' => $title,
            'content_type' => null,
            'content_id' => null,
            'modifiers' => array(),
            'only_on_page' => '',
            'page_link' => $page_link,
            'url' => null,
            'extra_meta' => array(
                'description' => null,
                'image' => null,
                'add_date' => null,
                'edit_date' => null,
                'submitter' => null,
                'views' => null,
                'rating' => null,
                'meta_keywords' => null,
                'meta_description' => null,
                'categories' => null,
                'validated' => null,
                'db_row' => null,
            ),
            'permissions' => array(
                array(
                    'type' => 'zone',
                    'zone_name' => $zone,
                    'is_owned_at_this_level' => false,
                ),
                array(
                    'type' => 'page',
                    'zone_name' => $zone,
                    'page_name' => $page,
                    'is_owned_at_this_level' => false,
                ),
            ),
            'has_possible_children' => true,
            'children' => null,

            // These are likely to be changed in individual hooks
            'sitemap_priority' => SITEMAP_IMPORTANCE_MEDIUM,
            'sitemap_refreshfreq' => 'weekly',
        );

        if ($callback !== null) {
            call_user_func($callback, $struct);
        }

        // Children...

        $struct['children'] = array();
        foreach ($mapping['children'] as $child_id) {
            $_mapping = $mappings[$child_id];
            $child_page_link = $zone . ':' . $page . ':' . urlencode($_mapping['slug']);
            $node = $this->get_node($child_page_link, $callback, $valid_node_types, $child_cutoff, $max_recurse_depth, $recurse_level, $options, $zone, $meta_gather, $row);
            if (($callback === null || $return_anyway) && ($node !== null)) {
                $struct['children'][] = $node;
            }
        }

        return ($callback === null || $return_anyway) ? $struct : null;
    }
}