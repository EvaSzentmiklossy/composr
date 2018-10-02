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
 * @package    shopping
 */

/**
 * Hook class.
 */
class Hook_addon_registry_shopping
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
        return 'Shopping catalogue functionality.';
    }

    /**
     * Get a list of tutorials that apply to this addon.
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_ecommerce',
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
                'ecommerce',
                'catalogues',
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
        return 'themes/default/images/icons/menu/rich_content/ecommerce/shopping_cart.svg';
    }

    /**
     * Get a list of files that belong to this addon.
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/menu/rich_content/ecommerce/orders.svg',
            'themes/default/images/icons/menu/adminzone/audit/ecommerce/undispatched_orders.svg',
            'themes/default/images/icons/menu/rich_content/ecommerce/shopping_cart.svg',
            'themes/default/images/icons/buttons/cart_add.svg',
            'themes/default/images/icons/buttons/cart_checkout.svg',
            'themes/default/images/icons/buttons/cart_empty.svg',
            'themes/default/images/icons/buttons/cart_update.svg',
            'sources/hooks/systems/notifications/order_dispatched.php',
            'sources/hooks/systems/notifications/new_order.php',
            'sources/hooks/systems/notifications/low_stock.php',
            'sources/hooks/modules/admin_setupwizard_installprofiles/shopping.php',
            'sources/hooks/systems/config/cart_hold_hours.php',
            'sources/hooks/systems/ecommerce/catalogue_items.php',
            'sources/hooks/systems/ecommerce/cart_orders.php',
            'sources/hooks/blocks/main_staff_checklist/shopping_orders.php',
            'sources/hooks/systems/tasks/export_shopping_orders.php',
            'sources/shopping.php',
            'site/pages/modules/shopping.php',
            'themes/default/templates/CATALOGUE_products_CATEGORY_SCREEN.tpl',
            'themes/default/templates/CATALOGUE_products_CATEGORY_EMBED.tpl',
            'themes/default/templates/CATALOGUE_products_ENTRY_SCREEN.tpl',
            'themes/default/templates/CATALOGUE_products_GRID_ENTRY_FIELD.tpl',
            'themes/default/templates/CATALOGUE_products_FIELDMAP_ENTRY_FIELD.tpl',
            'themes/default/templates/CATALOGUE_products_GRID_ENTRY_WRAP.tpl',
            'themes/default/templates/RESULTS_products_TABLE.tpl',
            'themes/default/javascript/shopping.js',
            'themes/default/templates/ECOM_SHOPPING_CART_BUTTONS.tpl',
            'adminzone/pages/modules/admin_shopping.php',
            'lang/EN/shopping.ini',
            'sources/hooks/systems/addon_registry/shopping.php',
            'sources/hooks/systems/cns_cpf_filter/shopping_cart.php',
            'themes/default/css/shopping.css',
            'themes/default/templates/ECOM_ADMIN_ORDER_ACTIONS.tpl',
            'themes/default/templates/ECOM_CART_LINK.tpl',
            'themes/default/templates/ECOM_ORDER_DETAILS_SCREEN.tpl',
            'themes/default/templates/ECOM_ADMIN_ORDERS_SCREEN.tpl',
            'themes/default/templates/ECOM_ORDERS_SCREEN.tpl',
            'themes/default/templates/ECOM_SHIPPING_ADDRESS.tpl',
            'themes/default/templates/ECOM_CART_BUTTON_VIA_PAYPAL.tpl',
            'themes/default/templates/ECOM_SHOPPING_CART_SCREEN.tpl',
            'themes/default/templates/ECOM_SHOPPING_ITEM_QUANTITY_FIELD.tpl',
            'themes/default/templates/ECOM_SHOPPING_ITEM_REMOVE_FIELD.tpl',
            'themes/default/templates/RESULTS_cart_TABLE.tpl',
            'themes/default/templates/RESULTS_TABLE_cart_FIELD.tpl',
            'sources/hooks/systems/symbols/STOCK_CHECK.php',
            'sources/hooks/systems/symbols/CART_LINK.php',
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
            'templates/ECOM_ORDERS_SCREEN.tpl' => 'ecom_orders_screen',
            'templates/ECOM_ADMIN_ORDERS_SCREEN.tpl' => 'administrative__ecom_admin_orders_screen',
            'templates/ECOM_ORDER_DETAILS_SCREEN.tpl' => 'ecom_order_details_screen',
            'templates/ECOM_ADMIN_ORDER_ACTIONS.tpl' => 'ecom_order_details_screen',
            'templates/ECOM_SHIPPING_ADDRESS.tpl' => 'ecom_order_details_screen',
            'templates/ECOM_SHOPPING_ITEM_QUANTITY_FIELD.tpl' => 'shopping_cart_screen',
            'templates/ECOM_SHOPPING_ITEM_REMOVE_FIELD.tpl' => 'shopping_cart_screen',
            'templates/ECOM_CART_BUTTON_VIA_PAYPAL.tpl' => 'ecom_cart_button_via_paypal',
            'templates/ECOM_SHOPPING_CART_SCREEN.tpl' => 'shopping_cart_screen',
            'templates/RESULTS_cart_TABLE.tpl' => 'shopping_cart_screen',
            'templates/RESULTS_TABLE_cart_FIELD.tpl' => 'shopping_cart_screen',
            'templates/ECOM_CART_LINK.tpl' => 'ecom_cart_link_screen',
            'templates/CATALOGUE_products_CATEGORY_EMBED.tpl' => 'grid_category_screen__products',
            'templates/CATALOGUE_products_ENTRY_SCREEN.tpl' => 'products_entry_screen',
            'templates/CATALOGUE_products_FIELDMAP_ENTRY_FIELD.tpl' => 'products_entry_screen',
            'templates/ECOM_SHOPPING_CART_BUTTONS.tpl' => 'products_entry_screen',
            'templates/CATALOGUE_products_CATEGORY_SCREEN.tpl' => 'grid_category_screen__products',
            'templates/CATALOGUE_products_GRID_ENTRY_FIELD.tpl' => 'grid_category_screen__products',
            'templates/CATALOGUE_products_GRID_ENTRY_WRAP.tpl' => 'grid_category_screen__products',
            'templates/RESULTS_products_TABLE.tpl' => 'results_products_table',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declarative.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__products_entry_screen()
    {
        require_lang('catalogues');
        require_lang('ecommerce');
        require_css('catalogues');

        $fields = new Tempcode();
        $fields_table = new Tempcode();

        foreach (placeholder_array() as $v) {
            $_field = do_lorem_template('CATALOGUE_products_FIELDMAP_ENTRY_FIELD', array(
                'ENTRYID' => placeholder_random_id(),
                'CATALOGUE' => lorem_phrase(),
                'TYPE' => lorem_word(),
                'FIELD' => lorem_word(),
                'FIELDID' => placeholder_random_id(),
                '_FIELDID' => placeholder_random_id(),
                'FIELDTYPE' => lorem_word(),
                'VALUE_PLAIN' => lorem_phrase(),
                'VALUE' => lorem_phrase(),
            ));
            $fields->attach($_field);
        }

        $cart_buttons = do_lorem_template('ECOM_SHOPPING_CART_BUTTONS', array(
            'OUT_OF_STOCK' => lorem_phrase(),
            'ACTION_URL' => placeholder_url(),
            'TYPE_CODE' => placeholder_id(),
            'PURCHASE_ACTION_URL' => placeholder_url(),
            'CART_URL' => placeholder_url(),
        ));

        $rating_inside = new Tempcode();

        $map = array(
            'ID' => placeholder_id(),
            'FIELD_0' => lorem_phrase(),
            'FIELD_0_PLAIN' => lorem_phrase(),
            'FIELD_1' => lorem_phrase(),
            'FIELD_1_PLAIN' => lorem_phrase(),
            'FIELD_2' => placeholder_number(),
            'FIELD_3_PLAIN' => placeholder_number(),
            'FIELD_3' => placeholder_number(),
            'FIELD_2_PLAIN' => placeholder_number(),
            'FIELD_7' => placeholder_image(),
            'FIELD_7_PLAIN' => placeholder_url(),
            'FIELD_7_THUMB' => placeholder_image(),
            'FIELD_9' => lorem_phrase(),
            'PRODUCT_CODE' => placeholder_id(),
            'PRICE' => placeholder_number(),
            'RATING' => $rating_inside,
            'ALLOW_RATING' => false,
            'MAP_TABLE' => placeholder_table(),
            'ADD_TO_CART' => placeholder_url(),
            'FIELDS' => $fields,
            'ENTRY_SCREEN' => true,
            'GIVE_CONTEXT' => false,
            'EDIT_URL' => placeholder_url(),
        );
        $entry = do_lorem_template('CATALOGUE_DEFAULT_FIELDMAP_ENTRY_WRAP', $map);

        return array(
            lorem_globalise(do_lorem_template('CATALOGUE_products_ENTRY_SCREEN', $map + array(
                    'TITLE' => lorem_title(),
                    'WARNINGS' => '',
                    'ENTRY' => $entry,
                    'ID' => placeholder_id(),
                    'EDIT_URL' => placeholder_url(),
                    'TRACKBACK_DETAILS' => lorem_phrase(),
                    'RATING_DETAILS' => lorem_phrase(),
                    'COMMENT_DETAILS' => lorem_phrase(),
                    'ADD_DATE' => placeholder_date(),
                    'ADD_DATE_RAW' => placeholder_date_raw(),
                    'EDIT_DATE_RAW' => placeholder_date_raw(),
                    'VIEWS' => placeholder_number(),
                    'TAGS' => array(),
                    'CART_BUTTONS' => $cart_buttons,
                    'CATALOGUE' => 'products',
                    'SUBMITTER' => placeholder_id(),
                    'FIELD_1' => lorem_word(),
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
    public function tpl_preview__grid_category_screen__products()
    {
        require_lang('catalogues');
        require_lang('ecommerce');
        require_css('catalogues');

        $fields = new Tempcode();
        $fields_table = new Tempcode();

        foreach (placeholder_array() as $v) {
            $_field = do_lorem_template('CATALOGUE_products_GRID_ENTRY_FIELD', array(
                'ENTRYID' => placeholder_random_id(),
                'CATALOGUE' => lorem_phrase(),
                'TYPE' => lorem_word(),
                'FIELD' => lorem_word(),
                'FIELDID' => placeholder_random_id(),
                '_FIELDID' => placeholder_random_id(),
                'FIELDTYPE' => lorem_word(),
                'VALUE_PLAIN' => lorem_phrase(),
                'VALUE' => lorem_phrase(),
            ));
            $fields->attach($_field);
        }

        $rating_inside = new Tempcode();

        $map = array(
            'FIELD_0' => lorem_phrase(),
            'FIELD_0_PLAIN' => lorem_phrase(),
            'FIELD_1' => lorem_phrase(),
            'FIELD_1_PLAIN' => lorem_phrase(),
            'FIELD_2' => placeholder_number(),
            'FIELD_2_PLAIN' => placeholder_number(),
            'FIELD_7' => placeholder_image(),
            'FIELD_7_PLAIN' => placeholder_url(),
            'FIELD_7_THUMB' => placeholder_image(),
            'FIELD_9' => lorem_phrase(),
            'PRODUCT_CODE' => placeholder_id(),
            'PRICE' => placeholder_number(),
            'RATING' => $rating_inside,
            'MAP_TABLE' => placeholder_table(),
            'ADD_TO_CART' => placeholder_url(),
            'FIELDS' => $fields,
            'URL' => placeholder_url(),
            'VIEW_URL' => placeholder_url(),
            'ALLOW_RATING' => false,
        );
        $entry = do_lorem_template('CATALOGUE_products_GRID_ENTRY_WRAP', $map);

        $entries = do_lorem_template('CATALOGUE_products_CATEGORY_EMBED', array(
            'DISPLAY_TYPE' => 'FIELDMAPS',
            'ENTRIES' => $entry,
            'ROOT' => placeholder_id(),
            'BLOCK_PARAMS' => '',
            'SORTING' => '',
            'PAGINATION' => '',

            'START' => '0',
            'MAX' => '10',
            'START_PARAM' => 'x_start',
            'MAX_PARAM' => 'x_max',
        ));

        return array(
            lorem_globalise(do_lorem_template('CATALOGUE_products_CATEGORY_SCREEN', $map + array(
                'ID' => placeholder_id(),
                'ADD_DATE_RAW' => placeholder_date(),
                'TITLE' => lorem_title(),
                '_TITLE' => lorem_phrase(),
                'CATALOGUE_TITLE' => lorem_phrase(),
                'TAGS' => '',
                'CATALOGUE' => lorem_word_2(),
                'ADD_ENTRY_URL' => placeholder_url(),
                'ADD_CAT_URL' => placeholder_url(),
                'ADD_CAT_TITLE' => do_lang_tempcode('ADD_CATALOGUE_CATEGORY'),
                'EDIT_CAT_URL' => placeholder_url(),
                'EDIT_CATALOGUE_URL' => placeholder_url(),
                'ENTRIES' => $entries,
                'SUBCATEGORIES' => '',
                'DESCRIPTION' => lorem_sentence(),
                'TREE' => lorem_phrase(),
                'DISPLAY_TYPE' => '0',
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
    public function tpl_preview__ecom_cart_link_screen()
    {
        require_lang('ecommerce');

        $cart_link = do_lorem_template('ECOM_CART_LINK', array(
            'URL' => placeholder_url(),
            'TITLE' => lorem_phrase(),
            'ITEMS' => placeholder_number(),
        ), null, false);

        return array(
            lorem_globalise($cart_link, null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declarative.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__results_products_table()
    {
        require_css('catalogues');
        require_lang('ecommerce');

        $array = placeholder_array();
        $cells = new Tempcode();
        foreach ($array as $k => $v) {
            if ($k == 1) {
                $cells->attach(do_lorem_template('RESULTS_TABLE_FIELD_TITLE', array(
                    'VALUE' => $v,
                )));
            } else {
                $cells->attach(do_lorem_template('RESULTS_TABLE_FIELD_TITLE_SORTABLE', array(
                    'VALUE' => $v,
                    'SORT_URL_DESC' => placeholder_url(),
                    'SORT_DESC_SELECTED' => lorem_word(),
                    'SORT_ASC_SELECTED' => lorem_word(),
                    'SORT_URL_ASC' => placeholder_url(),
                )));
            }
        }
        $header_row = $cells;

        $order_entries = new Tempcode();
        foreach ($array as $k1 => $_v) {
            $cells = new Tempcode();
            foreach ($array as $k2 => $v) {
                $tick = do_lorem_template('RESULTS_TABLE_TICK', array(
                    'ID' => placeholder_id() . '_' . strval($k1) . '_' . strval($k2),
                ));
                $cells->attach(do_lorem_template('RESULTS_TABLE_FIELD', array(
                    'VALUE' => $tick,
                )));
            }
            $order_entries->attach(do_lorem_template('RESULTS_TABLE_ENTRY', array(
                'VALUES' => $cells,
            )));
        }

        $selectors = new Tempcode();
        $sortable = null;
        foreach ($array as $k => $v) {
            $selectors->attach(do_lorem_template('PAGINATION_SORTER', array(
                'SELECTED' => '',
                'NAME' => $v,
                'VALUE' => $v,
            )));
        }
        $sort = do_lorem_template('PAGINATION_SORT', array(
            'HIDDEN' => '',
            'SORT' => lorem_word(),
            'URL' => placeholder_url(),
            'SELECTORS' => $selectors,
        ));

        return array(
            lorem_globalise(do_lorem_template('RESULTS_products_TABLE', array(
                'TEXT_ID' => placeholder_random_id(),
                'HEADER_ROW' => $header_row,
                'RESULT_ENTRIES' => $order_entries,
                'SORT' => $sort,
                'PAGINATION' => placeholder_pagination(),
                'MESSAGE' => lorem_phrase(),
                'WIDTHS' => array(
                    placeholder_number(),
                ),
            )), null, '', true)
        );
    }

    /**
     * Function to display custom result tables.
     *
     * @param  ID_TEXT $tpl_set Tpl set name
     * @return Tempcode Tempcode
     */
    public function show_custom_tables($tpl_set)
    {
        $header_row = new Tempcode();
        foreach (array(lorem_word(), lorem_word_2(), lorem_word(), lorem_word_2()) as $k => $v) {
            $header_row->attach(do_lorem_template('RESULTS_TABLE_FIELD_TITLE', array(
                'VALUE' => $v,
            )));
        }
        $entries = new Tempcode();
        foreach (placeholder_array() as $k => $v) {
            $cells = new Tempcode();

            $entry_data = array(
                lorem_word(),
                placeholder_date(),
                lorem_word(),
                lorem_word()
            );

            foreach ($entry_data as $_k => $_v) {
                $cells->attach(do_lorem_template('RESULTS_TABLE_' . $tpl_set . 'FIELD', array(
                    'VALUE' => $_v,
                )));
            }
            $entries->attach(do_lorem_template('RESULTS_TABLE_' . $tpl_set . 'ENTRY', array(
                'VALUES' => $cells,
            )));
        }

        $selectors = new Tempcode();
        foreach (placeholder_array(11) as $k => $v) {
            $selectors->attach(do_lorem_template('PAGINATION_SORTER', array(
                'SELECTED' => '',
                'NAME' => $v,
                'VALUE' => $v,
            )));
        }

        $sort = do_lorem_template('PAGINATION_SORT', array(
            'HIDDEN' => '',
            'SORT' => lorem_word(),
            'URL' => placeholder_url(),
            'SELECTORS' => $selectors,
        ));

        return do_lorem_template('RESULTS_' . $tpl_set . 'TABLE', array(
            'HEADER_ROW' => $header_row,
            'RESULT_ENTRIES' => $entries,
            'MESSAGE' => new Tempcode(),
            'SORT' => $sort,
            'PAGINATION' => placeholder_pagination(),
            'WIDTHS' => array(
                placeholder_number(),
            ),
        ));
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declarative.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__ecom_cart_button_via_paypal()
    {
        require_lang('ecommerce');

        $items = array();
        foreach (placeholder_array() as $k => $v) {
            $items[] = array(
                'PRODUCT_NAME' => lorem_word(),
                'TYPE_CODE' => lorem_word(),
                'PRICE' => placeholder_number(),
                'TAX' => placeholder_number(),
                'AMOUNT' => placeholder_number(),
                'QUANTITY' => placeholder_number(),
            );
        }
        return array(
            lorem_globalise(do_lorem_template('ECOM_CART_BUTTON_VIA_PAYPAL', array(
                'ITEMS' => $items,
                'CURRENCY' => 'GBP',
                'SHIPPING_COST' => placeholder_number(),
                'PAYMENT_ADDRESS' => lorem_word(),
                'FORM_URL' => placeholder_url(),
                'MEMBER_ADDRESS' => placeholder_array(),
                'ORDER_ID' => placeholder_id(),
                'TRANS_EXPECTING_ID' => placeholder_id(),
                'TYPE_CODE' => $items[0]['TYPE_CODE'],
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
    public function tpl_preview__shopping_cart_screen()
    {
        require_lang('ecommerce');

        $shopping_cart = new Tempcode();
        foreach (placeholder_array() as $k => $v) {
            $cells = new Tempcode();
            foreach (placeholder_array(8) as $_v) {
                $cells->attach(do_lorem_template('RESULTS_TABLE_FIELD_TITLE', array(
                    'VALUE' => $_v,
                )));
            }
            $header_row = $cells;

            $product_image = placeholder_image();
            $product_link = placeholder_link();
            $currency = lorem_word();
            $edit_qnty = do_lorem_template('ECOM_SHOPPING_ITEM_QUANTITY_FIELD', array(
                'TYPE_CODE' => strval($k),
                'QUANTITY' => lorem_phrase(),
            ));
            $del_item = do_lorem_template('ECOM_SHOPPING_ITEM_REMOVE_FIELD', array(
                'TYPE_CODE' => strval($k),
            ));

            $values = array(
                $product_image,
                $product_link,
                $edit_qnty,
                $currency . (string)placeholder_number(),
                $currency . (string)placeholder_number(),
                $currency . (string)placeholder_number(),
                $currency . placeholder_number(),
                $del_item,
            );
            $cells = new Tempcode();
            foreach ($values as $value) {
                $cells->attach(do_lorem_template('RESULTS_TABLE_cart_FIELD', array(
                    'VALUE' => $value,
                    'CLASS' => '',
                )));
            }
            $shopping_cart->attach(do_lorem_template('RESULTS_TABLE_ENTRY', array(
                'VALUES' => $cells,
            )));
        }

        $selectors = new Tempcode();
        foreach (placeholder_array() as $k => $v) {
            $selectors->attach(do_lorem_template('PAGINATION_SORTER', array(
                'SELECTED' => '',
                'NAME' => placeholder_id(),
                'VALUE' => lorem_word(),
            )));
        }
        $sort = do_lorem_template('PAGINATION_SORT', array(
            'HIDDEN' => '',
            'SORT' => lorem_word(),
            'URL' => placeholder_url(),
            'SELECTORS' => $selectors,
        ));

        $results_table = do_lorem_template('RESULTS_cart_TABLE', array(
            'WIDTHS' => array(),
            'HEADER_ROW' => $header_row,
            'RESULT_ENTRIES' => $shopping_cart,
            'MESSAGE' => new Tempcode(),
            'SORT' => $sort,
            'PAGINATION' => lorem_word(),
        ));

        return array(
            lorem_globalise(do_lorem_template('ECOM_SHOPPING_CART_SCREEN', array(
                'TITLE' => lorem_title(),
                'RESULTS_TABLE' => $results_table,
                'UPDATE_CART_URL' => placeholder_url(),
                'CONTINUE_SHOPPING_URL' => placeholder_url(),
                'MESSAGE' => lorem_phrase(),
                'TYPE_CODES' => placeholder_id(),
                'EMPTY_CART_URL' => placeholder_url(),
                'TOTAL_PRICE' => placeholder_number(),
                'TOTAL_TAX' => placeholder_number(),
                'TOTAL_SHIPPING_COST' => placeholder_number(),
                'TOTAL_SHIPPING_TAX' => placeholder_number(),
                'GRAND_TOTAL' => placeholder_number(),
                'CURRENCY' => 'GBP',
                'PROCEED' => lorem_phrase(),
                'FIELDS' => placeholder_fields(),
                'NEXT_URL' => placeholder_url(),
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
    public function tpl_preview__ecom_orders_screen()
    {
        require_lang('ecommerce');

        $orders = array();
        foreach (placeholder_array() as $v) {
            $orders[] = array(
                'ORDER_TITLE' => lorem_word(),
                'ID' => placeholder_id(),
                'TXN_ID' => placeholder_id(),
                'TRANSACTION_LINKER' => lorem_word(),
                'TOTAL_PRICE' => placeholder_number(),
                'TOTAL_TAX' => placeholder_number(),
                'TOTAL_SHIPPING_COST' => placeholder_number(),
                'CURRENCY' => 'GBP',
                'DATE' => placeholder_date(),
                'STATUS' => lorem_word_2(),
                'NOTE' => lorem_phrase(),
                'ORDER_DET_URL' => placeholder_url(),
                'FULFILLABLE' => true,
            );
        }
        return array(
            lorem_globalise(do_lorem_template('ECOM_ORDERS_SCREEN', array(
                'TITLE' => lorem_title(),
                'ORDERS' => $orders,
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
    public function tpl_preview__administrative__ecom_admin_orders_screen()
    {
        require_lang('ecommerce');

        return array(
            lorem_globalise(do_lorem_template('ECOM_ADMIN_ORDERS_SCREEN', array(
                'TITLE' => lorem_title(),
                'RESULTS_TABLE' => placeholder_table(),
                'PAGINATION' => placeholder_pagination(),
                'SEARCH_URL' => placeholder_url(),
                'SEARCH_VAL' => lorem_phrase(),
                'HIDDEN' => '',
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
    public function tpl_preview__ecom_order_details_screen()
    {
        require_code('ecommerce');
        require_lang('cns_special_cpf');
        require_lang('ecommerce');

        $order_actions = do_lorem_template('ECOM_ADMIN_ORDER_ACTIONS', array(
            'ORDER_TITLE' => lorem_phrase(),
            'ORDER_ACTUALISE_URL' => placeholder_url(),
            'ORDER_STATUS' => lorem_word(),
        ));

        $address_parts = array(
            'name' => lorem_phrase(),
            'street_address' => lorem_phrase(),
            'city' => lorem_phrase(),
            'county' => lorem_phrase(),
            'state' => lorem_phrase(),
            'post_code' => lorem_phrase(),
            'country' => lorem_phrase(),
            'email' => lorem_phrase(),
            'phone' => lorem_phrase(),
        );

        $shipping_address = do_lorem_template('ECOM_SHIPPING_ADDRESS', array(
            'FIRSTNAME' => lorem_phrase(),
            'LASTNAME' => lorem_phrase(),
            'STREET_ADDRESS' => lorem_phrase(),
            'CITY' => lorem_phrase(),
            'COUNTY' => lorem_phrase(),
            'STATE' => lorem_phrase(),
            'POST_CODE' => lorem_phrase(),
            'COUNTRY' => lorem_phrase(),
            'EMAIL' => lorem_phrase(),
            'PHONE' => lorem_phrase(),
            'FORMATTED_ADDRESS' => get_formatted_address($address_parts),
        ));

        return array(
            lorem_globalise(do_lorem_template('ECOM_ORDER_DETAILS_SCREEN', array(
                'TITLE' => lorem_title(),
                'TEXT' => lorem_sentence(),
                'RESULTS_TABLE' => placeholder_table(),
                'ORDER_NUMBER' => placeholder_number(),
                'ADD_DATE' => placeholder_date(),
                'TOTAL_PRICE' => placeholder_number(),
                'TOTAL_TAX' => placeholder_number(),
                'TOTAL_SHIPPING_COST' => placeholder_number(),
                'CURRENCY' => 'GBP',
                'TRANSACTION_LINKER' => lorem_phrase(),
                'ORDERED_BY_MEMBER_ID' => placeholder_id(),
                'ORDERED_BY_USERNAME' => lorem_word(),
                'ORDER_STATUS' => lorem_phrase(),
                'NOTES' => lorem_phrase(),
                'ORDER_ACTIONS' => $order_actions,
                'SHIPPING_ADDRESS' => $shipping_address,
            )), null, '', true)
        );
    }
}
