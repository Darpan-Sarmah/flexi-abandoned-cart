<?php
/**
 * Provide an admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link  https://abandoned-cart-recovery
 * @since 1.0.0
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/modules/sub-modules
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Retrieve the scheduler activity.
 *
 * This function fetches the details about scheduled tasks related
 * to abandoned cart recovery.
 *
 * @return array Returns an array of scheduler activity data.
 */
function get_scheduler_activity() {

	$events     = _get_cron_array();
	$activities = array();

	if ( isset( $events ) ) {
		foreach ( $events as $timestamp => $event ) {

			foreach ( $event as $hook_name => $details ) {

				if ( strpos( $hook_name, 'acr_woocom_' ) !== false ) {
					foreach ( $details as $event_data ) {

						$args   = isset( $event_data['args'] ) ? implode( ', ', $event_data['args'] ) : 'None';
						$status = isset( $event_data['schedule'] ) ? 'Active' : 'Inactive';

						$activities[] = array(
							'hook_name'      => $hook_name,
							'next_execution' => gmdate( 'Y-m-d H:i:s', $timestamp ),
							'args'           => $args,
							'status'         => $status,
						);
					}
				}
			}
		}
	}

	return $activities;
}

$scheduler_activities = get_scheduler_activity();
?>
	<div class="wrap woocommerce">
	<table class="wp-list-table widefat fixed striped table-view-list">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Hook Name', 'flexi-abandon-cart-recovery' ); ?></th>
				<th><?php esc_html_e( 'Next Execution Time', 'flexi-abandon-cart-recovery' ); ?></th>
				<th><?php esc_html_e( 'Arguments', 'flexi-abandon-cart-recovery' ); ?></th>
				<th><?php esc_html_e( 'Status', 'flexi-abandon-cart-recovery' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! empty( $scheduler_activities ) ) : ?>
				<?php foreach ( $scheduler_activities as $activity ) : ?>
					<tr>
						<td><?php echo esc_html( $activity['hook_name'] ); ?></td>
						<td><?php echo esc_html( $activity['next_execution'] ); ?></td>
						<td><?php echo esc_html( $activity['args'] ); ?></td>
						<td><?php echo esc_html( $activity['status'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>

					<td colspan="4"><?php esc_html_e( 'No scheduled events found for the Abandoned Cart Recovery plugin.', 'flexi-abandon-cart-recovery' ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
			</div>

