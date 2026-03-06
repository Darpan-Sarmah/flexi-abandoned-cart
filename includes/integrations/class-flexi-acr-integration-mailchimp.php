<?php
/**
 * Mailchimp Integration for Flexi Abandoned Cart Recovery.
 *
 * Syncs abandoned-cart user data to a Mailchimp audience list and
 * optionally adds/removes tags on cart events.
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
 * Flexi_ACR_Integration_Mailchimp
 *
 * @since 1.0.0
 */
class Flexi_ACR_Integration_Mailchimp extends Flexi_ACR_Integration_Base {

	protected $id          = 'mailchimp';
	protected $name        = 'Mailchimp';
	protected $description = 'Sync abandoned-cart contacts to a Mailchimp audience and tag them automatically.';
	protected $option_key  = 'flexi_acr_integration_mailchimp';

	/**
	 * Initialize hooks.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		if ( ! $this->is_enabled() ) {
			return;
		}
		add_action( 'flexi_acr_cart_abandoned', array( $this, 'sync_on_abandonment' ) );
		add_action( 'flexi_acr_cart_recovered',  array( $this, 'tag_recovered' ) );
	}

	/**
	 * Sync user when a cart is abandoned.
	 *
	 * @param array $data Cart data including user info.
	 */
	public function sync_on_abandonment( $data ) {
		$email = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
		if ( empty( $email ) ) {
			return;
		}
		$settings = $this->get_settings();
		$list_id  = isset( $settings['list_id'] ) ? $settings['list_id'] : '';
		$api_key  = isset( $settings['api_key'] ) ? $settings['api_key'] : '';

		if ( empty( $list_id ) || empty( $api_key ) ) {
			return;
		}

		$this->upsert_member(
			$api_key,
			$list_id,
			$email,
			array(
				'FNAME' => isset( $data['first_name'] ) ? $data['first_name'] : '',
				'LNAME' => isset( $data['last_name'] ) ? $data['last_name'] : '',
			),
			array( 'abandoned-cart' )
		);
	}

	/**
	 * Tag user as recovered when they complete a purchase.
	 *
	 * @param array $data Recovery data.
	 */
	public function tag_recovered( $data ) {
		$email = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
		if ( empty( $email ) ) {
			return;
		}
		$settings = $this->get_settings();
		$list_id  = isset( $settings['list_id'] ) ? $settings['list_id'] : '';
		$api_key  = isset( $settings['api_key'] ) ? $settings['api_key'] : '';

		if ( empty( $list_id ) || empty( $api_key ) ) {
			return;
		}

		$this->upsert_member( $api_key, $list_id, $email, array(), array( 'recovered' ) );
	}

	/**
	 * Upsert a Mailchimp list member and apply tags.
	 *
	 * @param string $api_key  Mailchimp API key.
	 * @param string $list_id  Audience list ID.
	 * @param string $email    Member email.
	 * @param array  $merge    Merge fields.
	 * @param array  $tags     Tags to apply.
	 */
	private function upsert_member( $api_key, $list_id, $email, $merge = array(), $tags = array() ) {
		$dc       = substr( $api_key, strpos( $api_key, '-' ) + 1 );
		$hash     = md5( strtolower( $email ) );
		$endpoint = "https://{$dc}.api.mailchimp.com/3.0/lists/{$list_id}/members/{$hash}";

		$body = array(
			'email_address' => $email,
			'status_if_new' => 'subscribed',
			'merge_fields'  => $merge,
		);

		if ( ! empty( $tags ) ) {
			$body['tags'] = $tags;
		}

		$response = wp_remote_request(
			$endpoint,
			array(
				'method'  => 'PUT',
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( 'anystring:' . $api_key ),
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'Flexi ACR Mailchimp Error: ' . $response->get_error_message() );
		}
	}

	/**
	 * Render settings form fields.
	 *
	 * @since 1.0.0
	 */
	public function render_settings() {
		$settings = $this->get_settings();
		$enabled  = ! empty( $settings['enabled'] );
		$api_key  = isset( $settings['api_key'] ) ? esc_attr( $settings['api_key'] ) : '';
		$list_id  = isset( $settings['list_id'] ) ? esc_attr( $settings['list_id'] ) : '';
		?>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Enable Mailchimp Integration', 'flexi-abandon-cart-recovery' ); ?></th>
				<td>
					<input type="checkbox" name="flexi_acr_integration[mailchimp][enabled]" value="1" <?php checked( $enabled ); ?>>
				</td>
			</tr>
			<tr>
				<th><label for="mailchimp_api_key"><?php esc_html_e( 'API Key', 'flexi-abandon-cart-recovery' ); ?></label></th>
				<td>
					<input type="text" id="mailchimp_api_key" name="flexi_acr_integration[mailchimp][api_key]"
						value="<?php echo $api_key; ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Found in Mailchimp Account → Extras → API keys.', 'flexi-abandon-cart-recovery' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="mailchimp_list_id"><?php esc_html_e( 'Audience List ID', 'flexi-abandon-cart-recovery' ); ?></label></th>
				<td>
					<input type="text" id="mailchimp_list_id" name="flexi_acr_integration[mailchimp][list_id]"
						value="<?php echo $list_id; ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Found in Mailchimp Audience → Settings → Audience name and defaults.', 'flexi-abandon-cart-recovery' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}
}
