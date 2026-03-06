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
 */

// Fetching the saved setting.
$option_saved   = get_option('flexi_abandon_cart_plugin_global_setting');
$saved_settings = json_decode($option_saved, true);

// Check if settings were saved and a 'saved' parameter is in the URL.
if (isset($_GET[ 'settings-updated' ]) && 'true' === $_GET[ 'settings-updated' ]) {
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings Saved Successfully.', 'flexi-abandon-cart-recovery') . '</p></div>';
}

if (isset($_SERVER[ 'REQUEST_METHOD' ]) && 'POST' === $_SERVER[ 'REQUEST_METHOD' ] && isset($_POST[ 'submit' ])) {
    // Verify the nonce for security.
    if (!isset($_POST[ 'cart_abandon_setings' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'cart_abandon_setings' ])), 'flexi_abandon_cart_plugin_settings')) {
        return;
    }
    // Sanitize and save the settings.
    $aban_cart_rec_setting_array = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if (isset($aban_cart_rec_setting_array[ 'enable_tracking' ])) {
        $aban_cart_rec_setting_array[ 'enable_tracking' ] = 'on';
    } else {
        $aban_cart_rec_setting_array[ 'enable_tracking' ] = 'off';
    }

    if (isset($aban_cart_rec_setting_array[ 'force_guest_login' ])) {
        $aban_cart_rec_setting_array[ 'force_guest_login' ] = 'on';
    } else {
        $aban_cart_rec_setting_array[ 'force_guest_login' ] = 'off';
    }

    // Language Switch.
    if (isset($aban_cart_rec_setting_array[ 'email_and_plugin_language' ])) {

        $email_and_plugin_language = sanitize_text_field($aban_cart_rec_setting_array[ 'email_and_plugin_language' ]);
        switch_to_locale($email_and_plugin_language);
        update_option('aban_cart_rec_language', $email_and_plugin_language);

        'en_US' === $email_and_plugin_language ? $email_and_plugin_language = '' : $email_and_plugin_language;
        update_option('WPLANG', $email_and_plugin_language);
    }

    // Save the force guest login setting as a standalone option for easy retrieval.
    $force_guest_login = isset($aban_cart_rec_setting_array['flexi_force_guest_login']) ? 'on' : 'off';
    update_option('flexi_force_guest_login', $force_guest_login);

    // Update Setting Option.
    $array_to_save = wp_json_encode($aban_cart_rec_setting_array, true);
    update_option('flexi_abandon_cart_plugin_global_setting', $array_to_save);

    // Redirect to the same page with a 'settings-updated' parameter to show the success message.
    $request_uri = isset($_SERVER[ 'REQUEST_URI' ]) ? esc_url_raw(wp_unslash($_SERVER[ 'REQUEST_URI' ])) : '';
    wp_safe_redirect(add_query_arg('settings-updated', 'true', $request_uri));
    exit;
}
?>

<div class='wrap woocommerce'>
    <form id="abandon_cart_form" method="POST">
        <?php wp_nonce_field('flexi_abandon_cart_plugin_settings', 'cart_abandon_setings');?>

        <h2><?php echo esc_html__('Configuration Settings', 'flexi-abandon-cart-recovery'); ?></h2>

        <table class="form-table configuration_settings">
            <tbody>
                <!-- Enable Tracking -->
                <tr>
                    <th scope="row" class="titledesc">
                        <?php echo esc_html__('Enable Tracking', 'flexi-abandon-cart-recovery'); ?>
                    </th>
                    <td class="forminp forminp-checkbox">
                        <label for="enable_tracking">
                            <?php
// If saved settings exist, use them. Otherwise, default to 'on'.
$enable_tracking = isset($saved_settings[ 'enable_tracking' ]) ? $saved_settings[ 'enable_tracking' ] : 'on';
$checked         = 'on' === $enable_tracking ? 'checked' : '';
?>
                            <input name="enable_tracking" id="enable_tracking" type="checkbox" value="on"
                                <?php echo esc_html($checked); ?>>
                            <?php echo esc_html__('Enable to start tracking abandoned cart', 'flexi-abandon-cart-recovery'); ?>
                        </label>
                    </td>
                </tr>

                <!-- Force Guest Users to Login -->
                <tr>
                    <th scope="row" class="titledesc">
                        <?php echo esc_html__('Force Guest Users to Login', 'flexi-abandon-cart-recovery'); ?>
                    </th>
                    <td class="forminp forminp-checkbox">
                        <label for="force_guest_login">
                            <?php
$force_guest_login = isset($saved_settings[ 'force_guest_login' ]) ? $saved_settings[ 'force_guest_login' ] : 'off';
$checked           = 'on' === $force_guest_login ? 'checked' : '';
?>
                            <input name="force_guest_login" id="force_guest_login" type="checkbox" value="on"
                                <?php echo esc_html($checked); ?>>
                            <?php echo esc_html__('Require guest users to log in before viewing prices or purchasing. Disable to allow guest cart tracking.', 'flexi-abandon-cart-recovery'); ?>
                        </label>
                    </td>
                </tr>

                <!-- Accept Valid Email -->
                <tr>
                    <th scope="row" class="titledesc">
                        <?php echo esc_html__('Capture Valid Email Format', 'flexi-abandon-cart-recovery'); ?>
                    </th>
                    <td class="forminp forminp-checkbox">
                        <label for="capture_valid_email">
                            <?php
// If saved settings exist, use them. Otherwise, default to 'on'.
$capture_valid_email = isset($saved_settings[ 'capture_valid_email' ]) ? $saved_settings[ 'capture_valid_email' ] : 'on';
$checked             = 'on' === $capture_valid_email ? 'checked' : '';
?>
                            <input name="capture_valid_email" id="capture_valid_email" type="checkbox" value="on"
                                <?php echo esc_html($checked); ?>>
                        </label>
                    </td>
                </tr>
                <!-- Cart Abandon Time -->
                <tr>
                    <th scope="row" class="titledesc">
                        <label
                            for="cart_abandon_time"><?php echo esc_html__('Cart abandoned track after time', 'flexi-abandon-cart-recovery'); ?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="cart_abandon_time" id="cart_abandon_time" type="number"
                            value="<?php echo isset($saved_settings[ 'cart_abandon_time' ]) ? esc_attr($saved_settings[ 'cart_abandon_time' ]) : ''; ?>">
                        &nbsp; &nbsp;
                        <select name="cart_abandon_time_duration" id="cart_abandon_time_duration">
                            <option value="seconds"
                                <?php echo (isset($saved_settings[ 'cart_abandon_time_duration' ]) && 'seconds' === $saved_settings[ 'cart_abandon_time_duration' ]) ? 'selected' : ''; ?>>
                                <?php echo esc_html__('Seconds', 'flexi-abandon-cart-recovery'); ?></option>

                            <option value="minutes"
                                <?php echo (!isset($saved_settings[ 'cart_abandon_time_duration' ]) || 'minutes' === $saved_settings[ 'cart_abandon_time_duration' ]) ? 'selected' : ''; ?>>
                                <?php echo esc_html__('Minutes', 'flexi-abandon-cart-recovery'); ?>
                            </option>

                            <option value="hours"
                                <?php echo (isset($saved_settings[ 'cart_abandon_time_duration' ]) && 'hours' === $saved_settings[ 'cart_abandon_time_duration' ]) ? 'selected' : ''; ?>>
                                <?php echo esc_html__('Hours', 'flexi-abandon-cart-recovery'); ?></option>

                        </select>
                        <p class="description">
                            <?php echo esc_html__('Consider cart abandoned after the above entered minutes of item being added to cart and order not placed.', 'flexi-abandon-cart-recovery'); ?>
                        </p>
                    </td>
                </tr>

                <!-- Resend Email After -->
                <tr>
                    <th scope="row" class="titledesc">
                        <label
                            for="resend_email_after"><?php echo esc_html__('Resend Email duration', 'flexi-abandon-cart-recovery'); ?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="resend_email_after" id="resend_email_after" type="number"
                            value="<?php echo isset($saved_settings[ 'resend_email_after' ]) ? esc_attr($saved_settings[ 'resend_email_after' ]) : ''; ?>">
                        &nbsp; &nbsp;
                        <select name="resend_email_after_duration" id="resend_email_after_duration">
                            <option value="seconds"
                                <?php echo (isset($saved_settings[ 'resend_email_after_duration' ]) && 'seconds' === $saved_settings[ 'resend_email_after_duration' ]) ? 'selected' : ''; ?>>
                                <?php echo esc_html__('Seconds', 'flexi-abandon-cart-recovery'); ?></option>

                            <option value="minutes"
                                <?php echo (isset($saved_settings[ 'resend_email_after_duration' ]) && 'minutes' === $saved_settings[ 'resend_email_after_duration' ]) ? 'selected' : ''; ?>>
                                <?php echo esc_html__('Minutes', 'flexi-abandon-cart-recovery'); ?></option>

                            <option value="hours"
                                <?php echo (!isset($saved_settings[ 'resend_email_after_duration' ]) || 'hours' === $saved_settings[ 'resend_email_after_duration' ]) ? 'selected' : ''; ?>>
                                <?php echo esc_html__('Hours', 'flexi-abandon-cart-recovery'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php echo esc_html__('Time duration between reminder emails.', 'flexi-abandon-cart-recovery'); ?>
                        </p>
                    </td>
                </tr>

                <!-- Cart Expiry duration -->
                <tr>
                    <th scope="row" class="titledesc">
                        <label
                            for="cart_expire_after"><?php echo esc_html__('Carts Expiry duration', 'flexi-abandon-cart-recovery'); ?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="cart_expire_after" id="cart_expire_after" type="number"
                            value="<?php echo isset($saved_settings[ 'cart_expire_after' ]) ? esc_attr($saved_settings[ 'cart_expire_after' ]) : ''; ?>">
                        &nbsp; &nbsp;
                        <select name="cart_expire_after_duration" id="cart_expire_after_duration">
                            <option value="hours"
                                <?php echo (isset($saved_settings[ 'cart_expire_after_duration' ]) && 'hours' === $saved_settings[ 'cart_expire_after_duration' ]) ? 'selected' : ''; ?>>
                                <?php echo esc_html__('Hours', 'flexi-abandon-cart-recovery'); ?></option>

                            <option value="days"
                                <?php echo (!isset($saved_settings[ 'cart_expire_after_duration' ]) || 'days' === $saved_settings[ 'cart_expire_after_duration' ]) ? 'selected' : ''; ?>>
                                <?php echo esc_html__('Days', 'flexi-abandon-cart-recovery'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php echo esc_html__('Cart will become automatically expired.', 'flexi-abandon-cart-recovery'); ?>
                        </p>
                    </td>
                </tr>

                <!-- Email from -->
                <tr>
                    <th scope="row" class="titledesc">
                        <label
                            for="email_from"><?php echo esc_html__('From Email : ', 'flexi-abandon-cart-recovery'); ?></label>
                    </th>
                    <td class="forminp forminp-checkbox">
                        <?php

$email_from = isset($saved_settings[ 'email_from' ]) ? esc_attr($saved_settings[ 'email_from' ]) : 'info@flexi.com';
?>
                        <input name="email_from" id="email_from" type="email"
                            value="<?php echo esc_html($email_from); ?>">
                    </td>
                </tr>

                <!-- From Name-->
                <tr>
                    <th scope="row" class="titledesc">
                        <label
                            for="email_name"><?php echo esc_html__('From Name : ', 'flexi-abandon-cart-recovery'); ?></label>
                    </th>
                    <td class="forminp forminp-checkbox">
                        <input name="email_name" id="email_name" type="text"
                            value="<?php echo isset($saved_settings[ 'email_name' ]) ? esc_attr($saved_settings[ 'email_name' ]) : esc_attr(get_bloginfo('name')); ?>">
                    </td>
                </tr>

                <!-- Language Switch -->
                <tr>
                    <th scope="row" class="titledesc">
                        <label
                            for="email_and_plugin_language"><?php echo esc_html__('Language', 'flexi-abandon-cart-recovery'); ?></label>
                    </th>
                    <td class="forminp forminp-checkbox">
                        <select name="email_and_plugin_language" id="email_and_plugin_language">
                            <option value="en_US"
                                <?php echo (isset($saved_settings[ 'email_and_plugin_language' ]) && 'en_US' === $saved_settings[ 'email_and_plugin_language' ]) ? 'selected' : ''; ?>>
                                <?php echo esc_html__('English', 'flexi-abandon-cart-recovery'); ?>
                            </option>

                            <option value="it_IT"
                                <?php echo (isset($saved_settings[ 'email_and_plugin_language' ]) && 'it_IT' === $saved_settings[ 'email_and_plugin_language' ]) ? 'selected' : ''; ?>>
                                <?php echo esc_html__('Italian', 'flexi-abandon-cart-recovery'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>

        <hr>

        <h2><?php echo esc_html__('Guest User Behavior', 'flexi-abandon-cart-recovery'); ?></h2>

        <table class="form-table configuration_settings">
            <tbody>
                <!-- Force Guest Login -->
                <tr>
                    <th scope="row" class="titledesc">
                        <?php echo esc_html__('Force Guest Users to Login?', 'flexi-abandon-cart-recovery'); ?>
                    </th>
                    <td class="forminp forminp-checkbox">
                        <label for="flexi_force_guest_login">
                            <?php
$flexi_force_guest_login = get_option('flexi_force_guest_login', 'off');
$checked                 = 'on' === $flexi_force_guest_login ? 'checked' : '';
?>
                            <input name="flexi_force_guest_login" id="flexi_force_guest_login" type="checkbox" value="on"
                                <?php echo esc_html($checked); ?>>
                            <?php echo esc_html__('Enable this to require guest users to login before viewing prices or making purchases. Disable to allow guest browsing and track guest cart abandonment.', 'flexi-abandon-cart-recovery'); ?>
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>

        <hr>

        <h2><?php echo esc_html__('GDPR Setting', 'flexi-abandon-cart-recovery'); ?></h2>

<table class="form-table configuration_settings">
    <tbody>
        <!-- Enable Setting on Shop page-->
        <tr>
            <th scope="row" class="titledesc">
                <?php echo esc_html__('Show On Shop page', 'flexi-abandon-cart-recovery'); ?>
            </th>
            <td class="forminp forminp-checkbox">
                <label for="gdpr_shop_page">
                    <?php
$gdpr_shop_page = isset($saved_settings[ 'gdpr_shop_page' ]) ? $saved_settings[ 'gdpr_shop_page' ] : '';
$checked        = 'on' === $gdpr_shop_page ? 'checked' : '';
?>
                            <input name="gdpr_shop_page" id="gdpr_shop_page" type="checkbox" <?php echo esc_html($checked); ?>>
                </label>
                    </td>
                </tr>
 <!-- Enable Setting on product page -->
                <tr>
            <th scope="row" class="titledesc">
                <?php echo esc_html__('Show On Product page', 'flexi-abandon-cart-recovery'); ?>
            </th>
            <td class="forminp forminp-checkbox">
                <label for="gdpr_product_page">
                    <?php
$gdpr_product_page = isset($saved_settings[ 'gdpr_product_page' ]) ? $saved_settings[ 'gdpr_product_page' ] : '';
$checked           = 'on' === $gdpr_product_page ? 'checked' : '';
?>
                            <input name="gdpr_product_page" id="gdpr_product_page" type="checkbox" <?php echo esc_html($checked); ?>>
                </label>
                    </td>
                </tr>
</tbody>
<tbody id="toggle_display_gdpr_message" style="display: none;">
                <!-- GDPR MESSAGE -->
                <tr>
                    <th scope="row" class="titledesc">
                        <label
                            for="gdpr_message"><?php echo esc_html__('GDPR Message ', 'flexi-abandon-cart-recovery'); ?></label>
                    </th>
                    <td class="forminp forminp-checkbox">
                       <?php
$initial_data = isset($saved_settings[ 'gdpr_message' ])
? wp_kses_post($saved_settings[ 'gdpr_message' ])
: "";

$editor_id = 'gdpr_message';
$settings  = array(
    'gdpr_message',
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

            </tbody>
        </table>

        <?php submit_button(esc_html__('Save Settings', 'flexi-abandon-cart-recovery'));?>
    </form>
</div>