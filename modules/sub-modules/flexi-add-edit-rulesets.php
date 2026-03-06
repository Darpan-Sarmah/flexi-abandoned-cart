<?php
/**
 * Provide an admin area view for the plugin.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link  https://abandoned-cart-recovery
 * @since 1.0.0
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/modules/sub-modules
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

$flexi_carts_dbqueries = new Flexi_Database_Queries();
if (isset($_GET[ 'panel' ]) && 'edit_rule' === $_GET[ 'panel' ] && isset($_GET[ 'ruleSetID' ])) {
    $ruleSetID     = sanitize_text_field(wp_unslash($_GET[ 'ruleSetID' ]));
    $query_results = $flexi_carts_dbqueries->select_db_query('flexi_email_rules', '*', "", 'id = ' . $ruleSetID);
    $form_data     = $query_results[ 0 ];
    $heading = "Edit Rule ".$form_data['rule_name'];

}
if (isset($_GET[ 'rule-created' ]) && 'true' === $_GET[ 'rule-created' ]) {
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Rule Set Created Successfully.', 'flexi-abandon-cart-recovery') . '</p></div>';
} elseif (isset($_GET[ 'rule-updated' ]) && 'true' === $_GET[ 'rule-updated' ]) {
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Rule Set Updated Successfully.', 'flexi-abandon-cart-recovery') . '</p></div>';
}

if (isset($_SERVER[ 'REQUEST_METHOD' ]) && 'POST' === $_SERVER[ 'REQUEST_METHOD' ]) {
    // Verify the nonce for security.

    if (!isset($_POST[ 'flexi_email_rule_set_data' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'flexi_email_rule_set_data' ])), 'flexi_email_rule_set')) {
        return;
    }
    $flexi_rules_set = $_POST; // Assuming this is filled from form data
    $final_data      = [  ];

    foreach ($flexi_rules_set as $key => $value) {
        if (preg_match('/filter-on-(\d+)/', $key, $matches)) {
            $i               = $matches[ 1 ];
            $filter_on_value = $flexi_rules_set[ "filter-on-$i" ] ?? '';

            if ($filter_on_value) {
                // Initialize the index if not set
                if (!isset($final_data[ $i ])) {
                    $final_data[ $i ] = [ 'filter_on' => $filter_on_value ];
                }

                // Set dynamic fields based on filter_on
                if ($filter_on_value === 'total_amt') {
                    $final_data[ $i ][ 'comparison_select' ] = $flexi_rules_set[ "comparison_select-$i" ] ?? '';
                    $final_data[ $i ][ 'amount' ]            = $flexi_rules_set[ "amount-$i" ] ?? '1';
                } elseif ($filter_on_value === 'num_of_items') {
                    $final_data[ $i ][ 'comparison_select' ] = $flexi_rules_set[ "comparison_select-$i" ] ?? '';
                    $final_data[ $i ][ 'items_num' ]         = $flexi_rules_set[ "items_num-$i" ] ?? "1";
                } elseif (in_array($filter_on_value, [ 'product_id', 'product_category', 'user_roles' ])) {
                    $final_data[ $i ][ 'include_exclude' ] = $flexi_rules_set[ "include_exclude-$i" ] ?? '';

                    if (($filter_on_value === 'product_id')) {
                        $final_data[ $i ][ 'product_ids' ] = $flexi_rules_set[ "product_ids-$i" ];
                    } elseif ($filter_on_value === 'product_category') {
                        $final_data[ $i ][ 'category_ids' ] = $flexi_rules_set[ "category_ids-$i" ];
                    } elseif ($filter_on_value === 'user_roles') {
                        $final_data[ $i ][ 'roles' ] = $flexi_rules_set[ "roles-$i" ];
                    }
                }
            }
        }
    }
    $merged_data = [  ];

    foreach ($final_data as $entry) {
        $key = $entry[ 'filter_on' ] . '-' . ($entry[ 'include_exclude' ] ?? '');

        if (!isset($merged_data[ $key ])) {
            $merged_data[ $key ] = $entry;
        } else {
            if (in_array($entry[ 'filter_on' ], [ 'user_roles', 'product_id', 'product_category' ])) {
                $field_key = match ($entry[ 'filter_on' ]) {
                    'user_roles' => 'roles',
                    'product_category' => 'category_ids',
                    'product_id' => 'product_ids',
                    default => null,
                };

                // Merge unique values for roles or product_ids
                if ($field_key && isset($entry[ $field_key ])) {
                    $merged_data[ $key ][ $field_key ] = array_unique(array_merge($merged_data[ $key ][ $field_key ] ?? [  ], $entry[ $field_key ]));
                }
            } else {
                continue;
            }
        }
    }

    $final_output = array_values($merged_data);

    // Remove empty entries
    foreach ($final_output as $index => $data) {
        foreach ($data as $key => $value) {
            if (empty($value) && !is_array($value)) {
                unset($final_output[ $index ][ $key ]);
            }
        }
    }

    // Remove empty filters
    $final_output = array_filter($final_output, function ($data) {
        return !empty($data[ 'filter_on' ]);
    });

    $rule_status = isset($flexi_rules_set[ 'status' ]) ? $flexi_rules_set[ 'status' ] : "inactive";
    $rule_name   = $flexi_rules_set[ 'rule_name' ];
    $rule_type   = $flexi_rules_set[ 'rule_type' ];

    $parms = array(
        'rule_name'   => $rule_name,
        'rule_type'   => $rule_type,
        'rule_filter' => json_encode($final_output, true),
        'status'      => $rule_status,
    );

    if ($form_data[ 'id' ] && ($ruleSetID === $form_data[ 'id' ])) {
        $where         = 'id = ' . $ruleSetID;
        $update_result = $flexi_carts_dbqueries->update_db_query('flexi_email_rules', $parms, $where);

        $request_uri = isset($_SERVER[ 'REQUEST_URI' ]) ? esc_url_raw(wp_unslash($_SERVER[ 'REQUEST_URI' ])) : '';
        wp_safe_redirect(add_query_arg('rule-updated', 'true', $request_uri));
        exit;

    } else {
        $insert = $flexi_carts_dbqueries->insert_db_query('flexi_email_rules', $parms);

        $request_uri = isset($_SERVER[ 'REQUEST_URI' ]) ? esc_url_raw(wp_unslash($_SERVER[ 'REQUEST_URI' ])) : '';
        wp_safe_redirect(add_query_arg('rule-created', 'true', $request_uri));
        exit;
    }

}

// Function to get WooCommerce product IDs
function get_woo_prod_ids()
{
    $args = array(
        'limit' => -1,
    );
    $products     = wc_get_products($args);
    $product_list = array();
    foreach ($products as $product) {
        $product_list[ $product->get_id() ] = $product->get_name();
    }
    return $product_list;
}

function fetch_categories()
{
    $categories = get_terms(array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ));
    $category_list = array();
    foreach ($categories as $category) {
        $category_list[ $category->term_id ] = $category->name;
    }
    return $category_list;
}

function get_user_roles()
{
    global $wp_roles;
    $roles = null;
    if ($wp_roles && property_exists($wp_roles, 'roles')) {
        $roles = $wp_roles->roles;
    }
    return $roles;
}

$all_products    = get_woo_prod_ids();
$all_categories  = fetch_categories();
$all_users_roles = get_user_roles();

?>

<div class="wrap">

    <h1><?php echo isset($heading) ? esc_html($heading , 'flexi-abandon-cart-recovery') : esc_html__('Add New Rule', 'flexi-abandon-cart-recovery'); ?>
    </h1>

    <form method="POST">
        <?php wp_nonce_field('flexi_email_rule_set', 'flexi_email_rule_set_data');?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="rule_name"><?php echo esc_html__('Rule Name', 'flexi-abandon-cart-recovery'); ?></label>
                </th>
                <td>
                    <?php $rule_name = isset($form_data[ 'rule_name' ]) ? $form_data[ 'rule_name' ] : "";?>
                    <input type="text" id="rule_name" name="rule_name" value="<?php echo esc_attr($rule_name) ?>"
                        required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="status"><?php echo esc_html__('Status', 'flexi-abandon-cart-recovery'); ?></label>
                </th>
                <td>
                    <?php
$rule_status = isset($form_data[ 'status' ]) ? $form_data[ 'status' ] : '';
$checked     = ('active' === $rule_status) ? 'checked' : '';
?>
                    <input type="checkbox" id="status" name="status" value="active" <?php echo esc_attr($checked); ?>>
                    <?php echo esc_html__('Active', 'flexi-abandon-cart-recovery'); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="type"><?php echo esc_html__('Type', 'flexi-abandon-cart-recovery'); ?></label>
                </th>
                <td>
                    <?php $rule_type = isset($form_data[ 'rule_type' ]) ? $form_data[ 'rule_type' ] : "";?>
                    <select id="type" name="rule_type">
                        <option value="and" <?php echo ("and" === $rule_type) ? "selected" : ""; ?>>
                            <?php echo esc_html__('AND', 'flexi-abandon-cart-recovery'); ?></option>
                        <option value="or" <?php echo ("or" === $rule_type) ? "selected" : ""; ?>>
                            <?php echo esc_html__('OR', 'flexi-abandon-cart-recovery'); ?></option>
                    </select>
                </td>
            </tr>


            <tr>
                <th scope="row">
                    <label for="type"><?php echo esc_html__('Based On ', 'flexi-abandon-cart-recovery'); ?></label>
                    <br><br>
                    <button type="button"
                        id="addBasedon"><?php echo esc_html__('Add', 'flexi-abandon-cart-recovery'); ?></button>
                </th>
                <td>
                    <div class="rule-set-container" id="ruleset_filter">
                        <?php
// Decode the rule_filter data from the form input
$rule_filter = isset($form_data[ 'rule_filter' ]) ? json_decode($form_data[ 'rule_filter' ], true) : "";

if (!empty($rule_filter)) {
    foreach ($rule_filter as $index => $filter_data) {
        $filter_on         = isset($filter_data[ 'filter_on' ]) ? $filter_data[ 'filter_on' ] : "";
        $comparison_select = isset($filter_data[ 'comparison_select' ]) ? $filter_data[ 'comparison_select' ] : "";
        $include_exclude   = isset($filter_data[ 'include_exclude' ]) ? $filter_data[ 'include_exclude' ] : "";
        $product_ids       = isset($filter_data[ 'product_ids' ]) ? $filter_data[ 'product_ids' ] : array();
        $category_ids      = isset($filter_data[ 'category_ids' ]) ? $filter_data[ 'category_ids' ] : array();
        $roles             = isset($filter_data[ 'roles' ]) ? $filter_data[ 'roles' ] : array();
        $amount            = isset($filter_data[ 'amount' ]) ? $filter_data[ 'amount' ] : "1";
        $items_num         = isset($filter_data[ 'items_num' ]) ? $filter_data[ 'items_num' ] : "1";

        ?>
                        <div class="based-row" id="basedRow-<?php echo esc_attr($index); ?>"
                            style="display: flex; align-items: center; gap: 10px; ">
                            <input type="hidden" value="<?php echo esc_attr($index); ?>" id="row_count"
                                name="based_count-<?php echo esc_attr($index); ?>">

                            <!-- Based on select -->
                            <select name="filter-on-<?php echo esc_attr($index); ?>" class="filter-on"
                                style="width: 150px;">
                                <option value="">
                                    <?php echo esc_html__('--Select--', 'flexi-abandon-cart-recovery'); ?>
                                </option>
                                <option value="num_of_items" <?php selected($filter_on, 'num_of_items');?>>
                                    <?php echo esc_html__('Number Of Items', 'flexi-abandon-cart-recovery'); ?>
                                </option>
                                <option value="total_amt" <?php selected($filter_on, 'total_amt');?>>
                                    <?php echo esc_html__('Total Amount', 'flexi-abandon-cart-recovery'); ?>
                                </option>
                                <option value="user_roles" <?php selected($filter_on, 'user_roles');?>>
                                    <?php echo esc_html__('User Roles', 'flexi-abandon-cart-recovery'); ?>
                                </option>
                                <option value="product_id" <?php selected($filter_on, 'product_id');?>>
                                    <?php echo esc_html__('Product ID', 'flexi-abandon-cart-recovery'); ?>
                                </option>
                                <option value="product_category" <?php selected($filter_on, 'product_category');?>>
                                    <?php echo esc_html__('Product Category', 'flexi-abandon-cart-recovery'); ?>
                                </option>
                            </select>

                            <!-- Greater/Less/Equal select-->
                            <div class="comparison-select-container"
                                style="display: <?php echo ('num_of_items' === $filter_on || 'total_amt' === $filter_on) ? 'block' : 'none' ?>">
                                <select name="comparison_select-<?php echo esc_attr($index); ?>" style="width: 120px;">
                                    <option value="greater_than" <?php selected($comparison_select, 'greater_than');?>>
                                        <?php echo esc_html__('Greater Than', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                    <option value="less_than" <?php selected($comparison_select, 'less_than');?>>
                                        <?php echo esc_html__('Less Than', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                    <option value="equal_to" <?php selected($comparison_select, 'equal_to');?>>
                                        <?php echo esc_html__('Equal To', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                    <option value="less_than_n_equals"
                                        <?php selected($comparison_select, 'less_than_n_equals');?>>
                                        <?php echo esc_html__('Less Than & Equal To', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                    <option value="greater_than_n_equals"
                                        <?php selected($comparison_select, 'greater_than_n_equals');?>>
                                        <?php echo esc_html__('Greater Than & Equal To', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                </select>
                            </div>

                            <!-- Include/Exclude select -->
                            <div class="include-exclude-select-container"
                                style="display: <?php echo ('user_roles' === $filter_on || 'product_id' === $filter_on || 'product_category' === $filter_on) ? 'block' : 'none' ?>">
                                <select name="include_exclude-<?php echo esc_attr($index); ?>" style="width: 120px;">
                                    <option value="include" <?php selected($include_exclude, 'include');?>>
                                        <?php echo esc_html__('Include', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                    <option value="exclude" <?php selected($include_exclude, 'exclude');?>>
                                        <?php echo esc_html__('Exclude', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                </select>
                            </div>

                            <!-- Product ID select box -->
                            <div class="product-id-select-container"
                                style="display: <?php echo ('product_id' === $filter_on) ? 'block' : 'none' ?>">
                                <label><?php echo esc_html__('Select Product IDs', 'flexi-abandon-cart-recovery'); ?></label>
                                <select class="product-select" name="product_ids-<?php echo esc_attr($index); ?>[]"
                                    multiple="multiple" style="width: 200px;">
                                    <?php
if ('product_id' === $filter_on) {
            foreach ($all_products as $id => $name) {
                $selected = in_array($id, $product_ids) ? 'selected="selected"' : '';
                echo "<option value='" . esc_attr($id) . "' $selected>" . esc_html($id . "-" . $name) . "</option>";
            }
        }?>
                                </select>
                            </div>

                            <!-- Product Category select box -->
                            <div class="product-category-select-container"
                                style="display: <?php echo ('product_category' === $filter_on) ? 'block' : 'none' ?>">
                                <label><?php echo esc_html__('Select Product Categories', 'flexi-abandon-cart-recovery'); ?></label>
                                <select class="category-select" name="category_ids-<?php echo esc_attr($index); ?>[]"
                                    multiple="multiple" style="width: 200px;">
                                    <?php
if ('product_category' === $filter_on && !empty($all_categories)) {
            foreach ($all_categories as $id => $name) {
                $selected = in_array($id, $category_ids) ? 'selected="selected"' : '';
                echo "<option value='" . esc_attr($id) . "' $selected>" . esc_html($name) . "</option>";
            }
        }?>
                                </select>
                            </div>

                            <!-- User Roles select box -->
                            <div class="user-roles-select-container"
                                style="display: <?php echo ('user_roles' === $filter_on) ? 'block' : 'none' ?>">
                                <label><?php echo esc_html__('Select Roles', 'flexi-abandon-cart-recovery'); ?></label>
                                <select class="roles-select" name="roles-<?php echo esc_attr($index); ?>[]"
                                    multiple="multiple" style="width: 200px;">
                                    <?php
if ('user_roles' === $filter_on && !empty($all_users_roles)) {
            foreach ($all_users_roles as $key => $info) {
                $selected = in_array($key, $roles) ? 'selected ="selected"' : '';
                echo "<option value='" . esc_attr($key) . "' $selected>" . esc_html($info[ 'name' ]) . "</option>";
            }
        }?>

                                </select>
                            </div>

                            <!-- Amount Input box -->
                            <div class="limit-input-container"
                                style="display:<?php echo ('total_amt' === $filter_on) ? 'block' : 'none' ?>">
                                <label><?php echo esc_html__('Enter Amount', 'flexi-abandon-cart-recovery'); ?></label>
                                <input type="number" class="amount-input" name="amount-<?php echo esc_attr($index); ?>"
                                    style="width: 200px;" min="1" value="<?php echo $amount; ?>">
                            </div>

                            <!-- Number of Items Input box -->
                            <div class="num-items-input-container"
                                style="display:<?php echo ('num_of_items' === $filter_on) ? 'block' : 'none' ?>">
                                <label><?php echo esc_html__('Enter Number', 'flexi-abandon-cart-recovery'); ?></label>
                                <input type="number" class="items-number"
                                    name="items_num-<?php echo esc_attr($index); ?>" style="width: 200px;" min="1"
                                    value="<?php echo $items_num; ?>">
                            </div>

                        </div>
                        <?php
}
} else {?>
                        <div class="based-row" id="basedRow-1" style="display: flex; align-items: center; gap: 10px; ">
                            <input type="hidden" value="1" name="based_count-1">

                            <!-- Based on select -->
                            <select name="filter-on-1" class="filter-on" style="width: 150px;">
                                <option value="">
                                    <?php echo esc_html__('--Select--', 'flexi-abandon-cart-recovery'); ?>
                                </option>
                                <option value="num_of_items">
                                    <?php echo esc_html__('Number Of Items', 'flexi-abandon-cart-recovery'); ?>
                                </option>
                                <option value="total_amt">
                                    <?php echo esc_html__('Total Amount', 'flexi-abandon-cart-recovery'); ?>
                                </option>
                                <option value="user_roles">
                                    <?php echo esc_html__('User Roles', 'flexi-abandon-cart-recovery'); ?>
                                </option>
                                <option value="product_id">
                                    <?php echo esc_html__('Product ID', 'flexi-abandon-cart-recovery'); ?>
                                </option>
                                <option value="product_category">
                                    <?php echo esc_html__('Product Category', 'flexi-abandon-cart-recovery'); ?>
                                </option>
                            </select>

                            <!-- Greater/Less/Equal select-->
                            <div class="comparison-select-container" style="display: none;">
                                <select name="comparison_select-1" style="width: 120px;">
                                    <option value="greater_than">
                                        <?php echo esc_html__('Greater Than', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                    <option value="less_than">
                                        <?php echo esc_html__('Less Than', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                    <option value="equal_to">
                                        <?php echo esc_html__('Equal To', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                    <option value="less_than_n_equals">
                                        <?php echo esc_html__('Less Than & Equal To', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                    <option value="greater_than_n_equals">
                                        <?php echo esc_html__('Greater Than & Equal To', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                </select>
                            </div>

                            <!-- Include/Exclude select -->
                            <div class="include-exclude-select-container" style="display: none;">
                                <select name="include_exclude-1" style="width: 120px;">
                                    <option value="include">
                                        <?php echo esc_html__('Include', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                    <option value="exclude">
                                        <?php echo esc_html__('Exclude', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                </select>
                            </div>

                            <!-- Product ID select box -->
                            <div class="product-id-select-container" style="display: none;">
                                <label><?php echo esc_html__('Select Product IDs', 'flexi-abandon-cart-recovery'); ?></label>
                                <select class="product-select" name="product_ids-1[]" multiple="multiple"
                                    style="width: 200px;">
                                </select>
                            </div>

                            <!-- Product Category select box -->
                            <div class="product-category-select-container" style="display: none;">
                                <label><?php echo esc_html__('Select Product Categories', 'flexi-abandon-cart-recovery'); ?></label>
                                <select class="category-select" name="category_ids-1[]" multiple="multiple"
                                    style="width: 200px;">
                                </select>
                            </div>

                            <!-- User Roles select box -->
                            <div class="user-roles-select-container" style="display: none;">
                                <label><?php echo esc_html__('Select Roles', 'flexi-abandon-cart-recovery'); ?></label>
                                <select class="roles-select" name="roles-1[]" multiple="multiple" style="width: 200px;">
                                </select>
                            </div>

                            <!-- Amount Input box -->
                            <div class="limit-input-container" style="display: none;">
                                <label><?php echo esc_html__('Enter Amount', 'flexi-abandon-cart-recovery'); ?></label>
                                <input type="number" class="amount-input" name="amount-1" style="width: 200px;" min="1"
                                    value="1">
                            </div>

                            <!-- Number of Items Input box -->
                            <div class="num-items-input-container" style="display: none;">
                                <label><?php echo esc_html__('Enter Number', 'flexi-abandon-cart-recovery'); ?></label>
                                <input type="number" class="items-number" name="items_num-1" style="width: 200px;"
                                    min="1" value="1">
                            </div>

                        </div>
                        <?php
}
?>
                    </div>
                </td>
            </tr>
        </table>

        <button type="submit" class="button button-primary"
            name="save_rule_set"><?php echo esc_html__('Save Rule', 'flexi-abandon-cart-recovery'); ?></button>
    </form>
</div>