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

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

// Load analytics class.
require_once FLEXI_ABANDON_CART_RECOVERY_DIR . 'modules/sub-modules/class-flexi-cart-analytics.php';

// Date range filter.
$date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '';
$date_to   = isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '';
$period    = isset( $_GET['period'] ) ? sanitize_text_field( wp_unslash( $_GET['period'] ) ) : 'daily';

$valid_periods = array( 'daily', 'weekly', 'monthly' );
if ( ! in_array( $period, $valid_periods, true ) ) {
$period = 'daily';
}

// Handle CSV export.
if ( isset( $_GET['export'] ) && current_user_can( 'manage_options' ) ) {
if ( ! isset( $_GET['flexi_export_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['flexi_export_nonce'] ) ), 'flexi_acr_export' ) ) {
wp_die( esc_html__( 'Nonce verification failed.', 'flexi-abandon-cart-recovery' ) );
}
$analytics   = new Flexi_Cart_Analytics();
$export_type = sanitize_text_field( wp_unslash( $_GET['export'] ) );
$analytics->export_csv( $export_type, $date_from, $date_to );
exit;
}

$analytics   = new Flexi_Cart_Analytics();
$stats       = $analytics->get_summary_stats( $date_from, $date_to );
$funnel      = $analytics->get_conversion_funnel( $date_from, $date_to );
$top_products = $analytics->get_top_abandoned_products( 5, $date_from, $date_to );
$tpl_perf    = $analytics->get_template_performance( $date_from, $date_to );

// Chart data.
$orders_chart  = $analytics->get_orders_chart_data( $period, $date_from, $date_to );
$revenue_chart = $analytics->get_revenue_chart_data( $period, $date_from, $date_to );
$email_chart   = $analytics->get_email_chart_data( $period, $date_from, $date_to );

$currency = get_woocommerce_currency_symbol();
$export_nonce = wp_create_nonce( 'flexi_acr_export' );
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

<!-- Revenue Chart -->
<div class="ct-rec-grid-container-revenue">
<div class="ct-rec-ibox chart-wrapper">
<div class="ct-rec-ibox-content charts">
<h2><?php esc_html_e( 'Revenue', 'flexi-abandon-cart-recovery' ); ?></h2>
</div>
<div class="ct-rec-ibox-title">
<canvas id="revenueChart"></canvas>
</div>
</div>
</div>

<br>

<!-- Conversion Funnel -->
<div class="ct-rec-grid-container-revenue">
<div class="ct-rec-ibox" style="padding:20px;">
<h2 style="margin-top:0;"><?php esc_html_e( 'Conversion Funnel', 'flexi-abandon-cart-recovery' ); ?></h2>
<table class="widefat striped" style="width:100%;">
<thead>
<tr>
<th><?php esc_html_e( 'Stage', 'flexi-abandon-cart-recovery' ); ?></th>
<th><?php esc_html_e( 'Count', 'flexi-abandon-cart-recovery' ); ?></th>
<th><?php esc_html_e( '% of Total Carts', 'flexi-abandon-cart-recovery' ); ?></th>
</tr>
</thead>
<tbody>
<?php foreach ( $funnel as $stage ) : ?>
<tr>
<td><?php echo esc_html( $stage['stage'] ); ?></td>
<td><?php echo esc_html( $stage['count'] ); ?></td>
<td>
<div style="display:flex; align-items:center; gap:8px;">
<div style="background:#4CAF50; height:14px; width:<?php echo esc_attr( $stage['percentage'] ); ?>%; max-width:200px; border-radius:4px;"></div>
<?php echo esc_html( $stage['percentage'] ); ?>%
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

<br>

<!-- Two-column: Top Products + Template Performance -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">

<!-- Top Abandoned Products -->
<div class="ct-rec-ibox" style="padding:20px;">
<h2 style="margin-top:0;"><?php esc_html_e( 'Top Abandoned Products', 'flexi-abandon-cart-recovery' ); ?></h2>
<?php if ( ! empty( $top_products ) ) : ?>
<table class="widefat striped">
<thead>
<tr>
<th><?php esc_html_e( 'Product', 'flexi-abandon-cart-recovery' ); ?></th>
<th><?php esc_html_e( 'Times Abandoned', 'flexi-abandon-cart-recovery' ); ?></th>
<th><?php esc_html_e( 'Total Value', 'flexi-abandon-cart-recovery' ); ?></th>
</tr>
</thead>
<tbody>
<?php foreach ( $top_products as $product ) : ?>
<tr>
<td><?php echo esc_html( $product['item_name'] ); ?></td>
<td><?php echo esc_html( $product['abandon_count'] ); ?></td>
<td><?php echo esc_html( $currency . number_format( $product['total_value'], 2 ) ); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else : ?>
<p><?php esc_html_e( 'No abandoned product data available yet.', 'flexi-abandon-cart-recovery' ); ?></p>
<?php endif; ?>
</div>

<!-- Email Template Performance -->
<div class="ct-rec-ibox" style="padding:20px;">
<h2 style="margin-top:0;"><?php esc_html_e( 'Email Template Performance', 'flexi-abandon-cart-recovery' ); ?></h2>
<?php if ( ! empty( $tpl_perf ) ) : ?>
<table class="widefat striped">
<thead>
<tr>
<th><?php esc_html_e( 'Template', 'flexi-abandon-cart-recovery' ); ?></th>
<th><?php esc_html_e( 'Sent', 'flexi-abandon-cart-recovery' ); ?></th>
<th><?php esc_html_e( 'Open %', 'flexi-abandon-cart-recovery' ); ?></th>
<th><?php esc_html_e( 'Click %', 'flexi-abandon-cart-recovery' ); ?></th>
</tr>
</thead>
<tbody>
<?php foreach ( $tpl_perf as $tpl ) : ?>
<tr>
<td><?php echo esc_html( $tpl['template_name'] ); ?></td>
<td><?php echo esc_html( $tpl['total_sent'] ); ?></td>
<td><?php echo esc_html( $tpl['open_rate'] ); ?>%</td>
<td><?php echo esc_html( $tpl['click_rate'] ); ?>%</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else : ?>
<p><?php esc_html_e( 'No template performance data available yet.', 'flexi-abandon-cart-recovery' ); ?></p>
<?php endif; ?>
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
