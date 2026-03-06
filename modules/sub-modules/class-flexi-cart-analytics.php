<?php
/**
 * Advanced Analytics Engine for Flexi Abandoned Cart Recovery.
 *
 * Provides comprehensive analytics including revenue tracking, ROI calculations,
 * conversion funnel data, email performance metrics, and report exports.
 *
 * @link  https://abandoned-cart-recovery
 * @since 1.0.0
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/modules/sub-modules
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flexi_Cart_Analytics
 *
 * @since 1.0.0
 */
class Flexi_Cart_Analytics {

	/**
	 * Database queries instance.
	 *
	 * @var Flexi_Database_Queries
	 */
	private $db;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->db = new Flexi_Database_Queries();
	}

	/**
	 * Get summary statistics for the dashboard.
	 *
	 * @since 1.0.0
	 *
	 * @param string $date_from Start date (Y-m-d). Optional.
	 * @param string $date_to   End date (Y-m-d). Optional.
	 * @return array Summary statistics.
	 */
	public function get_summary_stats( $date_from = '', $date_to = '' ) {
		global $wpdb;

		$where_date = $this->build_date_where( 'ucd.created_at', $date_from, $date_to );

		// Total carts.
		$total_carts = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT ucd.id) FROM {$wpdb->prefix}flexi_users_cart_details ucd
			 WHERE 1=1 {$where_date}"
		);

		// Abandoned carts.
		$abandoned_carts = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT ucd.id) FROM {$wpdb->prefix}flexi_users_cart_details ucd
			 WHERE ucd.cart_status = 'abandoned' {$where_date}"
		);

		// Recovered (purchased) carts.
		$purchased_carts = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT ucd.id) FROM {$wpdb->prefix}flexi_users_cart_details ucd
			 WHERE ucd.cart_status = 'purchased' {$where_date}"
		);

		// Active carts.
		$active_carts = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT ucd.id) FROM {$wpdb->prefix}flexi_users_cart_details ucd
			 WHERE ucd.cart_status = 'active' {$where_date}"
		);

		// Cart items totals.
		$revenue_data = $wpdb->get_row(
			"SELECT
				SUM(CASE WHEN ucd.cart_status = 'abandoned' THEN uci.price * uci.quantity ELSE 0 END) as abandoned_revenue,
				SUM(CASE WHEN ucd.cart_status = 'purchased' THEN uci.price * uci.quantity ELSE 0 END) as recovered_revenue
			 FROM {$wpdb->prefix}flexi_users_cart_details ucd
			 LEFT JOIN {$wpdb->prefix}flexi_users_cart_items uci ON ucd.id = uci.cart_id
			 WHERE 1=1 {$where_date}",
			ARRAY_A
		);

		$abandoned_revenue = isset( $revenue_data['abandoned_revenue'] ) ? floatval( $revenue_data['abandoned_revenue'] ) : 0.0;
		$recovered_revenue = isset( $revenue_data['recovered_revenue'] ) ? floatval( $revenue_data['recovered_revenue'] ) : 0.0;

		// Email stats.
		$email_date_where = $this->build_date_where( 'el.send_time', $date_from, $date_to );
		$email_stats      = $wpdb->get_row(
			"SELECT
				COUNT(id) as total_sent,
				SUM(opened) as total_opened,
				SUM(clicked) as total_clicked,
				SUM(purchased) as total_purchased,
				SUM(CASE WHEN status='success' THEN 1 ELSE 0 END) as success_count,
				SUM(CASE WHEN status='error' THEN 1 ELSE 0 END) as error_count
			 FROM {$wpdb->prefix}flexi_email_logs el
			 WHERE 1=1 {$email_date_where}",
			ARRAY_A
		);

		$total_sent      = isset( $email_stats['total_sent'] ) ? (int) $email_stats['total_sent'] : 0;
		$total_opened    = isset( $email_stats['total_opened'] ) ? (int) $email_stats['total_opened'] : 0;
		$total_clicked   = isset( $email_stats['total_clicked'] ) ? (int) $email_stats['total_clicked'] : 0;
		$total_purchased = isset( $email_stats['total_purchased'] ) ? (int) $email_stats['total_purchased'] : 0;

		// Recovery rate.
		$recovery_rate = $abandoned_carts > 0 ? round( ( $purchased_carts / $abandoned_carts ) * 100, 2 ) : 0;

		// Email open rate.
		$open_rate  = $total_sent > 0 ? round( ( $total_opened / $total_sent ) * 100, 2 ) : 0;
		$click_rate = $total_sent > 0 ? round( ( $total_clicked / $total_sent ) * 100, 2 ) : 0;

		return apply_filters(
			'flexi_acr_analytics_summary',
			array(
				'total_carts'        => $total_carts,
				'abandoned_carts'    => $abandoned_carts,
				'purchased_carts'    => $purchased_carts,
				'active_carts'       => $active_carts,
				'abandoned_revenue'  => $abandoned_revenue,
				'recovered_revenue'  => $recovered_revenue,
				'recovery_rate'      => $recovery_rate,
				'total_sent'         => $total_sent,
				'total_opened'       => $total_opened,
				'total_clicked'      => $total_clicked,
				'total_purchased'    => $total_purchased,
				'email_open_rate'    => $open_rate,
				'email_click_rate'   => $click_rate,
				'success_emails'     => isset( $email_stats['success_count'] ) ? (int) $email_stats['success_count'] : 0,
				'error_emails'       => isset( $email_stats['error_count'] ) ? (int) $email_stats['error_count'] : 0,
			)
		);
	}

	/**
	 * Get conversion funnel data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $date_from Start date. Optional.
	 * @param string $date_to   End date. Optional.
	 * @return array Funnel stages with counts and percentages.
	 */
	public function get_conversion_funnel( $date_from = '', $date_to = '' ) {
		$stats      = $this->get_summary_stats( $date_from, $date_to );
		$total      = max( $stats['total_carts'], 1 );

		return array(
			array(
				'stage'      => __( 'Total Carts', 'flexi-abandon-cart-recovery' ),
				'count'      => $stats['total_carts'],
				'percentage' => 100,
			),
			array(
				'stage'      => __( 'Abandoned', 'flexi-abandon-cart-recovery' ),
				'count'      => $stats['abandoned_carts'],
				'percentage' => round( ( $stats['abandoned_carts'] / $total ) * 100, 1 ),
			),
			array(
				'stage'      => __( 'Emails Sent', 'flexi-abandon-cart-recovery' ),
				'count'      => $stats['total_sent'],
				'percentage' => round( ( $stats['total_sent'] / $total ) * 100, 1 ),
			),
			array(
				'stage'      => __( 'Emails Opened', 'flexi-abandon-cart-recovery' ),
				'count'      => $stats['total_opened'],
				'percentage' => round( ( $stats['total_opened'] / $total ) * 100, 1 ),
			),
			array(
				'stage'      => __( 'Links Clicked', 'flexi-abandon-cart-recovery' ),
				'count'      => $stats['total_clicked'],
				'percentage' => round( ( $stats['total_clicked'] / $total ) * 100, 1 ),
			),
			array(
				'stage'      => __( 'Recovered', 'flexi-abandon-cart-recovery' ),
				'count'      => $stats['purchased_carts'],
				'percentage' => round( ( $stats['purchased_carts'] / $total ) * 100, 1 ),
			),
		);
	}

	/**
	 * Get email performance by template.
	 *
	 * @since 1.0.0
	 *
	 * @param string $date_from Start date. Optional.
	 * @param string $date_to   End date. Optional.
	 * @return array Template performance data.
	 */
	public function get_template_performance( $date_from = '', $date_to = '' ) {
		global $wpdb;

		$date_where = $this->build_date_where( 'el.send_time', $date_from, $date_to );

		$results = $wpdb->get_results(
			"SELECT
				el.template_used,
				COUNT(el.id) as total_sent,
				SUM(el.opened) as total_opened,
				SUM(el.clicked) as total_clicked,
				SUM(el.purchased) as total_purchased,
				SUM(CASE WHEN el.status='success' THEN 1 ELSE 0 END) as success_count,
				SUM(CASE WHEN el.status='error' THEN 1 ELSE 0 END) as error_count
			 FROM {$wpdb->prefix}flexi_email_logs el
			 WHERE 1=1 {$date_where}
			 GROUP BY el.template_used
			 ORDER BY total_sent DESC",
			ARRAY_A
		);

		$performance = array();
		foreach ( $results as $row ) {
			$sent    = max( (int) $row['total_sent'], 1 );
			$opened  = (int) $row['total_opened'];
			$clicked = (int) $row['total_clicked'];

			$performance[] = array(
				'template_name'  => $row['template_used'],
				'total_sent'     => (int) $row['total_sent'],
				'total_opened'   => $opened,
				'total_clicked'  => $clicked,
				'total_purchased'=> (int) $row['total_purchased'],
				'open_rate'      => round( ( $opened / $sent ) * 100, 2 ),
				'click_rate'     => round( ( $clicked / $sent ) * 100, 2 ),
				'success_count'  => (int) $row['success_count'],
				'error_count'    => (int) $row['error_count'],
			);
		}

		return $performance;
	}

	/**
	 * Get top abandoned products.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $limit     Max number of products to return.
	 * @param string $date_from Start date. Optional.
	 * @param string $date_to   End date. Optional.
	 * @return array Top abandoned products.
	 */
	public function get_top_abandoned_products( $limit = 10, $date_from = '', $date_to = '' ) {
		global $wpdb;

		$date_where = $this->build_date_where( 'ucd.created_at', $date_from, $date_to );
		$limit      = absint( $limit );

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					uci.item_name,
					uci.item_id,
					COUNT(uci.id) as abandon_count,
					SUM(uci.quantity) as total_quantity,
					SUM(uci.price * uci.quantity) as total_value
				 FROM {$wpdb->prefix}flexi_users_cart_items uci
				 JOIN {$wpdb->prefix}flexi_users_cart_details ucd ON uci.cart_id = ucd.id
				 WHERE ucd.cart_status = 'abandoned' {$date_where}
				 GROUP BY uci.item_id, uci.item_name
				 ORDER BY abandon_count DESC
				 LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		return $results ? $results : array();
	}

	/**
	 * Get abandoned cart heatmap data (hour x day_of_week).
	 *
	 * @since 1.0.0
	 *
	 * @param string $date_from Start date. Optional.
	 * @param string $date_to   End date. Optional.
	 * @return array Heatmap data [day][hour] => count.
	 */
	public function get_abandonment_heatmap( $date_from = '', $date_to = '' ) {
		global $wpdb;

		$date_where = $this->build_date_where( 'abandon_time', $date_from, $date_to );

		$results = $wpdb->get_results(
			"SELECT
				DAYOFWEEK(abandon_time) as day_of_week,
				HOUR(abandon_time) as hour_of_day,
				COUNT(id) as count
			 FROM {$wpdb->prefix}flexi_users_cart_details
			 WHERE cart_status = 'abandoned'
			   AND abandon_time IS NOT NULL {$date_where}
			 GROUP BY day_of_week, hour_of_day",
			ARRAY_A
		);

		$heatmap = array();
		foreach ( $results as $row ) {
			$day  = (int) $row['day_of_week'];
			$hour = (int) $row['hour_of_day'];
			if ( ! isset( $heatmap[ $day ] ) ) {
				$heatmap[ $day ] = array();
			}
			$heatmap[ $day ][ $hour ] = (int) $row['count'];
		}

		return $heatmap;
	}

	/**
	 * Get chart data for orders (abandoned vs recovered) over time.
	 *
	 * @since 1.0.0
	 *
	 * @param string $period    'daily', 'weekly', or 'monthly'.
	 * @param string $date_from Start date. Optional.
	 * @param string $date_to   End date. Optional.
	 * @return array Chart labels and datasets.
	 */
	public function get_orders_chart_data( $period = 'daily', $date_from = '', $date_to = '' ) {
		global $wpdb;

		$group_format = $this->get_group_format( $period );
		$date_where   = $this->build_date_where( 'created_at', $date_from, $date_to );

		$results = $wpdb->get_results(
			"SELECT
				DATE_FORMAT(created_at, '{$group_format}') as period_label,
				SUM(CASE WHEN cart_status = 'abandoned' THEN 1 ELSE 0 END) as abandoned,
				SUM(CASE WHEN cart_status = 'purchased' THEN 1 ELSE 0 END) as recovered,
				COUNT(id) as total
			 FROM {$wpdb->prefix}flexi_users_cart_details
			 WHERE 1=1 {$date_where}
			 GROUP BY period_label
			 ORDER BY MIN(created_at) ASC",
			ARRAY_A
		);

		$labels    = array();
		$abandoned = array();
		$recovered = array();
		$total     = array();

		foreach ( $results as $row ) {
			$labels[]    = $row['period_label'];
			$abandoned[] = (int) $row['abandoned'];
			$recovered[] = (int) $row['recovered'];
			$total[]     = (int) $row['total'];
		}

		return array(
			'labels'    => $labels,
			'abandoned' => $abandoned,
			'recovered' => $recovered,
			'total'     => $total,
		);
	}

	/**
	 * Get revenue chart data over time.
	 *
	 * @since 1.0.0
	 *
	 * @param string $period    'daily', 'weekly', or 'monthly'.
	 * @param string $date_from Start date. Optional.
	 * @param string $date_to   End date. Optional.
	 * @return array Chart labels and datasets.
	 */
	public function get_revenue_chart_data( $period = 'daily', $date_from = '', $date_to = '' ) {
		global $wpdb;

		$group_format = $this->get_group_format( $period );
		$date_where   = $this->build_date_where( 'ucd.created_at', $date_from, $date_to );

		$results = $wpdb->get_results(
			"SELECT
				DATE_FORMAT(ucd.created_at, '{$group_format}') as period_label,
				SUM(CASE WHEN ucd.cart_status = 'abandoned' THEN uci.price * uci.quantity ELSE 0 END) as abandoned_rev,
				SUM(CASE WHEN ucd.cart_status = 'purchased' THEN uci.price * uci.quantity ELSE 0 END) as recovered_rev
			 FROM {$wpdb->prefix}flexi_users_cart_details ucd
			 LEFT JOIN {$wpdb->prefix}flexi_users_cart_items uci ON ucd.id = uci.cart_id
			 WHERE 1=1 {$date_where}
			 GROUP BY period_label
			 ORDER BY MIN(ucd.created_at) ASC",
			ARRAY_A
		);

		$labels       = array();
		$abandoned_rv = array();
		$recovered_rv = array();

		foreach ( $results as $row ) {
			$labels[]       = $row['period_label'];
			$abandoned_rv[] = round( floatval( $row['abandoned_rev'] ), 2 );
			$recovered_rv[] = round( floatval( $row['recovered_rev'] ), 2 );
		}

		return array(
			'labels'        => $labels,
			'abandoned_rev' => $abandoned_rv,
			'recovered_rev' => $recovered_rv,
		);
	}

	/**
	 * Get email send/open/click chart data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $period    'daily', 'weekly', or 'monthly'.
	 * @param string $date_from Start date. Optional.
	 * @param string $date_to   End date. Optional.
	 * @return array Chart labels and datasets.
	 */
	public function get_email_chart_data( $period = 'daily', $date_from = '', $date_to = '' ) {
		global $wpdb;

		$group_format = $this->get_group_format( $period );
		$date_where   = $this->build_date_where( 'send_time', $date_from, $date_to );

		$results = $wpdb->get_results(
			"SELECT
				DATE_FORMAT(send_time, '{$group_format}') as period_label,
				COUNT(id) as total_sent,
				SUM(opened) as total_opened,
				SUM(clicked) as total_clicked
			 FROM {$wpdb->prefix}flexi_email_logs
			 WHERE status = 'success' {$date_where}
			 GROUP BY period_label
			 ORDER BY MIN(send_time) ASC",
			ARRAY_A
		);

		$labels  = array();
		$sent    = array();
		$opened  = array();
		$clicked = array();

		foreach ( $results as $row ) {
			$labels[]  = $row['period_label'];
			$sent[]    = (int) $row['total_sent'];
			$opened[]  = (int) $row['total_opened'];
			$clicked[] = (int) $row['total_clicked'];
		}

		return array(
			'labels'  => $labels,
			'sent'    => $sent,
			'opened'  => $opened,
			'clicked' => $clicked,
		);
	}

	/**
	 * Get ROI calculation.
	 *
	 * @since 1.0.0
	 *
	 * @param float  $campaign_cost Monthly cost of the plugin/campaigns.
	 * @param string $date_from     Start date. Optional.
	 * @param string $date_to       End date. Optional.
	 * @return array ROI data.
	 */
	public function get_roi_data( $campaign_cost = 0.0, $date_from = '', $date_to = '' ) {
		$stats             = $this->get_summary_stats( $date_from, $date_to );
		$recovered_revenue = $stats['recovered_revenue'];
		$net_profit        = $recovered_revenue - floatval( $campaign_cost );
		$roi               = $campaign_cost > 0 ? round( ( $net_profit / floatval( $campaign_cost ) ) * 100, 2 ) : 0;

		return array(
			'recovered_revenue' => $recovered_revenue,
			'campaign_cost'     => floatval( $campaign_cost ),
			'net_profit'        => $net_profit,
			'roi_percentage'    => $roi,
			'recovery_rate'     => $stats['recovery_rate'],
		);
	}

	/**
	 * Export analytics report as CSV.
	 *
	 * @since 1.0.0
	 *
	 * @param string $report_type 'summary', 'carts', 'emails', 'products'.
	 * @param string $date_from   Start date. Optional.
	 * @param string $date_to     End date. Optional.
	 */
	public function export_csv( $report_type = 'summary', $date_from = '', $date_to = '' ) {
		$filename = 'flexi-acr-' . $report_type . '-' . gmdate( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );

		$output = fopen( 'php://output', 'w' );

		switch ( $report_type ) {
			case 'emails':
				$this->export_email_logs_csv( $output, $date_from, $date_to );
				break;
			case 'products':
				$this->export_products_csv( $output, $date_from, $date_to );
				break;
			case 'templates':
				$this->export_templates_csv( $output, $date_from, $date_to );
				break;
			default:
				$this->export_summary_csv( $output, $date_from, $date_to );
				break;
		}

		fclose( $output );
		exit;
	}

	/**
	 * Export summary CSV.
	 *
	 * @param resource $output    File handle.
	 * @param string   $date_from Start date.
	 * @param string   $date_to   End date.
	 */
	private function export_summary_csv( $output, $date_from, $date_to ) {
		$stats = $this->get_summary_stats( $date_from, $date_to );
		fputcsv( $output, array( 'Metric', 'Value' ) );
		foreach ( $stats as $key => $value ) {
			fputcsv( $output, array( ucwords( str_replace( '_', ' ', $key ) ), $value ) );
		}
	}

	/**
	 * Export email logs CSV.
	 *
	 * @param resource $output    File handle.
	 * @param string   $date_from Start date.
	 * @param string   $date_to   End date.
	 */
	private function export_email_logs_csv( $output, $date_from, $date_to ) {
		global $wpdb;

		$date_where = $this->build_date_where( 'send_time', $date_from, $date_to );

		$rows = $wpdb->get_results(
			"SELECT subject, email_from, email_to, send_time, status, template_used, coupon_applied, opened, clicked, purchased
			 FROM {$wpdb->prefix}flexi_email_logs
			 WHERE 1=1 {$date_where}
			 ORDER BY send_time DESC",
			ARRAY_A
		);

		if ( $rows ) {
			fputcsv( $output, array_keys( $rows[0] ) );
			foreach ( $rows as $row ) {
				fputcsv( $output, $row );
			}
		}
	}

	/**
	 * Export top products CSV.
	 *
	 * @param resource $output    File handle.
	 * @param string   $date_from Start date.
	 * @param string   $date_to   End date.
	 */
	private function export_products_csv( $output, $date_from, $date_to ) {
		$products = $this->get_top_abandoned_products( 100, $date_from, $date_to );
		if ( $products ) {
			fputcsv( $output, array( 'Product Name', 'Product ID', 'Abandon Count', 'Total Quantity', 'Total Value' ) );
			foreach ( $products as $product ) {
				fputcsv( $output, array(
					$product['item_name'],
					$product['item_id'],
					$product['abandon_count'],
					$product['total_quantity'],
					$product['total_value'],
				) );
			}
		}
	}

	/**
	 * Export template performance CSV.
	 *
	 * @param resource $output    File handle.
	 * @param string   $date_from Start date.
	 * @param string   $date_to   End date.
	 */
	private function export_templates_csv( $output, $date_from, $date_to ) {
		$templates = $this->get_template_performance( $date_from, $date_to );
		if ( $templates ) {
			fputcsv( $output, array( 'Template Name', 'Total Sent', 'Opened', 'Clicked', 'Purchased', 'Open Rate %', 'Click Rate %' ) );
			foreach ( $templates as $tpl ) {
				fputcsv( $output, array(
					$tpl['template_name'],
					$tpl['total_sent'],
					$tpl['total_opened'],
					$tpl['total_clicked'],
					$tpl['total_purchased'],
					$tpl['open_rate'],
					$tpl['click_rate'],
				) );
			}
		}
	}

	/**
	 * Build a DATE WHERE clause for a given column and date range.
	 *
	 * @param string $column    Column name (with table alias if needed).
	 * @param string $date_from Start date Y-m-d.
	 * @param string $date_to   End date Y-m-d.
	 * @return string SQL snippet (starts with AND).
	 */
	private function build_date_where( $column, $date_from, $date_to ) {
		global $wpdb;
		$clause = '';

		if ( ! empty( $date_from ) ) {
			$clause .= $wpdb->prepare( " AND {$column} >= %s", $date_from . ' 00:00:00' );
		}
		if ( ! empty( $date_to ) ) {
			$clause .= $wpdb->prepare( " AND {$column} <= %s", $date_to . ' 23:59:59' );
		}

		return $clause;
	}

	/**
	 * Get the MySQL DATE_FORMAT string for a given period.
	 *
	 * @param string $period 'daily', 'weekly', or 'monthly'.
	 * @return string DATE_FORMAT string.
	 */
	private function get_group_format( $period ) {
		switch ( $period ) {
			case 'weekly':
				return '%Y-%u';
			case 'monthly':
				return '%Y-%m';
			default:
				return '%Y-%m-%d';
		}
	}
}
