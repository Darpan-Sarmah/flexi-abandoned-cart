<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugi
 * @
 *
 * @link  https://abandoned-cart-recovery
 * @since 1.0.0
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/modules/sub_mmodules
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

$flexi_carts_dbqueries = new Flexi_Database_Queries();
if (isset($_GET[ 'panel' ]) && 'edit' === $_GET[ 'panel' ] && isset($_GET[ 'couponID' ])) {
    $couponID      = sanitize_text_field(wp_unslash($_GET[ 'couponID' ]));
    $query_results = $flexi_carts_dbqueries->select_db_query('flexi_cart_coupons', '*', "", 'id = ' . $couponID);
    $form_data     = $query_results[ 0 ];

}
if (isset($_GET[ 'coupon-created' ]) && 'true' === $_GET[ 'coupon-created' ]) {
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Coupon Created Successfully.', 'flexi-abandon-cart-recovery') . '</p></div>';
} elseif (isset($_GET[ 'coupon-updated' ]) && 'true' === $_GET[ 'coupon-updated' ]) {
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Coupon Updated Successfully.', 'flexi-abandon-cart-recovery') . '</p></div>';
}

if (isset($_SERVER[ 'REQUEST_METHOD' ]) && 'POST' === $_SERVER[ 'REQUEST_METHOD' ]) {
    // Verify the nonce for security.

    if (!isset($_POST[ 'flexi_cart_dynamic_coupon_data' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'flexi_cart_dynamic_coupon_data' ])), 'flexi_cart_dynamic_coupon')) {
        return;
    }

    // phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified above
    $raw_post          = wp_unslash( $_POST );
    $flexi_coupon_data = array_map( 'sanitize_text_field', array_filter( $raw_post, 'is_string' ) );
    // Preserve array fields without sanitize_text_field stripping them.
    foreach ( $raw_post as $key => $value ) {
        if ( is_array( $value ) ) {
            $flexi_coupon_data[ $key ] = array_map( 'sanitize_text_field', $value );
        }
    }
    // phpcs:enable WordPress.Security.NonceVerification.Missing
    $coupon_filters    = [  ];

    foreach ($flexi_coupon_data as $key => $value) {
        if (preg_match('/based-on-(\d+)/', $key, $matches)) {
            $i = $matches[ 1 ];
            if (!empty($flexi_coupon_data[ "based-on-$i" ]) && (!empty($flexi_coupon_data[ "product_ids-$i" ]) || $flexi_coupon_data[ "category_ids-$i" ])) {
                $coupon_filters[ $i ] = [
                    'based_on'        => $flexi_coupon_data[ "based-on-$i" ],
                    'include_exclude' => $flexi_coupon_data[ "include_exclude-$i" ],
                    'product_ids'     => $flexi_coupon_data[ "product_ids-$i" ] ?? [  ],
                    'category_ids'    => $flexi_coupon_data[ "category_ids-$i" ] ?? [  ],
                 ];
            }
        }
    }

    $coupon_status     = isset($flexi_coupon_data[ 'flexi_coupon_status' ]) ? $flexi_coupon_data[ 'flexi_coupon_status' ] : "inactive";
    $coupon_code       = $flexi_coupon_data[ 'flexi_coupon_code' ];
    $coupon_dis_type   = $flexi_coupon_data[ 'flexi_disc_type' ];
    $coupon_amount     = $flexi_coupon_data[ 'flexi_disc_amt' ];
    $is_individual_use = isset($flexi_coupon_data[ 'flexi_individual_use' ]) ? 1 : 0;

    if (isset($flexi_coupon_data[ 'flexi_usage_limit' ]) && "" !== $flexi_coupon_data[ 'flexi_usage_limit' ]) {
        $limit = isset($flexi_coupon_data[ 'flexi_coupon_limit' ]) ? $flexi_coupon_data[ 'flexi_coupon_limit' ] : "";
    }
    $expiry_date       = isset($flexi_coupon_data[ 'expiry_date' ]) ? $flexi_coupon_data[ 'expiry_date' ] : "0000-00-00T00:00";
    $restricted_emails = isset($flexi_coupon_data[ 'restricted_emails' ]) ? array_map('trim', explode(',', $flexi_coupon_data[ 'restricted_emails' ])) : [  ];

    $parms = array(
        'status'            => $coupon_status,
        'name_code'         => $coupon_code,
        'discount_type'     => $coupon_dis_type,
        'discount_amt'      => $coupon_amount,
        'is_individual_use' => $is_individual_use,
        'coupon_limit'      => isset($limit) ? $limit : "0",
        'coupon_filter'     => json_encode($coupon_filters, true),
        'expiry_date'       => $expiry_date,
        'restricted_emails' => json_encode($restricted_emails),
    );

    if ($form_data[ 'id' ] && ($couponID === $form_data[ 'id' ])) {
        $where         = 'id = ' . $couponID;
        $update_result = $flexi_carts_dbqueries->update_db_query('flexi_cart_coupons', $parms, $where);

        $request_uri = isset($_SERVER[ 'REQUEST_URI' ]) ? esc_url_raw(wp_unslash($_SERVER[ 'REQUEST_URI' ])) : '';
        wp_safe_redirect(add_query_arg('coupon-updated', 'true', $request_uri));
        exit;

    } else {
        $insert = $flexi_carts_dbqueries->insert_db_query('flexi_cart_coupons', $parms);

        $request_uri = isset($_SERVER[ 'REQUEST_URI' ]) ? esc_url_raw(wp_unslash($_SERVER[ 'REQUEST_URI' ])) : '';
        wp_safe_redirect(add_query_arg('coupon-created', 'true', $request_uri));
        exit;
    }

}

function get_all_products()
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

function get_all_categories()
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

$all_products   = get_all_products();
$all_categories = get_all_categories();

?>

<div class="wrap">
    <h1><?php
echo isset($couponID) ? esc_html__('Edit Dynamic Flexi Coupons', 'flexi-abandon-cart-recovery') : esc_html__('Create Dynamic Flexi Coupons', 'flexi-abandon-cart-recovery'); ?>
    </h1>

    <form method="POST" id="flexi_dynamic_coupon">
        <?php wp_nonce_field('flexi_cart_dynamic_coupon', 'flexi_cart_dynamic_coupon_data');?>
        <table class="form-table">
            <tbody>

                <tr>
                    <th>
                        <label for="flexi_coupon_status">
                            <?php echo esc_html__('Coupon Status', 'flexi-abandon-cart-recovery'); ?>
                        </label>
                    </th>
                    <td>
                        <?php
$coupon_status = isset($form_data[ 'status' ]) ? $form_data[ 'status' ] : '';
$checked       = ('active' === $coupon_status) ? 'checked' : '';?>
                        <input type="checkbox" id="flexi_coupon_status" name="flexi_coupon_status" value="active"
                            <?php echo esc_attr($checked); ?>>
                    </td>
                </tr>

                <tr>
                    <th>
                        <label for="flexi_coupon_code">
                            <?php echo esc_html__('Coupon Code', 'flexi-abandon-cart-recovery'); ?>
                        </label>
                    </th>
                    <td>
                        <?php $coupon_name = isset($form_data[ 'name_code' ]) ? $form_data[ 'name_code' ] : "";?>
                        <input type="text" id="flexi_coupon_code" name="flexi_coupon_code"
                            value="<?php echo esc_attr($coupon_name); ?>">
                        <p class="description">
                            <?php echo esc_html__('Enter the code of the coupon', 'flexi-abandon-cart-recovery'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th>
                        <label for="flexi_disc_type">
                            <?php echo esc_html__('Coupon Discount Type', 'flexi-abandon-cart-recovery'); ?>
                        </label>
                    </th>
                    <td>
                        <?php $discount_type = isset($form_data[ 'discount_type' ]) ? $form_data[ 'discount_type' ] : "";?>
                        <select id="flexi_disc_type" name="flexi_disc_type">
                            <option value="fixed_price"
                                <?php echo ("fixed_price" === $discount_type) ? "selected" : ""; ?>>
                                <?php echo esc_html__('Fixed Price', 'flexi-abandon-cart-recovery'); ?>
                            </option>

                            <option value="percentage"
                                <?php echo ("percentage" === $discount_type) ? "selected" : ""; ?>>
                                <?php echo esc_html__('Percentage', 'flexi-abandon-cart-recovery'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th>
                        <label for="flexi_disc_amt">
                            <?php echo esc_html__('Coupon Discount Amount', 'flexi-abandon-cart-recovery'); ?>
                        </label>
                    </th>
                    <td>
                        <?php $coupon_amt = isset($form_data[ 'discount_amt' ]) ? $form_data[ 'discount_amt' ] : "";?>
                        <input type="number" id="flexi_disc_amt" name="flexi_disc_amt" min="0"
                            value="<?php echo esc_attr($coupon_amt); ?>">
                    </td>
                </tr>

                <tr>
                    <th>
                        <label for="flexi_individual_use">
                            <?php echo esc_html__('Individual Use Only', 'flexi-abandon-cart-recovery'); ?>
                        </label>
                    </th>
                    <td>
                        <?php
$is_individual_use = isset($form_data[ 'is_individual_use' ]) && "1" === $form_data[ 'is_individual_use' ] ? "active" : "inactive";
$checked           = ('active' === $is_individual_use) ? 'checked' : '';
?>
                        <input type="checkbox" id="flexi_individual_use" name="flexi_individual_use" value="active"
                            <?php echo esc_attr($checked); ?>>
                        <p class="description">
                            <?php echo esc_html__('Turn on, if it can be used for one and only one purchase.', 'flexi-abandon-cart-recovery'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th>
                        <label for="flexi_filter">
                            <?php echo esc_html__('Filter ', 'flexi-abandon-cart-recovery'); ?>
                        </label>
                        <br>
                        <button type="button"
                            id="addFilter"><?php echo esc_html__('Add Filter', 'flexi-abandon-cart-recovery'); ?></button>
                    </th>
                    <td>
                        <div class="filter-container" id="filterForm">
                            <?php
$coupon_filter = isset($form_data[ 'coupon_filter' ]) ? json_decode($form_data[ 'coupon_filter' ], true) : "";

if (!empty($coupon_filter)) {
    foreach ($coupon_filter as $index => $filter_data) {
        $based_on        = isset($filter_data[ 'based_on' ]) ? $filter_data[ 'based_on' ] : 1;
        $include_exclude = isset($filter_data[ 'include_exclude' ]) ? $filter_data[ 'include_exclude' ] : 1;
        $product_ids     = isset($filter_data[ 'product_ids' ]) ? $filter_data[ 'product_ids' ] : array();
        $category_ids    = isset($filter_data[ 'category_ids' ]) ? $filter_data[ 'category_ids' ] : array();
        ?>

                            <div class="filter-row" id="filterRow-<?php echo esc_attr($index); ?>"
                                style="display: flex; align-items: center; gap: 10px;">
                                <input type="hidden" value="<?php echo esc_attr($index); ?>" id="filter_count"
                                    name="filter_count-<?php echo esc_attr($index); ?>">

                                <!-- Based on select -->
                                <select name="based-on-<?php echo esc_attr($index); ?>" class="based-on"
                                    style="width: 150px;">
                                    <option value="">
                                        <?php echo esc_html__('--Select--', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                    <option value="product_id" <?php selected($based_on, 'product_id');?>>
                                        <?php echo esc_html__('Product ID', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                    <option value="product_category" <?php selected($based_on, 'product_category');?>>
                                        <?php echo esc_html__('Product Category', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                </select>

                                <!-- Include/Exclude select -->
                                <div class="include-exclude-select-container"
                                    style="display: <?php echo $based_on == 'product_id' || $based_on == 'product_category' ? 'block' : 'none'; ?>">
                                    <select name="include_exclude-<?php echo esc_attr($index); ?>"
                                        style="width: 120px;">
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
                                    style="display: <?php echo $based_on == 'product_id' ? 'block' : 'none'; ?>">
                                    <label><?php echo esc_html__('Select Product IDs', 'flexi-abandon-cart-recovery'); ?></label>
                                    <select class="product-select" name="product_ids-<?php echo esc_attr($index); ?>[]"
                                        multiple="multiple" style="width: 200px;">
                                        <?php
foreach ($all_products as $product_id => $product_name) {
            $selected = in_array($product_id, $product_ids) ? 'selected="selected"' : '';
            echo "<option value='" . esc_attr($product_id) . "' $selected>" . esc_html($product_name) . "</option>";
        }
        ?>
                                    </select>
                                </div>

                                <!-- Product Category select box -->
                                <div class="product-category-select-container"
                                    style="display: <?php echo $based_on == 'product_category' ? 'block' : 'none'; ?>">
                                    <label><?php echo esc_html__('Select Product Categories', 'flexi-abandon-cart-recovery'); ?></label>
                                    <select class="category-select"
                                        name="category_ids-<?php echo esc_attr($index); ?>[]" multiple="multiple"
                                        style="width: 200px;">
                                        <?php
foreach ($all_categories as $category_id => $category_name) {
            $selected = in_array($category_id, $category_ids) ? 'selected="selected"' : '';
            echo "<option value='" . esc_attr($category_id) . "' $selected>" . esc_html($category_name) . "</option>";
        }
        ?>
                                    </select>
                                </div>
                            </div>

                            <?php
}
} else {
    ?>
                            <!-- Default empty filter display -->
                            <div class="filter-row" id="filterRow-1"
                                style="display: flex; align-items: center; gap: 10px;">
                                <input type="hidden" value="1" name="filter_count-1">

                                <!-- Based on select -->
                                <select name="based-on-1" class="based-on" style="width: 150px;">
                                    <option value="">
                                        <?php echo esc_html__('--Select--', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                    <option value="product_id">
                                        <?php echo esc_html__('Product ID', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                    <option value="product_category">
                                        <?php echo esc_html__('Product Category', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                </select>

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
                            </div>
                            <?php
}
?>
                        </div>
                    </td>
                </tr>


                <tr>
                    <th>
                        <label for="flexi_usage_limit">
                            <?php echo esc_html__('Usage Limit', 'flexi-abandon-cart-recovery'); ?>
                        </label>
                    </th>
                    <td>
                        <?php $coupon_limit = isset($form_data[ 'coupon_limit' ]) ? $form_data[ 'coupon_limit' ] : "0";?>
                        <input type="checkbox" id="flexi_usage_limit" name="flexi_usage_limit"
                            <?php echo ("0" !== $coupon_limit) ? 'checked' : ""; ?>>
                        <br>
                        <br>
                        <input type="number" id="flexi_coupon_limit" name="flexi_coupon_limit" style="display:none;"
                            value="<?php echo esc_attr($coupon_limit); ?>">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="flexi_expiry">
                            <?php echo esc_html__('Coupon Expiry Date', 'flexi-abandon-cart-recovery'); ?>
                        </label>
                    </th>
                    <td>
                        <?php
$current_date = current_time('Y-m-d\TH:i');
$expiry_date  = isset($form_data[ 'expiry_date' ]) ? $form_data[ 'expiry_date' ] : "0000-00-00T00:00";
?>
                        <input type="datetime-local" id="expiry_date" name="expiry_date"
                            value="<?php echo esc_attr($expiry_date) ?>" min="<?php echo esc_attr($current_date); ?>" />
                    </td>
                </tr>

                <tr>
    <th>
        <label for="restricted_emails">
            <?php echo esc_html__('Restrict Emails', 'flexi-abandon-cart-recovery'); ?>
        </label>
    </th>
    <td>
        <?php
        $restricted_emails = isset($form_data['restricted_emails']) ? $form_data['restricted_emails'] : '';

        $restricted_emails_content = '';

        if (!empty($restricted_emails)) {
            $decoded_emails = json_decode($restricted_emails, true);
            if (is_array($decoded_emails)) {
                $restricted_emails_content = implode(",", array_map('trim', $decoded_emails));
            }
        }
        $restricted_emails_content = trim($restricted_emails_content);
        ?>
        <textarea id="restricted_emails" name="restricted_emails" rows="5" style="width: 100%;">
            <?php echo esc_html__($restricted_emails_content); ?>
        </textarea>
        <p class="description">
            <?php echo esc_html__('Enter email addresses separated by commas.', 'flexi-abandon-cart-recovery'); ?>
        </p>
    </td>
</tr>

            </tbody>
        </table>

        <button class="button button-primary" id="save_coupon">Save</button>
    </form>
</div>