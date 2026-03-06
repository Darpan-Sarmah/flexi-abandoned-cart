<?php
/**
 * Webhook Configuration UI for Flexi Abandoned Cart Recovery.
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

if ( isset( $_GET['webhook-saved'] ) && 'true' === $_GET['webhook-saved'] ) {
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Webhooks saved successfully.', 'flexi-abandon-cart-recovery' ) . '</p></div>';
}

$webhooks = class_exists( 'Flexi_Webhooks' ) ? ( new Flexi_Webhooks() )->get_webhooks() : array();
$events   = Flexi_Webhooks::EVENTS;

// Ensure at least one empty row for the form.
if ( empty( $webhooks ) ) {
	$webhooks = array(
		array( 'url' => '', 'secret' => '', 'events' => array(), 'active' => 1, 'label' => '' ),
	);
}
?>

<div class="wrap woocommerce">
	<h1><?php esc_html_e( 'Webhook Configuration', 'flexi-abandon-cart-recovery' ); ?></h1>
	<p><?php esc_html_e( 'Configure outbound webhooks to notify external services when plugin events occur. Each webhook receives a JSON POST with event data. Use the secret to verify the X-Flexi-ACR-Signature header.', 'flexi-abandon-cart-recovery' ); ?></p>

	<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="flexi_acr_save_webhooks">
		<?php wp_nonce_field( 'flexi_acr_save_webhooks', 'flexi_acr_webhooks_nonce' ); ?>

		<div id="flexi-webhooks-container">
			<?php foreach ( $webhooks as $index => $webhook ) :
				$active_checked = ! empty( $webhook['active'] ) ? 'checked' : '';
				$label          = isset( $webhook['label'] ) ? esc_attr( $webhook['label'] ) : '';
				$url            = isset( $webhook['url'] ) ? esc_attr( $webhook['url'] ) : '';
				$secret         = isset( $webhook['secret'] ) ? esc_attr( $webhook['secret'] ) : '';
				$wh_events      = isset( $webhook['events'] ) ? (array) $webhook['events'] : array_keys( $events );
				?>
			<div class="flexi-webhook-row" style="background:#fff; border:1px solid #ddd; border-radius:4px; padding:16px; margin-bottom:16px;">
				<table class="form-table" style="margin:0;">
					<tr>
						<th style="width:160px;"><label><?php esc_html_e( 'Label', 'flexi-abandon-cart-recovery' ); ?></label></th>
						<td><input type="text" name="flexi_acr_webhook[<?php echo esc_attr( $index ); ?>][label]" value="<?php echo $label; ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. Zapier integration', 'flexi-abandon-cart-recovery' ); ?>"></td>
					</tr>
					<tr>
						<th><label><?php esc_html_e( 'Endpoint URL', 'flexi-abandon-cart-recovery' ); ?></label></th>
						<td><input type="url" name="flexi_acr_webhook[<?php echo esc_attr( $index ); ?>][url]" value="<?php echo $url; ?>" class="large-text" placeholder="https://hooks.example.com/acr" required></td>
					</tr>
					<tr>
						<th><label><?php esc_html_e( 'Secret Key', 'flexi-abandon-cart-recovery' ); ?></label></th>
						<td>
							<input type="text" name="flexi_acr_webhook[<?php echo esc_attr( $index ); ?>][secret]" value="<?php echo $secret; ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Optional HMAC secret', 'flexi-abandon-cart-recovery' ); ?>">
							<p class="description"><?php esc_html_e( 'If set, we include a X-Flexi-ACR-Signature header (sha256=HMAC) so you can verify the payload.', 'flexi-abandon-cart-recovery' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Events', 'flexi-abandon-cart-recovery' ); ?></th>
						<td>
							<?php foreach ( $events as $event_slug => $event_label ) :
								$checked = in_array( $event_slug, $wh_events, true ) ? 'checked' : '';
								?>
							<label style="display:inline-block; margin-right:16px; margin-bottom:6px;">
								<input type="checkbox" name="flexi_acr_webhook[<?php echo esc_attr( $index ); ?>][events][]" value="<?php echo esc_attr( $event_slug ); ?>" <?php echo esc_attr( $checked ); ?>>
								<?php echo esc_html( $event_label ); ?>
							</label>
							<?php endforeach; ?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Active', 'flexi-abandon-cart-recovery' ); ?></th>
						<td>
							<input type="checkbox" name="flexi_acr_webhook[<?php echo esc_attr( $index ); ?>][active]" value="1" <?php echo esc_attr( $active_checked ); ?>>
							<button type="button" class="button flexi-remove-webhook" style="margin-left:16px; color:red;"><?php esc_html_e( 'Remove', 'flexi-abandon-cart-recovery' ); ?></button>
						</td>
					</tr>
				</table>
			</div>
			<?php endforeach; ?>
		</div>

		<p>
			<button type="button" id="flexi-add-webhook" class="button"><?php esc_html_e( '+ Add Webhook', 'flexi-abandon-cart-recovery' ); ?></button>
		</p>

		<p>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Webhooks', 'flexi-abandon-cart-recovery' ); ?></button>
		</p>
	</form>

	<hr>
	<h2><?php esc_html_e( 'Available Events', 'flexi-abandon-cart-recovery' ); ?></h2>
	<table class="widefat striped" style="max-width:600px;">
		<thead><tr><th><?php esc_html_e( 'Event Slug', 'flexi-abandon-cart-recovery' ); ?></th><th><?php esc_html_e( 'Description', 'flexi-abandon-cart-recovery' ); ?></th></tr></thead>
		<tbody>
			<?php foreach ( $events as $slug => $label ) : ?>
			<tr><td><code><?php echo esc_html( $slug ); ?></code></td><td><?php echo esc_html( $label ); ?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<hr>
	<h2><?php esc_html_e( 'REST API Endpoints', 'flexi-abandon-cart-recovery' ); ?></h2>
	<p><?php esc_html_e( 'All endpoints require WordPress authentication with manage_options capability. Base URL:', 'flexi-abandon-cart-recovery' ); ?>
	<code><?php echo esc_html( rest_url( 'flexi-acr/v1/' ) ); ?></code></p>
	<table class="widefat striped" style="max-width:800px;">
		<thead><tr><th><?php esc_html_e( 'Method', 'flexi-abandon-cart-recovery' ); ?></th><th><?php esc_html_e( 'Endpoint', 'flexi-abandon-cart-recovery' ); ?></th><th><?php esc_html_e( 'Description', 'flexi-abandon-cart-recovery' ); ?></th></tr></thead>
		<tbody>
			<tr><td>GET</td><td><code>/analytics/summary</code></td><td><?php esc_html_e( 'Summary statistics (date_from, date_to)', 'flexi-abandon-cart-recovery' ); ?></td></tr>
			<tr><td>GET</td><td><code>/analytics/funnel</code></td><td><?php esc_html_e( 'Conversion funnel data', 'flexi-abandon-cart-recovery' ); ?></td></tr>
			<tr><td>GET</td><td><code>/analytics/top-products</code></td><td><?php esc_html_e( 'Top abandoned products (limit, date_from, date_to)', 'flexi-abandon-cart-recovery' ); ?></td></tr>
			<tr><td>GET</td><td><code>/analytics/template-performance</code></td><td><?php esc_html_e( 'Email template performance', 'flexi-abandon-cart-recovery' ); ?></td></tr>
			<tr><td>GET</td><td><code>/analytics/roi</code></td><td><?php esc_html_e( 'ROI calculation (campaign_cost, date_from, date_to)', 'flexi-abandon-cart-recovery' ); ?></td></tr>
			<tr><td>GET</td><td><code>/carts</code></td><td><?php esc_html_e( 'List carts (status, per_page, page, date_from, date_to)', 'flexi-abandon-cart-recovery' ); ?></td></tr>
			<tr><td>GET</td><td><code>/carts/{id}</code></td><td><?php esc_html_e( 'Get single cart with items', 'flexi-abandon-cart-recovery' ); ?></td></tr>
			<tr><td>GET</td><td><code>/email-logs</code></td><td><?php esc_html_e( 'List email logs (per_page, page, date_from, date_to)', 'flexi-abandon-cart-recovery' ); ?></td></tr>
			<tr><td>GET</td><td><code>/settings</code></td><td><?php esc_html_e( 'Plugin settings (read-only)', 'flexi-abandon-cart-recovery' ); ?></td></tr>
			<tr><td>POST</td><td><code>/unsubscribe</code></td><td><?php esc_html_e( 'Unsubscribe user (email, token) — public', 'flexi-abandon-cart-recovery' ); ?></td></tr>
		</tbody>
	</table>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
	// Remove webhook row.
	$(document).on('click', '.flexi-remove-webhook', function() {
		$(this).closest('.flexi-webhook-row').remove();
	});

	// Add a new webhook row (clone the first one).
	$('#flexi-add-webhook').on('click', function() {
		var container = $('#flexi-webhooks-container');
		var rows      = container.find('.flexi-webhook-row');
		var newIndex  = rows.length;
		var newRow    = rows.first().clone(true);

		// Update all name attributes with new index.
		newRow.find('[name]').each(function() {
			var name = $(this).attr('name');
			$(this).attr('name', name.replace(/\[\d+\]/, '[' + newIndex + ']'));
			$(this).val('');
		});
		newRow.find('input[type="checkbox"]').prop('checked', true);
		container.append(newRow);
	});
});
</script>
