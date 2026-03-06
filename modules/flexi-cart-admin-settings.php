<?php
/**
 * Provide an admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link  https://abandoned-cart-recovery
 * @since 1.0.0
 *
 * @package    Abandoned_Cart_Recovery
 * @subpackage Abandoned_Cart_Recovery/modules
 **/

if (isset($_GET[ 'admin-setting-updated' ]) && 'true' === $_GET[ 'admin-setting-updated' ]) {
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings Saved Successfully.', 'flexi-abandon-cart-recovery') . '</p></div>';
}

if (isset($_SERVER[ 'REQUEST_METHOD' ]) && 'POST' === $_SERVER[ 'REQUEST_METHOD' ] && isset($_POST[ 'save_admin_setting' ])) {
    // Verify the nonce for security.
    if (!isset($_POST[ 'admin_abandon_setings' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'admin_abandon_setings' ])), 'flexi_admin_notification_setings')) {
        return;
    }
    // Sanitize and save the settings.
    $admin_notifi_settings_array = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    echo "<pre>";
    print_r($admin_notifi_settings_array);

    $purchase_mail = isset($admin_notifi_settings_array[ 'purchase_mail' ]) ? $admin_notifi_settings_array[ 'purchase_mail' ] = 'on' : $admin_notifi_settings_array[ 'purchase_mail' ] = 'off';

    $receive_report = isset($admin_notifi_settings_array[ 'receive_report' ]) ? $admin_notifi_settings_array[ 'receive_report' ] = 'on' : $admin_notifi_settings_array[ 'receive_report' ] = 'off';

    // Update Setting Option.
    $array_to_save = wp_json_encode($admin_notifi_settings_array, true);
    update_option('flexi_abandon_cart_plugin_admin_notify_setting', $array_to_save);

    // Redirect to the same page with a 'admin-settings-updated' parameter to show the success message.
    $request_uri = isset($_SERVER[ 'REQUEST_URI' ]) ? esc_url_raw(wp_unslash($_SERVER[ 'REQUEST_URI' ])) : '';
    wp_safe_redirect(add_query_arg('admin-setting-updated', 'true', $request_uri));
    exit;
}
?>

<div class='wrap woocommerce'>
    <form id="abandon_cart_form" method="POST">
        <?php wp_nonce_field('flexi_admin_notification_setings', 'admin_abandon_setings');?>

        <h2><?php echo esc_html__('Admin Notification', 'flexi-abandon-cart-recovery');
$admin_email = get_option('admin_email'); ?>
        </h2>


        <div class='wrap woocommerce'>
            <form id="abandon_cart_form" method="POST">
                <?php wp_nonce_field('flexi_admin_notification_setings', 'admin_abandon_setings');?>

                <table class="form-table configuration_settings">
                    <!-- Enable receiving Notification when purchase is made -->
                    <tbody>
                        <tr>
                            <th scope="row" class="titledesc">
                                <?php echo esc_html__('Receive Notification When Purchased', 'flexi-abandon-cart-recovery'); ?>
                            </th>
                            <td class="forminp forminp-checkbox">
                                <label for="purchase_mail">
                                    <?php
$purchase_mail = isset($saved_settings[ 'purchase_mail' ]) ? $saved_settings[ 'purchase_mail' ] : '';
$checked       = 'off' === $purchase_mail ? '' : 'checked';
?>
                                    <input name="purchase_mail" id="purchase_mail" value="on" type="checkbox"
                                        <?php echo esc_html($checked); ?>>
                                    <?php echo esc_html__('Enable to receive email about every cart purchase.', 'flexi-abandon-cart-recovery'); ?>
                                </label>
                            </td>
                        </tr>
                    </tbody>
                    <tbody id="toggle_diplay_purchase_mail" style="display: none;">
                        <!--PUrchase Send Email TO -->
                        <tr>
                            <th scope="row" class="titledesc">
                                <label
                                    for="purchase_mail_to"><?php echo esc_html__('Send Email To', 'flexi-abandon-cart-recovery'); ?></label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name="purchase_mail_to" id="purchase_mail_to" type="email"
                                    value="<?php echo isset($saved_settings[ 'purchase_mail_to' ]) ? esc_attr($saved_settings[ 'purchase_mail_to' ]) : $admin_email; ?>">
                            </td>
                        </tr>

                        <!-- Send Email suject -->
                        <tr>
                            <th scope="row" class="titledesc">
                                <label
                                    for="purc_email_subject"><?php echo esc_html__('Email Subject', 'flexi-abandon-cart-recovery'); ?></label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name="purc_email_subject" id="purc_email_subject" type="text"
                                    value="<?php echo isset($saved_settings[ 'purc_email_subject' ]) ? esc_attr($saved_settings[ 'purc_email_subject' ]) : ''; ?>">
                            </td>
                        </tr>

                        <!-- Email Body -->
                        <tr>
                            <th scope="row" class="titledesc">
                                <label
                                    for="purchase_email_body"><?php echo esc_html__('Email Body ', 'flexi-abandon-cart-recovery'); ?></label>
                            </th>
                            <td class="forminp forminp-checkbox">
                                <?php
$initial_data = isset($saved_settings[ 'purchase_email_body' ])
? wp_kses_post($saved_settings[ 'purchase_email_body' ])
: "";

$editor_id = 'purchase_email_body';
$settings  = array(
    'media_buttons' => false,
    'textarea_rows' => 5,
    'tinymce'       => array(
        'theme_advanced_buttons1' => 'bold,italic,underline,|,bullist,numlist,blockquote,|,link,unlink,|,spellchecker,fullscreen,|,formatselect,styleselect',
    ),
);
wp_editor($initial_data, $editor_id, $settings);
?>
                            </td>
                        </tr>

                        <!-- Send Test Mail-->
                        <tr>
                            <th scope="row" class="titledesc">
                                <label
                                    for="purchase_mail_to"><?php echo esc_html__('Send Test Email', 'flexi-abandon-cart-recovery'); ?></label>
                            </th>
                            <td class="forminp forminp-checkbox">
                                <input id="purchase_mail_to" type="text"
                                    value="<?php echo isset($saved_settings[ 'purchase_mail_to' ]) ? esc_attr($saved_settings[ 'purchase_mail_to' ]) : $admin_email; ?>"
                                     >
                                &nbsp; &nbsp; &nbsp;
                                <button class="button button-primary"
                                    id="purchase_test_mail"><?php echo esc_html__('Send Test Mail', 'flexi-abandon-cart-recovery'); ?></button>
                            </td>
                        </tr>
                        <!-- </tbody> -->
                    </tbody>

                    <tbody>
                        <!-- Enable Receiving Report -->
                        <tr>
                            <th scope="row" class="titledesc">
                                <?php echo esc_html__('Receive Report', 'flexi-abandon-cart-recovery'); ?>
                            </th>
                            <td class="forminp forminp-checkbox">
                                <label for="receive_report">
                                    <?php
$receive_report = isset($saved_settings[ 'receive_report' ]) ? $saved_settings[ 'receive_report' ] : '';
$checked        = 'on' === $receive_report ? 'checked' : '';
?>
                                    <input name="receive_report" id="receive_report" type="checkbox"
                                        <?php echo esc_html($checked); ?>>
                                    <?php echo esc_html__('Enable to receive email about cart summary.', 'flexi-abandon-cart-recovery'); ?>
                                </label>
                            </td>
                        </tr>
                    </tbody>
                    <tbody id="toggle_diplay_admin_noti" style="display: none;">
                        <!-- reprt days -->
                        <tr>
        <th scope="row" class="titledesc">
            <label for="admin_report_duration"><?php echo esc_html__('Set Report Days', 'flexi-abandon-cart-recovery'); ?></label>
        </th>
        <td class="forminp forminp-text">
            <input name="admin_report_duration" id="admin_report_duration" type="number" min="1"
                value="<?php echo isset($saved_settings['admin_report_duration']) ? esc_attr($saved_settings['admin_report_duration']) : ''; ?>">
            &nbsp; <b><?php echo esc_html__('Days', 'flexi-abandon-cart-recovery'); ?></b>
        </td>
    </tr>
                        <!-- Send Email TO -->
                        <tr>
                            <th scope="row" class="titledesc">
                                <label
                                    for="admin_send_to"><?php echo esc_html__('Send Email To', 'flexi-abandon-cart-recovery'); ?></label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name="admin_send_to" id="admin_send_to" type="email"
                                    value="<?php echo isset($saved_settings[ 'admin_send_to' ]) ? esc_attr($saved_settings[ 'admin_send_to' ]) : $admin_email; ?>">
                            </td>
                        </tr>

                        <!-- Send Email suject -->
                        <tr>
                            <th scope="row" class="titledesc">
                                <label
                                    for="admin_email_subject"><?php echo esc_html__('Email Subject', 'flexi-abandon-cart-recovery'); ?></label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name="admin_email_subject" id="admin_email_subject" type="text"
                                    value="<?php echo isset($saved_settings[ 'admin_email_subject' ]) ? esc_attr($saved_settings[ 'admin_email_subject' ]) : ''; ?>">
                            </td>
                        </tr>

                        <!-- Email Body -->
                        <tr>
                            <th scope="row" class="titledesc">
                                <label
                                    for="admin_email_body"><?php echo esc_html__('Email Body ', 'flexi-abandon-cart-recovery'); ?></label>
                            </th>
                            <td class="forminp forminp-inputbox">
                                <div id="report_container">
                                    <div id="report_excel">
                                        <table id="excel_table">
                                            <tbody id="excel_table_body">
                                                <tr>
                                                    <th scope="row" class="titledesc">
                                                        <label
                                                            for="excel_columns"><?php echo esc_html__('Select Excel Columns', 'flexi-abandon-cart-recovery'); ?></label>
                                                    </th>
                                                    <td>
                                                        <div id="table-controls">
                                                            <select id="excel_select_col" multiple="multiple"
                                                                style="width:500px;" name="excel_selected_columns[]">
                                                                <option value="customer_name">
                                                                    <?php echo esc_html__('Customer Name', 'flexi-abandon-cart-recovery'); ?>
                                                                </option>
                                                                <option value="email">
                                                                    <?php echo esc_html__('Customer Email', 'flexi-abandon-cart-recovery'); ?>
                                                                </option>
                                                                <option value="abandon_time">
                                                                    <?php echo esc_html__('Cart Abandon Time', 'flexi-abandon-cart-recovery'); ?>
                                                                </option>
                                                                <option value="abandon_cart_value">
                                                                    <?php echo esc_html__('Abandon Cart Value', 'flexi-abandon-cart-recovery'); ?>
                                                                </option>
                                                                <option value="num_of_items">
                                                                    <?php echo esc_html__('Number of Items', 'flexi-abandon-cart-recovery'); ?>
                                                                </option>
                                                                <option value="cart_status">
                                                                    <?php echo esc_html__('Cart Status', 'flexi-abandon-cart-recovery'); ?>
                                                                </option>
                                                                <option value="times_mail_send">
                                                                    <?php echo esc_html__('Times Mail Sent', 'flexi-abandon-cart-recovery'); ?>
                                                                </option>
                                                                <option value="mail_opened">
                                                                    <?php echo esc_html__('Times Mail Opened', 'flexi-abandon-cart-recovery'); ?>
                                                                </option>
                                                                <option value="link_opened">
                                                                    <?php echo esc_html__('Times Link Opened', 'flexi-abandon-cart-recovery'); ?>
                                                                </option>
                                                                <option value="coupon">
                                                                    <?php echo esc_html__('Coupons', 'flexi-abandon-cart-recovery'); ?>
                                                                </option>
                                                                <option value="expired_coupons">
                                                                    <?php echo esc_html__('Expired Coupons', 'flexi-abandon-cart-recovery'); ?>
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <tr class="display_selected_columns">
                                                    <td colspan="3">
                                                        <div id="selected-excel-columns">
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Send Test Mail-->
                        <tr>
                            <th scope="row" class="titledesc">
                                <label
                                    for="test_mail"><?php echo esc_html__('Send Test Email', 'flexi-abandon-cart-recovery'); ?></label>
                            </th>
                            <td class="forminp forminp-checkbox">
                                <input id="admin_send_test_to" type="text"
                                    value="<?php echo isset($saved_settings[ 'admin_send_test_to' ]) ? esc_attr($saved_settings[ 'admin_send_test_to' ]) : $admin_email; ?>">
                                &nbsp; &nbsp; &nbsp;
                                <button class="button button-primary" id="admin_test_mail">
                                    <?php echo esc_html__('Send Test Mail', 'flexi-abandon-cart-recovery'); ?>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>


                <button class="button button-primary" name="save_admin_setting" id="save_admin_setting">Save</button>
            </form>
        </div>