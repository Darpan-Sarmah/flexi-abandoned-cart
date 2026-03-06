<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://test
 * @since             1.0.0
 * @package           Flexi_Abandon_Cart_Recovery
 *
 * @wordpress-plugin
 * Plugin Name:       Flexi Abandon Cart Recovery
 * Plugin URI:        https://flexi-abandon-cart-recovery
 * Description:       Recover your lost revenue. Capture email address of users on the checkout page and send follow up emails if they don't complete the purchase.
 * Version:           1.0.0
 * Author:            Start and Grow
 * Author URI:        https://test/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       flexi-abandon-cart-recovery
 * Domain Path:       /languages
 * Requires Plugins: woocommerce
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'FLEXI_ABANDON_CART_RECOVERY_VERSION', '1.0.0' );
define( 'FLEXI_ABANDON_CART_RECOVERY_BASENAME', plugin_basename( __FILE__ ) );
define( 'FLEXI_ABANDON_CART_RECOVERY_PREFIX', 'flexi_abandon_cart_recovery' );
define( 'FLEXI_ABANDON_CART_RECOVERY_FILE', __FILE__ );
define( 'FLEXI_ABANDON_CART_RECOVERY_DIR', plugin_dir_path( FLEXI_ABANDON_CART_RECOVERY_FILE ) );
define( 'FLEXI_ABANDON_CART_RECOVERY_URL', plugin_dir_url( FLEXI_ABANDON_CART_RECOVERY_FILE ) );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-flexi-abandon-cart-recovery-activator.php
 */
function activate_flexi_abandon_cart_recovery() {
	require_once FLEXI_ABANDON_CART_RECOVERY_DIR . 'includes/class-flexi-abandon-cart-recovery-activator.php';
	Flexi_Abandon_Cart_Recovery_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-flexi-abandon-cart-recovery-deactivator.php
 */
function deactivate_flexi_abandon_cart_recovery() {
	require_once FLEXI_ABANDON_CART_RECOVERY_DIR . 'includes/class-flexi-abandon-cart-recovery-deactivator.php';
	Flexi_Abandon_Cart_Recovery_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_flexi_abandon_cart_recovery' );
register_deactivation_hook( __FILE__, 'deactivate_flexi_abandon_cart_recovery' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require FLEXI_ABANDON_CART_RECOVERY_DIR . 'includes/class-flexi-abandon-cart-recovery.php';
require FLEXI_ABANDON_CART_RECOVERY_DIR . 'includes/flexi-abandon-cart-recovery-dependency-check.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_flexi_abandon_cart_recovery() {

	$plugin = new Flexi_Abandon_Cart_Recovery();
	$plugin->run();

}

/**
 * Activation hook function for the Flexi Abandoned Cart Recovery plugin.
 */
function flexi_abandon_cart_reco_activation_hook() {
	set_transient( 'flexi-abandon-cart-active-notice', true, 5 );
}

/**
 * Displays an admin notice upon plugin activation.
 */
function flexi_abandon_cart_reco_admin_notice_activation() {
	if ( get_transient( 'flexi-abandon-cart-active-notice' ) ) {
		?>
		<div class="updated notice is-dismissible">
			<p>
			<?php echo esc_html__( "WELCOME !!! Recover your lost revenue. Capture email address of users on the checkout page and send follow up emails if they don't complete the purchase.", 'flexi-abandon-cart-recovery' ); ?>
			</p>
		</div>
		<?php
		delete_transient( 'flexi-abandon-cart-active-notice' );
	}
}

if ( flexi_abandon_cart_reco_check_woocommerce_active() ) {

	run_flexi_abandon_cart_recovery();
	register_activation_hook( __FILE__, 'flexi_abandon_cart_reco_activation_hook' );
	add_action( 'admin_notices', 'flexi_abandon_cart_reco_admin_notice_activation' );
} else {
	add_action( 'admin_init', 'deactivate_flexi_abandon_cart_reco_woo_missing' );
}
