<?php
/**
 * Handles plugin upgrades and database migrations.
 *
 * @link       https://example.com/flexi-abandoned-cart
 * @since      1.0.0
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/includes
 */

/**
 * Handles plugin upgrades and database migrations between versions.
 *
 * This class compares the stored plugin version in the database against the
 * current plugin version constant. When a version mismatch is detected it runs
 * any required migration routines and then updates the stored version.
 *
 * @since   1.0.0
 * @package Flexi_Abandon_Cart_Recovery
 */
class Flexi_Abandon_Cart_Recovery_Upgrader {

	/**
	 * Database option key used to store the installed plugin version.
	 *
	 * @since  1.0.0
	 * @var    string
	 */
	const VERSION_OPTION_KEY = 'flexi_abandon_cart_recovery_version';

	/**
	 * Run upgrade routines if the stored version differs from the current version.
	 *
	 * Should be called on the `plugins_loaded` hook (after WooCommerce is active).
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function run() {
		$installed_version = get_option( self::VERSION_OPTION_KEY, '0.0.0' );
		$current_version   = FLEXI_ABANDON_CART_RECOVERY_VERSION;

		if ( version_compare( $installed_version, $current_version, '==' ) ) {
			return;
		}

		self::upgrade( $installed_version, $current_version );

		update_option( self::VERSION_OPTION_KEY, $current_version );
	}

	/**
	 * Dispatch version-specific upgrade routines.
	 *
	 * Add a new conditional block here for each future release that requires a
	 * database migration or one-time setup step.
	 *
	 * @since  1.0.0
	 *
	 * @param string $from Installed version string (e.g. '1.0.0').
	 * @param string $to   Current version string  (e.g. '1.1.0').
	 * @return void
	 */
	private static function upgrade( $from, $to ) {
		// Example: migrations for a future 1.1.0 release would look like:
		//
		// if ( version_compare( $from, '1.1.0', '<' ) ) {
		//     self::upgrade_to_1_1_0();
		// }

		// Ensure the base database tables exist (idempotent).
		if ( version_compare( $from, '1.0.0', '<' ) ) {
			self::upgrade_to_1_0_0();
		}
	}

	/**
	 * Upgrade routine for version 1.0.0.
	 *
	 * Creates the initial database tables if they are missing.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private static function upgrade_to_1_0_0() {
		require_once FLEXI_ABANDON_CART_RECOVERY_DIR . 'includes/class-flexi-abandon-cart-recovery-activator.php';
		Flexi_Abandon_Cart_Recovery_Activator::activate();
	}
}
