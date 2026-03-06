<?php
/**
 * Integrations Settings UI for Flexi Abandoned Cart Recovery.
 *
 * @link  https://abandoned-cart-recovery
 * @since 1.0.0
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/modules
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $_GET['integrations-saved'] ) && 'true' === $_GET['integrations-saved'] ) {
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Integration settings saved.', 'flexi-abandon-cart-recovery' ) . '</p></div>';
}

// Handle save.
if ( isset( $_POST['save_integrations'] ) ) {
	if ( ! isset( $_POST['flexi_acr_integrations_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['flexi_acr_integrations_nonce'] ) ), 'flexi_acr_save_integrations' ) ) {
		wp_die( esc_html__( 'Nonce verification failed.', 'flexi-abandon-cart-recovery' ) );
	}

	if ( current_user_can( 'manage_options' ) ) {
		$raw = isset( $_POST['flexi_acr_integration'] ) ? (array) $_POST['flexi_acr_integration'] : array();

		// Mailchimp.
		if ( isset( $raw['mailchimp'] ) ) {
			$mc = $raw['mailchimp'];
			update_option( 'flexi_acr_integration_mailchimp', array(
				'enabled' => ! empty( $mc['enabled'] ) ? 1 : 0,
				'api_key' => isset( $mc['api_key'] ) ? sanitize_text_field( wp_unslash( $mc['api_key'] ) ) : '',
				'list_id' => isset( $mc['list_id'] ) ? sanitize_text_field( wp_unslash( $mc['list_id'] ) ) : '',
			) );
		}

		// Google Analytics.
		if ( isset( $raw['google_analytics'] ) ) {
			$ga = $raw['google_analytics'];
			update_option( 'flexi_acr_integration_google_analytics', array(
				'enabled'        => ! empty( $ga['enabled'] ) ? 1 : 0,
				'measurement_id' => isset( $ga['measurement_id'] ) ? sanitize_text_field( wp_unslash( $ga['measurement_id'] ) ) : '',
				'api_secret'     => isset( $ga['api_secret'] ) ? sanitize_text_field( wp_unslash( $ga['api_secret'] ) ) : '',
			) );
		}

		$redirect = add_query_arg(
			array( 'page' => 'flexi-cart-recovery-settings', 'section' => 'integrations', 'integrations-saved' => 'true' ),
			admin_url( 'admin.php' )
		);
		wp_safe_redirect( $redirect );
		exit;
	}
}
?>

<div class="wrap woocommerce">
	<h1><?php esc_html_e( 'Third-Party Integrations', 'flexi-abandon-cart-recovery' ); ?></h1>
	<p><?php esc_html_e( 'Connect the plugin with external services to sync data and track events automatically.', 'flexi-abandon-cart-recovery' ); ?></p>

	<form method="POST">
		<?php wp_nonce_field( 'flexi_acr_save_integrations', 'flexi_acr_integrations_nonce' ); ?>

		<!-- Mailchimp -->
		<div style="background:#fff; border:1px solid #ddd; border-radius:4px; padding:20px; margin-bottom:20px;">
			<h2 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:8px;">
				<?php esc_html_e( 'Mailchimp', 'flexi-abandon-cart-recovery' ); ?>
			</h2>
			<p><?php esc_html_e( 'Automatically sync abandoned-cart contacts to a Mailchimp audience and apply tags when carts are abandoned or recovered.', 'flexi-abandon-cart-recovery' ); ?></p>
			<?php
			if ( class_exists( 'Flexi_ACR_Integration_Mailchimp' ) ) {
				$mc_integration = new Flexi_ACR_Integration_Mailchimp();
				$mc_integration->render_settings();
			}
			?>
		</div>

		<!-- Google Analytics -->
		<div style="background:#fff; border:1px solid #ddd; border-radius:4px; padding:20px; margin-bottom:20px;">
			<h2 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:8px;">
				<?php esc_html_e( 'Google Analytics 4', 'flexi-abandon-cart-recovery' ); ?>
			</h2>
			<p><?php esc_html_e( 'Send cart abandonment, recovery, and email engagement events to GA4 via the Measurement Protocol.', 'flexi-abandon-cart-recovery' ); ?></p>
			<?php
			if ( class_exists( 'Flexi_ACR_Integration_Google_Analytics' ) ) {
				$ga_integration = new Flexi_ACR_Integration_Google_Analytics();
				$ga_integration->render_settings();
			}
			?>
		</div>

		<p>
			<button type="submit" name="save_integrations" class="button button-primary"><?php esc_html_e( 'Save Integration Settings', 'flexi-abandon-cart-recovery' ); ?></button>
		</p>
	</form>
</div>
