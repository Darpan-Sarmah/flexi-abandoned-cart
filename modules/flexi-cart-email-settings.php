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

$acr_tab    = isset($_GET[ 'tab' ]) ? sanitize_text_field(wp_unslash($_GET[ 'tab' ])) : 'email-templates';
$valid_tabs = array('email-templates', 'rule-set', 'email-trigger');

if (!in_array($acr_tab, $valid_tabs, true)) {
    $acr_tab = 'email-templates'; // Ensure 'email-templates' is the default active tab.
}
?>

<div class="success-admin-notices is-dismissible"></div>
<div class="navigation-wrapper wrap woocommerce">
    <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
        <ul class="navigation">
            <li>
                <a class="nav-tab <?php echo 'email-templates' === $acr_tab ? 'nav-tab-active' : ''; ?>"
                    href="<?php echo esc_url(admin_url('admin.php?page=flexi-cart-recovery-settings&section=email-settings&tab=email-templates')); ?>">
                    <?php esc_html_e('Email Templates', 'flexi-abandon-cart-recovery');?>
                </a>
            </li>

            <li>
                <a class="nav-tab <?php echo 'rule-set' === $acr_tab ? 'nav-tab-active' : ''; ?>"
                    href="<?php echo esc_url(admin_url('admin.php?page=flexi-cart-recovery-settings&section=email-settings&tab=rule-set')); ?>">
                    <?php esc_html_e('Rules Set', 'flexi-abandon-cart-recovery');?>
                </a>
            </li>

            <li>
                <a class="nav-tab <?php echo 'email-trigger' === $acr_tab ? 'nav-tab-active' : ''; ?>"
                    href="<?php echo esc_url(admin_url('admin.php?page=flexi-cart-recovery-settings&section=email-settings&tab=email-trigger')); ?>">
                    <?php esc_html_e('Email Triggers', 'flexi-abandon-cart-recovery');?>
                </a>
            </li>
        </ul>
    </nav>
</div>

<div class="tab-content">
    <?php
if ('email-templates' === $acr_tab) {
    include FLEXI_ABANDON_CART_RECOVERY_DIR . 'modules/sub-modules/flexi-cart-email-template.php';
} elseif ('rule-set' === $acr_tab) {
    include FLEXI_ABANDON_CART_RECOVERY_DIR . 'modules/sub-modules/flexi-cart-rule-sets.php';
} elseif ('email-trigger' === $acr_tab) {
    include FLEXI_ABANDON_CART_RECOVERY_DIR . 'modules/sub-modules/flexi-cart-email-triggers.php';
} else {
    include FLEXI_ABANDON_CART_RECOVERY_DIR . 'modules/sub-modules/flexi-cart-email-template.php';
}
?>
</div>