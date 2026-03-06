<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://test
 * @since      1.0.0
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/includes
 * @author     Start and Grow <test@gmailcom>
 */
class Flexi_Abandon_Cart_Recovery {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Flexi_Abandon_Cart_Recovery_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'FLEXI_ABANDON_CART_RECOVERY_VERSION' ) ) {
			$this->version = FLEXI_ABANDON_CART_RECOVERY_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'flexi-abandon-cart-recovery';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Flexi_Abandon_Cart_Recovery_Loader. Orchestrates the hooks of the plugin.
	 * - Flexi_Abandon_Cart_Recovery_i18n. Defines internationalization functionality.
	 * - Flexi_Abandon_Cart_Recovery_Admin. Defines all hooks for the admin area.
	 * - Flexi_Abandon_Cart_Recovery_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-flexi-abandon-cart-recovery-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-flexi-abandon-cart-recovery-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-flexi-abandon-cart-recovery-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-flexi-abandon-cart-recovery-public.php';

		$this->loader = new Flexi_Abandon_Cart_Recovery_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Flexi_Abandon_Cart_Recovery_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Flexi_Abandon_Cart_Recovery_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Flexi_Abandon_Cart_Recovery_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'flexi_acr_admin_menu', 22 );
		$this->loader->add_action( 'wp_ajax_flexi_save_trigger_data', $plugin_admin, 'flexi_save_trigger_data');
		$this->loader->add_action('wp_ajax_woocommerce_get_all_products',$plugin_admin, 'flexi_woocommerce_get_all_products');
		$this->loader->add_action('wp_ajax_nopriv_woocommerce_get_all_products',$plugin_admin, 'flexi_woocommerce_get_all_products');
		$this->loader->add_action('wp_ajax_woocommerce_get_all_categories',$plugin_admin, 'flexi_woocommerce_get_all_categories');
		$this->loader->add_action('wp_ajax_nopriv_woocommerce_get_all_categories',$plugin_admin, 'flexi_woocommerce_get_all_categories');

		$this->loader->add_action('wp_ajax_woocommerce_get_all_roles',$plugin_admin, 'flexi_woocommerce_get_all_user_roles');
		$this->loader->add_action('wp_ajax_nopriv_woocommerce_get_all_roles',$plugin_admin, 'flexi_woocommerce_get_all_user_roles');

		$this->loader->add_action('wp_ajax_flexi_send_test_mail',$plugin_admin, 'flexi_send_test_mail');
		$this->loader->add_action('wp_ajax_nopriv_flexi_send_test_mail',$plugin_admin, 'flexi_send_test_mail');

		$this->loader->add_action('wp_ajax_flexi_send_report_over_mail',$plugin_admin, 'flexi_send_report_over_mail');
		$this->loader->add_action('wp_ajax_nopriv_flexi_send_report_over_mail',$plugin_admin, 'flexi_send_report_over_mail');
		
		$this->loader->add_action( 'woocommerce_add_to_cart', $plugin_admin, 'flexi_capture_abandoned_cart_data', 10, 1 );
		$this->loader->add_action( 'woocommerce_after_cart_item_quantity_update', $plugin_admin, 'flexi_capture_abandoned_cart_data', 10, 1 );
		$this->loader->add_action( 'woocommerce_cart_item_restored', $plugin_admin, 'flexi_capture_abandoned_cart_data', 10, 1 );
		$this->loader->add_action( 'woocommerce_single_add_to_cart', $plugin_admin, 'flexi_capture_abandoned_cart_data', 10, 1 );
		$this->loader->add_action( 'woocommerce_cart_item_removed', $plugin_admin, 'flexi_capture_abandoned_cart_data', 10, 1 );
		$this->loader->add_action( 'flexi_check_for_abandon_carts_event', $plugin_admin, 'flexi_check_for_abandoned_carts', 10, 1 );

		$this->loader->add_action( 'admin_post_nopriv_track_email_open', $plugin_admin, 'track_email_open' );
		$this->loader->add_action( 'admin_post_track_email_open', $plugin_admin, 'track_email_open' );
		// $this->loader->add_action( 'wp_ajax_nopriv_track_link_click', $plugin_admin, 'mark_purchase_completed' );
		// $this->loader->add_action( 'wp_ajax_track_link_click', $plugin_admin, 'mark_purchase_completed' );
		$this->loader->add_action( 'init', $plugin_admin, 'track_link_click' );
		$this->loader->add_action('woocommerce_before_calculate_totals', $plugin_admin, 'track_link_click');

		$this->loader->add_action( 'woocommerce_thankyou', $plugin_admin, 'track_purchase', 1 );

		$this->loader->add_filter( 'cron_schedules', $plugin_admin, 'flexi_add_custom_scheduler' );
		$this->loader->add_filter( 'init', $plugin_admin, 'flexi_coupon_cart_expiry_scheduler' );
		$this->loader->add_filter( 'flexi_check_cart_expiry', $plugin_admin, 'mark_flexi_cart_expiry');
		$this->loader->add_filter( 'flexi_check_coupon_expiry', $plugin_admin, 'mark_flexi_coupons_expiry');
		

		// testing ajax
		$this->loader->add_action('wp_ajax_flexi_get_global_setting',$plugin_admin, 'mark_flexi_coupons_expiry');
		$this->loader->add_action('wp_ajax_nopriv_flexi_get_global_setting',$plugin_admin, 'mark_flexi_coupons_expiry');
		
		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		if ( isset( $page ) && 'flexi-cart-recovery-settings' === $page ) {

			$this->loader->add_filter( 'mce_buttons', $plugin_admin, 'flexi_tinymce_admin_btn' );
			$this->loader->add_filter( 'mce_external_plugins', $plugin_admin, 'flexi_admin_filter_mce_plugin' );
		}

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Flexi_Abandon_Cart_Recovery_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_filter( 'woocommerce_is_purchasable', $plugin_public, 'flexi_cart_woocommerce_is_purchasable', 10, 2 );
		$this->loader->add_filter( 'woocommerce_get_price_html', $plugin_public, 'flexi_cart_woocommerce_get_price_html', 10, 2 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Flexi_Abandon_Cart_Recovery_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
