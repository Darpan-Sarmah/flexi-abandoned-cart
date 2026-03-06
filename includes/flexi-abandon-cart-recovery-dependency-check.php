<?php
/**
 * Dependency Check Functions
 *
 * @package Flexi_Abandon_Cart_Recovery
 * @version 1.0.0
 * @link    https://test
 */

if (!defined('ABSPATH')) {
    die;
}

/**
 * Checks if WooCommerce is active.
 *
 * @return bool True if WooCommerce is active, false otherwise.
 */
function flexi_abandon_cart_reco_check_woocommerce_active()
{
    if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')), true)) {
        return true;
    }
    return false;
}

/**
 * Deactivates the plugin if WooCommerce is not installed or activated.
 *
 * Displays a notice and deactivates the plugin.
 *
 * @return void
 */
function deactivate_flexi_abandon_cart_reco_woo_missing()
{
    if (!class_exists('WooCommerce')) {
        if (isset($_GET[ 'activate' ]) && check_admin_referer('flexi_abandon_cart_reco_woo_missing_action', 'flexi_abandon_cart_reco_woo_missing_nonce')) {
            add_action('admin_notices', 'flexi_abandon_cart_reco_woo_missing_notice');
            unset($_GET[ 'activate' ]);
        }
    }
}

/**
 * Displays a notice when WooCommerce is missing.
 *
 * @return void
 */
function flexi_abandon_cart_reco_woo_missing_notice()
{
    deactivate_plugins(FLEXI_ABANDON_CART_RECOVERY_BASENAME);

    echo ('<div class="notice notice-error is-dismissible"><p>' . sprintf(
        esc_html__('Abandoned Cart Recovery requires WooCommerce to be installed and active. You can download %s from here.', 'flexi-abandon-cart-recovery'),
        '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>'
    ) . '</p></div>');
}
