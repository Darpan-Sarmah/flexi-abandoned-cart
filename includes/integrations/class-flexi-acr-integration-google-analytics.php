<?php
/**
 * Google Analytics / GA4 Integration for Flexi Abandoned Cart Recovery.
 *
 * Pushes cart abandonment and recovery events to Google Analytics 4
 * via the Measurement Protocol.
 *
 * @link  https://abandoned-cart-recovery
 * @since 1.0.0
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/includes/integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( __FILE__ ) . '/class-flexi-acr-integration-base.php';

/**
 * Flexi_ACR_Integration_Google_Analytics
 *
 * @since 1.0.0
 */
class Flexi_ACR_Integration_Google_Analytics extends Flexi_ACR_Integration_Base {

	protected $id          = 'google_analytics';
	protected $name        = 'Google Analytics (GA4)';
	protected $description = 'Send cart abandonment and recovery events to Google Analytics 4 via Measurement Protocol.';
	protected $option_key  = 'flexi_acr_integration_google_analytics';

	/** GA4 Measurement Protocol endpoint. */
	const MP_ENDPOINT = 'https://www.google-analytics.com/mp/collect';

	/**
	 * Initialize hooks.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		if ( ! $this->is_enabled() ) {
			return;
		}
		add_action( 'flexi_acr_cart_abandoned',  array( $this, 'track_abandonment' ) );
		add_action( 'flexi_acr_cart_recovered',  array( $this, 'track_recovery' ) );
		add_action( 'flexi_acr_email_opened',    array( $this, 'track_email_open' ) );
		add_action( 'flexi_acr_email_clicked',   array( $this, 'track_email_click' ) );
	}

	/**
	 * Track cart abandonment event.
	 *
	 * @param array $data Cart data.
	 */
	public function track_abandonment( $data ) {
		$this->send_event(
			'flexi_cart_abandoned',
			array(
				'currency' => get_woocommerce_currency(),
				'value'    => isset( $data['cart_total'] ) ? floatval( $data['cart_total'] ) : 0,
			)
		);
	}

	/**
	 * Track cart recovery event.
	 *
	 * @param array $data Recovery data.
	 */
	public function track_recovery( $data ) {
		$this->send_event(
			'flexi_cart_recovered',
			array(
				'currency'       => get_woocommerce_currency(),
				'value'          => isset( $data['order_total'] ) ? floatval( $data['order_total'] ) : 0,
				'transaction_id' => isset( $data['order_id'] ) ? $data['order_id'] : '',
			)
		);
	}

	/**
	 * Track email open event.
	 *
	 * @param array $data Tracking data.
	 */
	public function track_email_open( $data ) {
		$this->send_event( 'flexi_email_opened', array() );
	}

	/**
	 * Track email link click event.
	 *
	 * @param array $data Tracking data.
	 */
	public function track_email_click( $data ) {
		$this->send_event( 'flexi_email_clicked', array() );
	}

	/**
	 * Send a GA4 Measurement Protocol event.
	 *
	 * @param string $event_name Event name.
	 * @param array  $params     Event parameters.
	 */
	private function send_event( $event_name, $params ) {
		$settings     = $this->get_settings();
		$measurement_id = isset( $settings['measurement_id'] ) ? $settings['measurement_id'] : '';
		$api_secret     = isset( $settings['api_secret'] ) ? $settings['api_secret'] : '';

		if ( empty( $measurement_id ) || empty( $api_secret ) ) {
			return;
		}

		$url  = self::MP_ENDPOINT . '?measurement_id=' . rawurlencode( $measurement_id ) . '&api_secret=' . rawurlencode( $api_secret );
		$body = wp_json_encode(
			array(
				'client_id' => $this->get_client_id(),
				'events'    => array(
					array(
						'name'   => $event_name,
						'params' => $params,
					),
				),
			)
		);

		wp_remote_post(
			$url,
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => $body,
				'timeout' => 5,
				'blocking'=> false,
			)
		);
	}

	/**
	 * Get or generate a GA4 client ID.
	 *
	 * @return string
	 */
	private function get_client_id() {
		$client_id = isset( $_COOKIE['_ga'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['_ga'] ) ) : '';
		if ( $client_id ) {
			// Extract the client ID portion from the _ga cookie (GA.x.xxxxxxxxxx.xxxxxxxxxx).
			$parts = explode( '.', $client_id );
			if ( count( $parts ) >= 4 ) {
				return $parts[2] . '.' . $parts[3];
			}
		}
		// Fallback: generate a pseudo client ID.
		return wp_generate_uuid4();
	}

	/**
	 * Render settings form fields.
	 *
	 * @since 1.0.0
	 */
	public function render_settings() {
		$settings       = $this->get_settings();
		$enabled        = ! empty( $settings['enabled'] );
		$measurement_id = isset( $settings['measurement_id'] ) ? esc_attr( $settings['measurement_id'] ) : '';
		$api_secret     = isset( $settings['api_secret'] ) ? esc_attr( $settings['api_secret'] ) : '';
		?>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Enable Google Analytics Integration', 'flexi-abandon-cart-recovery' ); ?></th>
				<td>
					<input type="checkbox" name="flexi_acr_integration[google_analytics][enabled]" value="1" <?php checked( $enabled ); ?>>
				</td>
			</tr>
			<tr>
				<th><label for="ga_measurement_id"><?php esc_html_e( 'Measurement ID', 'flexi-abandon-cart-recovery' ); ?></label></th>
				<td>
					<input type="text" id="ga_measurement_id" name="flexi_acr_integration[google_analytics][measurement_id]"
						value="<?php echo $measurement_id; ?>" class="regular-text" placeholder="G-XXXXXXXXXX">
				</td>
			</tr>
			<tr>
				<th><label for="ga_api_secret"><?php esc_html_e( 'API Secret', 'flexi-abandon-cart-recovery' ); ?></label></th>
				<td>
					<input type="password" id="ga_api_secret" name="flexi_acr_integration[google_analytics][api_secret]"
						value="<?php echo $api_secret; ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Create in Google Analytics → Admin → Data Streams → Measurement Protocol.', 'flexi-abandon-cart-recovery' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}
}
