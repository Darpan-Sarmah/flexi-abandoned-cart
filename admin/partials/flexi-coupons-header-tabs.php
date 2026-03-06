<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://test
 * @since      1.0.0
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'create-coupon';

?>

<div class="success-admin-notices is-dismissible"></div>
<div class="navigation-wrapper wrap woocommerce">
	<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
		<ul class="navigation">
			<li>
				<a class="nav-tab <?php echo ( 'create-coupon' === $section || ! in_array( $section, array( 'create-coupon', 'coupons'), true ) ) ? 'nav-tab-active' : ''; ?>"
					href="<?php echo esc_url( admin_url( 'admin.php?page=flexi-abandon-cart-recovery-coupons&section=create-coupon' ) ); ?>">
					<?php esc_html_e( 'Create', 'flexi-abandon-cart-recovery' ); ?>
				</a>
			</li>
			<li>
				<a class="nav-tab <?php echo 'coupons' === $section ? 'nav-tab-active' : ''; ?>"
					href="<?php echo esc_url( admin_url( 'admin.php?page=flexi-abandon-cart-recovery-coupons&section=coupons' ) ); ?>">
					<?php esc_html_e( 'Coupons', 'flexi-abandon-cart-recovery' ); ?>
				</a>
			</li>
		</ul>
	</nav>
</div>
