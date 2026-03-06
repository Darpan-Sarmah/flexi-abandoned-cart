<?php
/**
 * Webhook Support for Flexi Abandoned Cart Recovery.
 *
 * Fires outbound HTTP POST webhooks for key plugin events so that
 * external tools (Zapier, Make, custom servers, etc.) can react in
 * real-time to cart abandonment, email opens/clicks, and recoveries.
 *
 * @link  https://abandoned-cart-recovery
 * @since 1.0.0
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flexi_Webhooks
 *
 * @since 1.0.0
 */
class Flexi_Webhooks {

	/**
	 * Option key where webhook configurations are stored.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'flexi_acr_webhooks';

	/**
	 * Supported webhook event slugs.
	 *
	 * @var array
	 */
	const EVENTS = array(
		'cart_abandoned'   => 'Cart Abandoned',
		'email_sent'       => 'Email Sent',
		'email_opened'     => 'Email Opened',
		'email_clicked'    => 'Email Link Clicked',
		'cart_recovered'   => 'Cart Recovered (Purchase Completed)',
		'coupon_applied'   => 'Coupon Applied',
		'user_unsubscribed'=> 'User Unsubscribed',
	);

	/**
	 * Constructor – hooks into plugin actions.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Core plugin events.
		add_action( 'flexi_acr_cart_abandoned',    array( $this, 'on_cart_abandoned' ) );
		add_action( 'flexi_acr_email_sent',        array( $this, 'on_email_sent' ) );
		add_action( 'flexi_acr_email_opened',      array( $this, 'on_email_opened' ) );
		add_action( 'flexi_acr_email_clicked',     array( $this, 'on_email_clicked' ) );
		add_action( 'flexi_acr_cart_recovered',    array( $this, 'on_cart_recovered' ) );
		add_action( 'flexi_acr_coupon_applied',    array( $this, 'on_coupon_applied' ) );
		add_action( 'flexi_acr_user_unsubscribed', array( $this, 'on_user_unsubscribed' ) );

		// Admin form handler for saving webhook configurations.
		add_action( 'admin_post_flexi_acr_save_webhooks', array( $this, 'save_webhooks' ) );
	}

	// ── Event handlers ─────────────────────────────────────────────────────────

	/**
	 * Triggered when a cart is marked as abandoned.
	 *
	 * @param array $data Cart data.
	 */
	public function on_cart_abandoned( $data ) {
		$this->dispatch( 'cart_abandoned', $data );
	}

	/**
	 * Triggered after an email is sent.
	 *
	 * @param array $data Email data (to, subject, template, etc.).
	 */
	public function on_email_sent( $data ) {
		$this->dispatch( 'email_sent', $data );
	}

	/**
	 * Triggered when an email open is tracked.
	 *
	 * @param array $data Tracking data.
	 */
	public function on_email_opened( $data ) {
		$this->dispatch( 'email_opened', $data );
	}

	/**
	 * Triggered when a recovery link is clicked.
	 *
	 * @param array $data Click data.
	 */
	public function on_email_clicked( $data ) {
		$this->dispatch( 'email_clicked', $data );
	}

	/**
	 * Triggered when a cart is recovered (purchase completed).
	 *
	 * @param array $data Order/cart data.
	 */
	public function on_cart_recovered( $data ) {
		$this->dispatch( 'cart_recovered', $data );
	}

	/**
	 * Triggered when a coupon is applied via the plugin.
	 *
	 * @param array $data Coupon data.
	 */
	public function on_coupon_applied( $data ) {
		$this->dispatch( 'coupon_applied', $data );
	}

	/**
	 * Triggered when a user unsubscribes.
	 *
	 * @param string $email Email address.
	 */
	public function on_user_unsubscribed( $email ) {
		$this->dispatch( 'user_unsubscribed', array( 'email' => $email ) );
	}

	// ── Core dispatch ─────────────────────────────────────────────────────────

	/**
	 * Dispatch a webhook for a given event to all configured endpoints.
	 *
	 * @since 1.0.0
	 *
	 * @param string $event Event slug (see self::EVENTS).
	 * @param array  $data  Payload to send.
	 */
	public function dispatch( $event, $data ) {
		$webhooks = $this->get_webhooks();

		foreach ( $webhooks as $webhook ) {
			if ( empty( $webhook['url'] ) || empty( $webhook['active'] ) ) {
				continue;
			}

			// Only fire if this webhook subscribes to the event.
			$subscribed_events = isset( $webhook['events'] ) ? (array) $webhook['events'] : array_keys( self::EVENTS );
			if ( ! in_array( $event, $subscribed_events, true ) ) {
				continue;
			}

			$this->send( $webhook, $event, $data );
		}
	}

	/**
	 * Send a single HTTP POST to a webhook endpoint.
	 *
	 * Failures are logged via the plugin's error log mechanism but do not
	 * interrupt normal plugin execution.
	 *
	 * @param array  $webhook Webhook config row.
	 * @param string $event   Event slug.
	 * @param array  $data    Payload.
	 */
	private function send( $webhook, $event, $data ) {
		$url     = esc_url_raw( $webhook['url'] );
		$secret  = isset( $webhook['secret'] ) ? $webhook['secret'] : '';
		$payload = array(
			'event'     => $event,
			'timestamp' => gmdate( 'c' ),
			'site_url'  => get_site_url(),
			'data'      => $data,
		);

		$body = wp_json_encode( $payload );

		$headers = array(
			'Content-Type' => 'application/json',
			'X-Flexi-ACR-Event' => $event,
		);

		if ( ! empty( $secret ) ) {
			$headers['X-Flexi-ACR-Signature'] = 'sha256=' . hash_hmac( 'sha256', $body, $secret );
		}

		$response = wp_remote_post(
			$url,
			array(
				'headers'   => $headers,
				'body'      => $body,
				'timeout'   => 10,
				'blocking'  => false, // Fire-and-forget to not slow the request.
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'Flexi ACR Webhook Error [' . $event . ']: ' . $response->get_error_message() );
		}

		do_action( 'flexi_acr_webhook_dispatched', $event, $url, $payload, $response );
	}

	// ── Configuration helpers ─────────────────────────────────────────────────

	/**
	 * Get all webhook configurations.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_webhooks() {
		return (array) get_option( self::OPTION_KEY, array() );
	}

	/**
	 * Save webhook configurations submitted from the admin form.
	 *
	 * @since 1.0.0
	 */
	public function save_webhooks() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'flexi-abandon-cart-recovery' ) );
		}

		if ( ! isset( $_POST['flexi_acr_webhooks_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['flexi_acr_webhooks_nonce'] ) ), 'flexi_acr_save_webhooks' ) ) {
			wp_die( esc_html__( 'Nonce verification failed.', 'flexi-abandon-cart-recovery' ) );
		}

		$raw_webhooks = isset( $_POST['flexi_acr_webhook'] ) ? (array) $_POST['flexi_acr_webhook'] : array();
		$clean        = array();

		foreach ( $raw_webhooks as $wh ) {
			$url = isset( $wh['url'] ) ? esc_url_raw( wp_unslash( $wh['url'] ) ) : '';
			if ( empty( $url ) ) {
				continue;
			}
			$events = isset( $wh['events'] ) ? array_map( 'sanitize_text_field', (array) $wh['events'] ) : array();
			// Validate event slugs.
			$events = array_intersect( $events, array_keys( self::EVENTS ) );

			$clean[] = array(
				'url'    => $url,
				'secret' => isset( $wh['secret'] ) ? sanitize_text_field( wp_unslash( $wh['secret'] ) ) : '',
				'events' => array_values( $events ),
				'active' => ! empty( $wh['active'] ) ? 1 : 0,
				'label'  => isset( $wh['label'] ) ? sanitize_text_field( wp_unslash( $wh['label'] ) ) : '',
			);
		}

		update_option( self::OPTION_KEY, $clean );

		$redirect = add_query_arg(
			array(
				'page'          => 'flexi-cart-recovery-settings',
				'section'       => 'webhooks',
				'webhook-saved' => 'true',
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Generate an unsubscribe token for a given email address.
	 *
	 * Can be used in email templates via the {{unsubscribe_url}} shortcode.
	 *
	 * Note: Tokens are derived from wp_salt('auth'). If the site's WordPress
	 * secret keys are rotated (e.g., via wp-cli or manual wp-config.php change),
	 * previously issued unsubscribe links will become invalid. Users who click
	 * a stale link will receive an error and can request a new email to get a
	 * fresh link. Storing tokens in the database would eliminate this limitation
	 * but adds DB overhead for a relatively rare operation.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email Email address.
	 * @return string Signed unsubscribe URL.
	 */
	public static function get_unsubscribe_url( $email ) {
		$token = hash_hmac( 'sha256', $email, wp_salt( 'auth' ) );
		return rest_url( 'flexi-acr/v1/unsubscribe' ) . '?email=' . rawurlencode( $email ) . '&token=' . $token;
	}

	/**
	 * Check whether an email is in the unsubscribe list.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email Email address.
	 * @return bool
	 */
	public static function is_unsubscribed( $email ) {
		$list = (array) get_option( 'flexi_acr_unsubscribe_list', array() );
		return in_array( sanitize_email( $email ), $list, true );
	}
}
