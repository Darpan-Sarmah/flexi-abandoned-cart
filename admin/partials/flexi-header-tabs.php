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

$section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'settings-view';

?>

<div class="success-admin-notices is-dismissible"></div>
<div class="navigation-wrapper wrap woocommerce">
	<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
		<ul class="navigation">
			<li>
				<a class="nav-tab <?php echo ( 'settings-view' === $section || ! in_array( $section, array( 'carts-list', 'email-settings', 'logs-view', 'language-setting', 'logs-view-scheduler', 'admin-setting' ), true ) ) ? 'nav-tab-active' : ''; ?>"
					href="<?php echo esc_url( admin_url( 'admin.php?page=flexi-cart-recovery-settings&section=settings-view' ) ); ?>">
					<?php esc_html_e( 'Global Settings', 'flexi-abandon-cart-recovery' ); ?>
				</a>
			</li>
			<li>
				<a class="nav-tab <?php echo 'carts-list' === $section ? 'nav-tab-active' : ''; ?>"
					href="<?php echo esc_url( admin_url( 'admin.php?page=flexi-cart-recovery-settings&section=carts-list' ) ); ?>">
					<?php esc_html_e( 'Carts Details', 'flexi-abandon-cart-recovery' ); ?>
				</a>
			</li>
			<li>
				<a class="nav-tab <?php echo 'email-settings' === $section ? 'nav-tab-active' : ''; ?>"
					href="<?php echo esc_url( admin_url( 'admin.php?page=flexi-cart-recovery-settings&section=email-settings' ) ); ?>">
					<?php esc_html_e( 'Email Templates', 'flexi-abandon-cart-recovery' ); ?>
				</a>
			</li>
			<li>
				<a class="nav-tab <?php echo 'logs-view' === $section ? 'nav-tab-active' : ''; ?>"
					href="<?php echo esc_url( admin_url( 'admin.php?page=flexi-cart-recovery-settings&section=logs-view' ) ); ?>">
					<?php esc_html_e( 'Logs', 'flexi-abandon-cart-recovery' ); ?>
				</a>
			</li>
			<li>
				<a class="nav-tab <?php echo 'admin-setting' === $section ? 'nav-tab-active' : ''; ?>"
					href="<?php echo esc_url( admin_url( 'admin.php?page=flexi-cart-recovery-settings&section=admin-setting' ) ); ?>">
					<?php esc_html_e( 'Admin Settings', 'flexi-abandon-cart-recovery' ); ?>
				</a>
			</li>
		</ul>
	</nav>
</div>
