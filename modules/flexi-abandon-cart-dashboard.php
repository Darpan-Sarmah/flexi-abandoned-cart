<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugi
 * @
 * 
 * @link  https://abandoned-cart-recovery
 * @since 1.0.0
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/modules
 */

// $abandoned_carts_queries = new Flexi_Database_Queries();
// $user_cart_details       = $abandoned_carts_queries->select_db_query( 'acr_users_cart_details', '*' );

// $mail_track_detais = $abandoned_carts_queries->select_db_query( 'acr_emails_tracking', array( 'opened', 'clicked' ) );

// $total_carts = ! empty( $user_cart_details ) ? count( $user_cart_details ) : 0;
$total_carts     = 0;
$abandoned_carts = 0;
$purchased_carts = 0;
$active_carts    = 0;
$mail_opened     = 0;
$link_clicked    = 0;

$total_cost           = 0.0;
$purchased_total_cost = 0.0;
$recovered_ratio      = 0;

// if ( ! empty( $user_cart_details ) ) {
//     foreach ( $user_cart_details as $cart ) {

//         switch ( $cart['cart_status'] ) {
//             case 'abandoned':
//                 ++$abandoned_carts;
//                 break;
//             case 'purchased':
//                 ++$purchased_carts;
//                 break;
//             case 'active':
//                 ++$active_carts;
//                 break;
//         }

//         $cart_details    = json_decode( $cart['cart_details'], true );
//         $cart_total_cost = $cart_details['total_cost'];

//         $total_cost += $cart_total_cost;

//         if ( 'purchased' === $cart['cart_status'] ) {
//             $purchased_total_cost += $cart_total_cost;
//         }
//     }
// }
// if ( ! empty( $mail_track_detais ) ) {
//     foreach ( $mail_track_detais as $key => $value ) {
//         $link_clicked += isset( $value['clicked'] ) ? $value['clicked'] : 0;
//         $mail_opened  += isset( $value['opened'] ) ? $value['opened'] : 0;
//     }
// }
?>



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

</div>-rec-ibox-content charts">
				<h2> <?php esc_html_e('Revenue', 'flexi-abandon-cart-recovery');?> </h2>
			</div>
		</div>
	</div>

</div>
