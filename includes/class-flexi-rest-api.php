<?php
/**
 * REST API Endpoints for Flexi Abandoned Cart Recovery.
 *
 * Registers WP REST API endpoints for external integrations and developers
 * to query abandoned cart data, email logs, analytics, and more.
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
 * Flexi_Rest_Api
 *
 * @since 1.0.0
 */
class Flexi_Rest_Api {

	/**
	 * REST API namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'flexi-acr/v1';

	/**
	 * Constructor – registers routes.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register all REST routes.
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {

		// ── Analytics ──────────────────────────────────────────────────────────

		register_rest_route(
			self::NAMESPACE,
			'/analytics/summary',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_analytics_summary' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => $this->get_date_range_args(),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/analytics/funnel',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_conversion_funnel' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => $this->get_date_range_args(),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/analytics/top-products',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_top_products' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array_merge(
					$this->get_date_range_args(),
					array(
						'limit' => array(
							'default'           => 10,
							'sanitize_callback' => 'absint',
						),
					)
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/analytics/template-performance',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_template_performance' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => $this->get_date_range_args(),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/analytics/roi',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_roi' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array_merge(
					$this->get_date_range_args(),
					array(
						'campaign_cost' => array(
							'default'           => 0,
							'sanitize_callback' => 'floatval',
						),
					)
				),
			)
		);

		// ── Abandoned Carts ────────────────────────────────────────────────────

		register_rest_route(
			self::NAMESPACE,
			'/carts',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_carts' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array_merge(
					$this->get_date_range_args(),
					array(
						'status' => array(
							'default'           => 'abandoned',
							'sanitize_callback' => 'sanitize_text_field',
							'enum'              => array( 'abandoned', 'purchased', 'active', '' ),
						),
						'per_page' => array(
							'default'           => 20,
							'sanitize_callback' => 'absint',
						),
						'page' => array(
							'default'           => 1,
							'sanitize_callback' => 'absint',
						),
					)
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/carts/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_cart' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// ── Email Logs ─────────────────────────────────────────────────────────

		register_rest_route(
			self::NAMESPACE,
			'/email-logs',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_email_logs' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array_merge(
					$this->get_date_range_args(),
					array(
						'per_page' => array(
							'default'           => 20,
							'sanitize_callback' => 'absint',
						),
						'page' => array(
							'default'           => 1,
							'sanitize_callback' => 'absint',
						),
					)
				),
			)
		);

		// ── Settings ───────────────────────────────────────────────────────────

		register_rest_route(
			self::NAMESPACE,
			'/settings',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_settings' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// ── GDPR Unsubscribe (public) ──────────────────────────────────────────

		register_rest_route(
			self::NAMESPACE,
			'/unsubscribe',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'handle_unsubscribe' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'email' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_email',
						'validate_callback' => 'is_email',
					),
					'token' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	// ── Callbacks ──────────────────────────────────────────────────────────────

	/**
	 * GET /analytics/summary
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_analytics_summary( WP_REST_Request $request ) {
		$analytics = $this->get_analytics_instance();
		$data      = $analytics->get_summary_stats(
			$request->get_param( 'date_from' ),
			$request->get_param( 'date_to' )
		);
		return rest_ensure_response( $data );
	}

	/**
	 * GET /analytics/funnel
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_conversion_funnel( WP_REST_Request $request ) {
		$analytics = $this->get_analytics_instance();
		$data      = $analytics->get_conversion_funnel(
			$request->get_param( 'date_from' ),
			$request->get_param( 'date_to' )
		);
		return rest_ensure_response( $data );
	}

	/**
	 * GET /analytics/top-products
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_top_products( WP_REST_Request $request ) {
		$analytics = $this->get_analytics_instance();
		$data      = $analytics->get_top_abandoned_products(
			$request->get_param( 'limit' ),
			$request->get_param( 'date_from' ),
			$request->get_param( 'date_to' )
		);
		return rest_ensure_response( $data );
	}

	/**
	 * GET /analytics/template-performance
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_template_performance( WP_REST_Request $request ) {
		$analytics = $this->get_analytics_instance();
		$data      = $analytics->get_template_performance(
			$request->get_param( 'date_from' ),
			$request->get_param( 'date_to' )
		);
		return rest_ensure_response( $data );
	}

	/**
	 * GET /analytics/roi
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_roi( WP_REST_Request $request ) {
		$analytics = $this->get_analytics_instance();
		$data      = $analytics->get_roi_data(
			$request->get_param( 'campaign_cost' ),
			$request->get_param( 'date_from' ),
			$request->get_param( 'date_to' )
		);
		return rest_ensure_response( $data );
	}

	/**
	 * GET /carts
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_carts( WP_REST_Request $request ) {
		global $wpdb;

		$status   = $request->get_param( 'status' );
		$per_page = $request->get_param( 'per_page' );
		$page     = $request->get_param( 'page' );
		$offset   = ( $page - 1 ) * $per_page;

		$where = '';
		if ( ! empty( $status ) ) {
			$where = $wpdb->prepare( 'WHERE cart_status = %s', $status );
		}

		$items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, user_id, cart_status, created_at, abandon_time, is_expired, is_purchased
				 FROM {$wpdb->prefix}flexi_users_cart_details
				 {$where}
				 ORDER BY created_at DESC
				 LIMIT %d OFFSET %d",
				$per_page,
				$offset
			),
			ARRAY_A
		);

		$total = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}flexi_users_cart_details {$where}" );

		return rest_ensure_response(
			array(
				'items'       => $items ? $items : array(),
				'total'       => $total,
				'per_page'    => $per_page,
				'page'        => $page,
				'total_pages' => (int) ceil( $total / $per_page ),
			)
		);
	}

	/**
	 * GET /carts/{id}
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_cart( WP_REST_Request $request ) {
		global $wpdb;

		$id = absint( $request->get_param( 'id' ) );

		$cart = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}flexi_users_cart_details WHERE id = %d",
				$id
			),
			ARRAY_A
		);

		if ( ! $cart ) {
			return new WP_Error( 'not_found', __( 'Cart not found.', 'flexi-abandon-cart-recovery' ), array( 'status' => 404 ) );
		}

		$items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}flexi_users_cart_items WHERE cart_id = %d",
				$id
			),
			ARRAY_A
		);

		$cart['items'] = $items ? $items : array();

		return rest_ensure_response( $cart );
	}

	/**
	 * GET /email-logs
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_email_logs( WP_REST_Request $request ) {
		global $wpdb;

		$per_page   = $request->get_param( 'per_page' );
		$page       = $request->get_param( 'page' );
		$offset     = ( $page - 1 ) * $per_page;
		$date_from  = $request->get_param( 'date_from' );
		$date_to    = $request->get_param( 'date_to' );
		$date_where = '';

		if ( ! empty( $date_from ) ) {
			$date_where .= $wpdb->prepare( ' AND send_time >= %s', $date_from . ' 00:00:00' );
		}
		if ( ! empty( $date_to ) ) {
			$date_where .= $wpdb->prepare( ' AND send_time <= %s', $date_to . ' 23:59:59' );
		}

		$items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, subject, email_from, email_to, send_time, status, template_used, coupon_applied, opened, clicked, purchased
				 FROM {$wpdb->prefix}flexi_email_logs
				 WHERE 1=1 {$date_where}
				 ORDER BY send_time DESC
				 LIMIT %d OFFSET %d",
				$per_page,
				$offset
			),
			ARRAY_A
		);

		$total = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}flexi_email_logs WHERE 1=1 {$date_where}" );

		return rest_ensure_response(
			array(
				'items'       => $items ? $items : array(),
				'total'       => $total,
				'per_page'    => $per_page,
				'page'        => $page,
				'total_pages' => (int) ceil( $total / $per_page ),
			)
		);
	}

	/**
	 * GET /settings
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_settings( WP_REST_Request $request ) {
		$settings = json_decode( get_option( 'abandon_cart_recovery_plugin_setting', '{}' ), true );
		// Remove sensitive keys before exposing.
		unset( $settings['email_from'], $settings['email_name'] );
		return rest_ensure_response( $settings );
	}

	/**
	 * POST /unsubscribe
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function handle_unsubscribe( WP_REST_Request $request ) {
		$email = $request->get_param( 'email' );
		$token = $request->get_param( 'token' );

		if ( ! $this->verify_unsubscribe_token( $email, $token ) ) {
			return new WP_Error(
				'invalid_token',
				__( 'Invalid or expired unsubscribe token.', 'flexi-abandon-cart-recovery' ),
				array( 'status' => 403 )
			);
		}

		$this->add_to_unsubscribe_list( $email );

		do_action( 'flexi_acr_user_unsubscribed', $email );

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'You have been successfully unsubscribed.', 'flexi-abandon-cart-recovery' ),
			)
		);
	}

	// ── Helpers ───────────────────────────────────────────────────────────────

	/**
	 * Permission callback: only users with manage_options capability.
	 *
	 * @return bool
	 */
	public function check_admin_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Common date range args schema.
	 *
	 * @return array
	 */
	private function get_date_range_args() {
		return array(
			'date_from' => array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'date_to'   => array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}

	/**
	 * Get analytics instance (lazy-loads required file).
	 *
	 * @return Flexi_Cart_Analytics
	 */
	private function get_analytics_instance() {
		if ( ! class_exists( 'Flexi_Cart_Analytics' ) ) {
			require_once FLEXI_ABANDON_CART_RECOVERY_DIR . 'modules/sub-modules/class-flexi-cart-analytics.php';
		}
		return new Flexi_Cart_Analytics();
	}

	/**
	 * Verify an unsubscribe token.
	 *
	 * @param string $email Email address.
	 * @param string $token Token from the email link.
	 * @return bool
	 */
	private function verify_unsubscribe_token( $email, $token ) {
		$expected = hash_hmac( 'sha256', $email, wp_salt( 'auth' ) );
		return hash_equals( $expected, $token );
	}

	/**
	 * Add an email to the plugin unsubscribe list (stored as serialized option).
	 *
	 * @param string $email Email address.
	 */
	private function add_to_unsubscribe_list( $email ) {
		$list   = get_option( 'flexi_acr_unsubscribe_list', array() );
		$list[] = sanitize_email( $email );
		$list   = array_unique( $list );
		update_option( 'flexi_acr_unsubscribe_list', $list );
	}
}
