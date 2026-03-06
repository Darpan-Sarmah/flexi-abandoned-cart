<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link  https://abandoned-cart-recovery
 * @since 1.0.0
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/modules
 */

$abandoned_carts_queries = new Flexi_Database_Queries();
$user_cart_details       = $abandoned_carts_queries->select_db_query( 'flexi_users_cart_details', '*' );

$mail_track_details = $abandoned_carts_queries->select_db_query( 'flexi_email_logs', array( 'opened', 'clicked' ) );

$total_carts     = ! empty( $user_cart_details ) ? count( $user_cart_details ) : 0;
$abandoned_carts = 0;
$purchased_carts = 0;
$active_carts    = 0;
$mail_opened     = 0;
$link_clicked    = 0;

$total_cost           = 0.0;
$purchased_total_cost = 0.0;
$recovered_ratio      = 0;

if ( ! empty( $user_cart_details ) ) {
    foreach ( $user_cart_details as $cart ) {

        switch ( $cart['cart_status'] ) {
            case 'abandoned':
                ++$abandoned_carts;
                break;
            case 'purchased':
                ++$purchased_carts;
                break;
            case 'active':
                ++$active_carts;
                break;
        }

        $cart_items      = $abandoned_carts_queries->select_db_query( 'flexi_users_cart_items', array( 'price', 'quantity' ), 'cart_id = ' . absint( $cart['id'] ) );
        $cart_total_cost = 0.0;
        if ( ! empty( $cart_items ) ) {
            foreach ( $cart_items as $item ) {
                $cart_total_cost += (float) $item['price'] * (int) $item['quantity'];
            }
        }

        $total_cost += $cart_total_cost;

        if ( 'purchased' === $cart['cart_status'] ) {
            $purchased_total_cost += $cart_total_cost;
        }
    }
}
if ( ! empty( $mail_track_details ) ) {
    foreach ( $mail_track_details as $key => $value ) {
        $link_clicked += isset( $value['clicked'] ) ? (int) $value['clicked'] : 0;
        $mail_opened  += isset( $value['opened'] ) ? (int) $value['opened'] : 0;
    }
}
?>

<div class="wrap woocommerce dashboard_page">

<h1 class="wp-heading-inline"><?php esc_html_e( 'Abandoned Cart Recovery — Dashboard', 'flexi-abandon-cart-recovery' ); ?></h1>

<!-- Date Range Filter -->
<form method="GET" style="display:inline-flex; align-items:center; gap:8px; margin:12px 0; flex-wrap:wrap;">
<input type="hidden" name="page" value="flexi-abandon-cart-recovery">
<label for="date_from"><?php esc_html_e( 'From:', 'flexi-abandon-cart-recovery' ); ?></label>
<input type="date" id="date_from" name="date_from" value="<?php echo esc_attr( $date_from ); ?>" class="regular-text" style="max-width:150px;">
<label for="date_to"><?php esc_html_e( 'To:', 'flexi-abandon-cart-recovery' ); ?></label>
<input type="date" id="date_to" name="date_to" value="<?php echo esc_attr( $date_to ); ?>" class="regular-text" style="max-width:150px;">
<label for="period"><?php esc_html_e( 'Group by:', 'flexi-abandon-cart-recovery' ); ?></label>
<select id="period" name="period">
<option value="daily"   <?php selected( $period, 'daily' ); ?>><?php esc_html_e( 'Daily', 'flexi-abandon-cart-recovery' ); ?></option>
<option value="weekly"  <?php selected( $period, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'flexi-abandon-cart-recovery' ); ?></option>
<option value="monthly" <?php selected( $period, 'monthly' ); ?>><?php esc_html_e( 'Monthly', 'flexi-abandon-cart-recovery' ); ?></option>
</select>
<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply', 'flexi-abandon-cart-recovery' ); ?></button>
<a href="<?php echo esc_url( admin_url( 'admin.php?page=flexi-abandon-cart-recovery' ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'flexi-abandon-cart-recovery' ); ?></a>
</form>

<!-- Export Buttons -->
<div style="margin-bottom:16px; display:flex; gap:8px; flex-wrap:wrap;">
<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => 'flexi-abandon-cart-recovery', 'export' => 'summary', 'date_from' => $date_from, 'date_to' => $date_to, 'flexi_export_nonce' => $export_nonce ) ) ); ?>">
&#11123; <?php esc_html_e( 'Export Summary CSV', 'flexi-abandon-cart-recovery' ); ?>
</a>
<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => 'flexi-abandon-cart-recovery', 'export' => 'emails', 'date_from' => $date_from, 'date_to' => $date_to, 'flexi_export_nonce' => $export_nonce ) ) ); ?>">
&#11123; <?php esc_html_e( 'Export Email Logs CSV', 'flexi-abandon-cart-recovery' ); ?>
</a>
<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => 'flexi-abandon-cart-recovery', 'export' => 'products', 'date_from' => $date_from, 'date_to' => $date_to, 'flexi_export_nonce' => $export_nonce ) ) ); ?>">
&#11123; <?php esc_html_e( 'Export Top Products CSV', 'flexi-abandon-cart-recovery' ); ?>
</a>
<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => 'flexi-abandon-cart-recovery', 'export' => 'templates', 'date_from' => $date_from, 'date_to' => $date_to, 'flexi_export_nonce' => $export_nonce ) ) ); ?>">
&#11123; <?php esc_html_e( 'Export Template Performance CSV', 'flexi-abandon-cart-recovery' ); ?>
</a>
</div>

<!-- Summary Stats Row 1 -->
<div class="ct-rec-grid-container">
<div class="ct-rec-ibox">
<div class="ct-rec-ibox-title">
<h3><?php esc_html_e( 'Total Carts', 'flexi-abandon-cart-recovery' ); ?></h3>
</div>
<div class="ct-rec-ibox-content">
<h1><?php echo esc_html( $stats['total_carts'] ); ?></h1>
<small><?php esc_html_e( 'All tracked carts.', 'flexi-abandon-cart-recovery' ); ?></small>
</div>
</div>
<div class="ct-rec-ibox">
<div class="ct-rec-ibox-title">
<h3><?php esc_html_e( 'Abandoned Carts', 'flexi-abandon-cart-recovery' ); ?></h3>
</div>
<div class="ct-rec-ibox-content">
<h1><?php echo esc_html( $stats['abandoned_carts'] ); ?></h1>
<small><?php esc_html_e( 'Carts not completed.', 'flexi-abandon-cart-recovery' ); ?></small>
</div>
</div>
<div class="ct-rec-ibox">
<div class="ct-rec-ibox-title">
<h3><?php esc_html_e( 'Recovered Carts', 'flexi-abandon-cart-recovery' ); ?></h3>
</div>
<div class="ct-rec-ibox-content">
<h1><?php echo esc_html( $stats['purchased_carts'] ); ?></h1>
<small><?php esc_html_e( 'Carts that led to a purchase.', 'flexi-abandon-cart-recovery' ); ?></small>
</div>
</div>
</div>

<!-- Summary Stats Row 2 -->
<div class="ct-rec-grid-container">
<div class="ct-rec-ibox">
<div class="ct-rec-ibox-title">
<h3><?php esc_html_e( 'Recovery Rate', 'flexi-abandon-cart-recovery' ); ?></h3>
</div>
<div class="ct-rec-ibox-content">
<h1><?php echo esc_html( $stats['recovery_rate'] ); ?>%</h1>
<small><?php esc_html_e( 'Recovered / Abandoned.', 'flexi-abandon-cart-recovery' ); ?></small>
</div>
</div>
<div class="ct-rec-ibox">
<div class="ct-rec-ibox-title">
<h3><?php esc_html_e( 'Abandoned Revenue', 'flexi-abandon-cart-recovery' ); ?></h3>
</div>
<div class="ct-rec-ibox-content">
<h1><?php echo esc_html( $currency . number_format( $stats['abandoned_revenue'], 2 ) ); ?></h1>
<small><?php esc_html_e( 'Potential revenue in abandoned carts.', 'flexi-abandon-cart-recovery' ); ?></small>
</div>
</div>
<div class="ct-rec-ibox">
<div class="ct-rec-ibox-title">
<h3><?php esc_html_e( 'Recovered Revenue', 'flexi-abandon-cart-recovery' ); ?></h3>
</div>
<div class="ct-rec-ibox-content">
<h1><?php echo esc_html( $currency . number_format( $stats['recovered_revenue'], 2 ) ); ?></h1>
<small><?php esc_html_e( 'Revenue recovered via plugin.', 'flexi-abandon-cart-recovery' ); ?></small>
</div>
</div>
</div>

<!-- Email Stats Row -->
<div class="ct-rec-grid-container">
<div class="ct-rec-ibox">
<div class="ct-rec-ibox-title">
<h3><?php esc_html_e( 'Emails Sent', 'flexi-abandon-cart-recovery' ); ?></h3>
</div>
<div class="ct-rec-ibox-content">
<h1><?php echo esc_html( $stats['total_sent'] ); ?></h1>
<small><?php esc_html_e( 'Total recovery emails sent.', 'flexi-abandon-cart-recovery' ); ?></small>
</div>
</div>
<div class="ct-rec-ibox">
<div class="ct-rec-ibox-title">
<h3><?php esc_html_e( 'Email Open Rate', 'flexi-abandon-cart-recovery' ); ?></h3>
</div>
<div class="ct-rec-ibox-content">
<h1><?php echo esc_html( $stats['email_open_rate'] ); ?>%</h1>
<small><?php echo esc_html( $stats['total_opened'] ); ?> <?php esc_html_e( 'opened', 'flexi-abandon-cart-recovery' ); ?></small>
</div>
</div>
<div class="ct-rec-ibox">
<div class="ct-rec-ibox-title">
<h3><?php esc_html_e( 'Email Click Rate', 'flexi-abandon-cart-recovery' ); ?></h3>
</div>
<div class="ct-rec-ibox-content">
<h1><?php echo esc_html( $stats['email_click_rate'] ); ?>%</h1>
<small><?php echo esc_html( $stats['total_clicked'] ); ?> <?php esc_html_e( 'clicked', 'flexi-abandon-cart-recovery' ); ?></small>
</div>
</div>
</div>

<br>

<!-- Charts Row -->
<div class="ct-rec-grid-container-charts">
<div class="ct-rec-ibox chart-wrapper">
<div class="ct-rec-ibox-content charts">
<h2><?php esc_html_e( 'Carts Over Time', 'flexi-abandon-cart-recovery' ); ?></h2>
</div>
<div class="ct-rec-ibox-title">
<canvas id="ordersChart"></canvas>
</div>
</div>
<div class="ct-rec-ibox chart-wrapper">
<div class="ct-rec-ibox-content charts">
<h2><?php esc_html_e( 'Emails Sent & Opened', 'flexi-abandon-cart-recovery' ); ?></h2>
</div>
<div class="ct-rec-ibox-title">
<canvas id="totalMailSentAndOpened"></canvas>
</div>
</div>
<div class="ct-rec-ibox chart-wrapper">
<div class="ct-rec-ibox-content charts">
<h2><?php esc_html_e( 'Emails Sent & Clicked', 'flexi-abandon-cart-recovery' ); ?></h2>
</div>
<div class="ct-rec-ibox-title">
<canvas id="totalMailSentAndClicked"></canvas>
</div>
</div>
</div>

<div class='wrap woocommerce dashboard_page'>

	<div class="ct-rec-grid-container">
		<div class="ct-rec-ibox">
			<div class="ct-rec-ibox-title">
				<h3> <?php esc_html_e('Total Orders', 'flexi-abandon-cart-recovery');?> </h3>
			</div>
			<div class="ct-rec-ibox-content">
				<h1> <?php echo esc_html($total_carts); ?> </h1>
				<small> <?php esc_html_e('Total Carts.', 'flexi-abandon-cart-recovery');?> </small>
			</div>
		</div>

		<div class="ct-rec-ibox">
			<div class="ct-rec-ibox-title">
				<h3> <?php esc_html_e('Abandoned Orders', 'flexi-abandon-cart-recovery');?> </h3>
			</div>
			<div class="ct-rec-ibox-content">
				<h1> <?php echo esc_html($abandoned_carts); ?> </h1>
				<small> <?php esc_html_e('Total Abandoned Carts.', 'flexi-abandon-cart-recovery');?> </small>
			</div>
		</div>

		<div class="ct-rec-ibox">
			<div class="ct-rec-ibox-title">
				<h3><?php esc_html_e('Recovered Orders', 'flexi-abandon-cart-recovery');?></h3>
			</div>
			<div class="ct-rec-ibox-content">
				<h1><?php echo esc_html($purchased_carts); ?></h1>
				<small> <?php esc_html_e('Total Recovered Carts.', 'flexi-abandon-cart-recovery');?> </small>
			</div>
		</div>
	</div>

	<div class="ct-rec-grid-container">
		<div class="ct-rec-ibox">
			<div class="ct-rec-ibox-title">
				<h3> <?php esc_html_e('Recovered Revenue', 'flexi-abandon-cart-recovery');?> </h3>
			</div>
			<div class="ct-rec-ibox-content">
				<h1> <?php echo esc_html(get_woocommerce_currency_symbol() . $purchased_total_cost); ?> </h1>
				<small> <?php esc_html_e('Total Recovered Revenue.', 'flexi-abandon-cart-recovery');?> </small>
			</div>
		</div>

		<div class="ct-rec-ibox">
			<div class="ct-rec-ibox-title">
				<h3><?php esc_html_e('Link Clicked', 'flexi-abandon-cart-recovery');?></h3>
			</div>
			<div class="ct-rec-ibox-content">
				<h1><?php echo esc_html($link_clicked); ?></h1>
				<small> <?php esc_html_e('Total Count Of Link Opened.', 'flexi-abandon-cart-recovery');?> </small>
			</div>
		</div>

		<div class="ct-rec-ibox">
			<div class="ct-rec-ibox-title">
				<h3><?php esc_html_e('Mail Opened Count', 'flexi-abandon-cart-recovery');?></h3>
			</div>
			<div class="ct-rec-ibox-content">
				<h1><?php echo esc_html($mail_opened); ?></h1>
				<small> <?php esc_html_e('Total Count Of Mail Opened.', 'flexi-abandon-cart-recovery');?> </small>
			</div>
		</div>

	</div>


	<br>
	<br>
	<div class="ct-rec-grid-container-charts">
		<div class="ct-rec-ibox chart-wrapper">
			<div class="ct-rec-ibox-title">
				<canvas id="ordersChart"></canvas>

			</div>
			<div class="ct-rec-ibox-content charts">
				<h2> <?php esc_html_e('Carts', 'flexi-abandon-cart-recovery');?> </h2>
			</div>
		</div>
		<div class="ct-rec-ibox chart-wrapper">
			<div class="ct-rec-ibox-title">
				<canvas id="totalMailSentAndOpened"></canvas>

			</div>
			<div class="ct-rec-ibox-content charts">
				<h2> <?php esc_html_e('Mail Send And Opened', 'flexi-abandon-cart-recovery');?> </h2>
			</div>
		</div>

		<div class="ct-rec-ibox chart-wrapper">
			<div class="ct-rec-ibox-title">
				<canvas id="totalMailSentAndClicked"></canvas>

			</div>
			<div class="ct-rec-ibox-content charts">
				<h2> <?php esc_html_e('Mail Send And Purchase Link Clicked', 'flexi-abandon-cart-recovery');?> </h2>
			</div>
		</div>

	</div>

	<div class="ct-rec-grid-container-revenue">
		<div class="ct-rec-ibox chart-wrapper">
			<div class="ct-rec-ibox-title">
				<canvas id="revenueChart"></canvas>

			</div>
			<div class="ct-rec-ibox-content charts">
				<h2> <?php esc_html_e('Revenue', 'flexi-abandon-cart-recovery');?> </h2>
			</div>
		</div>
	</div>

</div>

</div>

</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
var ordersData  = <?php echo wp_json_encode( $orders_chart ); ?>;
var emailData   = <?php echo wp_json_encode( $email_chart ); ?>;
var revenueData = <?php echo wp_json_encode( $revenue_chart ); ?>;

// Carts chart.
if (document.getElementById('ordersChart') && typeof Chart !== 'undefined') {
new Chart(document.getElementById('ordersChart').getContext('2d'), {
type: 'bar',
data: {
labels: ordersData.labels,
datasets: [
{ label: '<?php echo esc_js( __( 'Abandoned', 'flexi-abandon-cart-recovery' ) ); ?>', data: ordersData.abandoned, backgroundColor: 'rgba(231,76,60,0.7)' },
{ label: '<?php echo esc_js( __( 'Recovered', 'flexi-abandon-cart-recovery' ) ); ?>', data: ordersData.recovered, backgroundColor: 'rgba(46,204,113,0.7)' }
]
},
options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
}

// Email sent & opened chart.
if (document.getElementById('totalMailSentAndOpened') && typeof Chart !== 'undefined') {
new Chart(document.getElementById('totalMailSentAndOpened').getContext('2d'), {
type: 'line',
data: {
labels: emailData.labels,
datasets: [
{ label: '<?php echo esc_js( __( 'Sent', 'flexi-abandon-cart-recovery' ) ); ?>', data: emailData.sent, borderColor: 'rgba(52,152,219,1)', fill: false },
{ label: '<?php echo esc_js( __( 'Opened', 'flexi-abandon-cart-recovery' ) ); ?>', data: emailData.opened, borderColor: 'rgba(46,204,113,1)', fill: false }
]
},
options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
}

// Email sent & clicked chart.
if (document.getElementById('totalMailSentAndClicked') && typeof Chart !== 'undefined') {
new Chart(document.getElementById('totalMailSentAndClicked').getContext('2d'), {
type: 'line',
data: {
labels: emailData.labels,
datasets: [
{ label: '<?php echo esc_js( __( 'Sent', 'flexi-abandon-cart-recovery' ) ); ?>', data: emailData.sent, borderColor: 'rgba(52,152,219,1)', fill: false },
{ label: '<?php echo esc_js( __( 'Clicked', 'flexi-abandon-cart-recovery' ) ); ?>', data: emailData.clicked, borderColor: 'rgba(231,76,60,1)', fill: false }
]
},
options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
}

// Revenue chart.
if (document.getElementById('revenueChart') && typeof Chart !== 'undefined') {
new Chart(document.getElementById('revenueChart').getContext('2d'), {
type: 'line',
data: {
labels: revenueData.labels,
datasets: [
{ label: '<?php echo esc_js( __( 'Abandoned Revenue', 'flexi-abandon-cart-recovery' ) ); ?>', data: revenueData.abandoned_rev, borderColor: 'rgba(231,76,60,1)', fill: true, backgroundColor: 'rgba(231,76,60,0.1)' },
{ label: '<?php echo esc_js( __( 'Recovered Revenue', 'flexi-abandon-cart-recovery' ) ); ?>', data: revenueData.recovered_rev, borderColor: 'rgba(46,204,113,1)', fill: true, backgroundColor: 'rgba(46,204,113,0.1)' }
]
},
options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
}
});
</script>
