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

$acr_tab    = isset($_GET[ 'tab' ]) ? sanitize_text_field(wp_unslash($_GET[ 'tab' ])) : 'mails';
$valid_tabs = array('mails', 'scheduler');

// Default to 'mails' if the tab is invalid.
if (!in_array($acr_tab, $valid_tabs, true)) {
    $acr_tab = 'mails'; // Ensure 'mails' is the default active tab.
}
?>

<div class="success-admin-notices is-dismissible"></div>
<div class="navigation-wrapper wrap woocommerce">
	<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
		<ul class="navigation">
			<li>
				<a class="nav-tab <?php echo 'mails' === $acr_tab ? 'nav-tab-active' : ''; ?>"
					href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=flexi-cart-recovery-settings&section=logs-view&tab=mails'), 'acr_logs_tab_nonce', '_acr_nonce')); ?>">
					<?php esc_html_e('Mails', 'flexi-abandon-cart-recovery');?>
				</a>
			</li>
			<li>
				<a class="nav-tab <?php echo 'scheduler' === $acr_tab ? 'nav-tab-active' : ''; ?>"
					href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=flexi-cart-recovery-settings&section=logs-view&tab=scheduler'), 'acr_logs_tab_nonce', '_acr_nonce')); ?>">
					<?php esc_html_e('Schedulers', 'flexi-abandon-cart-recovery');?>
				</a>
			</li>
		</ul>
	</nav>
</div>

<div class="tab-content">
	<?php
$nonce = isset($_GET[ '_acr_nonce' ]) ? sanitize_key($_GET[ '_acr_nonce' ]) : '';

if (wp_verify_nonce($nonce, 'acr_logs_tab_nonce')) {
    if ('mails' === $acr_tab) {
        include FLEXI_ABANDON_CART_RECOVERY_DIR . 'modules/sub-modules/class-flexi-cart-maillogs.php';
    } elseif ('scheduler' === $acr_tab) {
        include FLEXI_ABANDON_CART_RECOVERY_DIR . 'modules/sub-modules/flexi-cart-schedulers.php';
    }
} elseif ('mails' === $acr_tab) {
    include FLEXI_ABANDON_CART_RECOVERY_DIR . 'modules/sub-modules/class-flexi-cart-maillogs.php';
}
?>
</div>
