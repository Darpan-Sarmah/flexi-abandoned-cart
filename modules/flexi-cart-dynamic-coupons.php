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
 * @subpackage Flexi_Abandon_Cart_Recovery/modules
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

$acr_tab    = isset($_GET[ 'tabs' ]) ? sanitize_text_field(wp_unslash($_GET[ 'tabs' ])) : 'view-coupon';
$valid_tabs = array('view-coupon', 'expired-coupon');

if (!in_array($acr_tab, $valid_tabs, true)) {
    $acr_tab = 'view-coupon'; 
}
?>

<div class="success-admin-notices is-dismissible"></div>
<div class="navigation-wrapper wrap woocommerce">
	<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
		<ul class="navigation">
			<li>
				<a class="nav-tab <?php echo 'view-coupon' === $acr_tab ? 'nav-tab-active' : ''; ?>"
					href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=flexi-abandon-cart-recovery-coupons&section=coupons&tabs=view-coupon'), 'acr_logs_tab_nonce', '_acr_nonce')); ?>">
					<?php esc_html_e('Coupons List', 'flexi-abandon-cart-recovery');?>
				</a>
			</li>
            <li>
				<a class="nav-tab <?php echo 'expired-coupon' === $acr_tab ? 'nav-tab-active' : ''; ?>"
					href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=flexi-abandon-cart-recovery-coupons&section=coupons&tabs=expired-coupon'), 'acr_logs_tab_nonce', '_acr_nonce')); ?>">
					<?php esc_html_e('Expired Coupons', 'flexi-abandon-cart-recovery');?>
				</a>
			</li>
		</ul>
	</nav>
</div>

<div class="tab-content">
	<?php
$nonce = isset($_GET[ '_acr_nonce' ]) ? sanitize_key($_GET[ '_acr_nonce' ]) : '';

if (wp_verify_nonce($nonce, 'acr_logs_tab_nonce')) {
	if ('view-coupon' === $acr_tab) {
        include FLEXI_ABANDON_CART_RECOVERY_DIR . 'modules/sub-modules/class-flexi-cart-dynamic-coupons.php';
    }
    elseif("expired-coupon" === $acr_tab){
        include FLEXI_ABANDON_CART_RECOVERY_DIR . 'modules/sub-modules/class-flexi-cart-expired-coupons.php';
    }
} elseif ('view-coupon' === $acr_tab) {
	include FLEXI_ABANDON_CART_RECOVERY_DIR . 'modules/sub-modules/class-flexi-cart-dynamic-coupons.php';
}
?>
</div>
