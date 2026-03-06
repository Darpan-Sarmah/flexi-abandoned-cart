<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://test
 * @since      1.0.0
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/public
 * @author     Start and Grow <test@gmailcom>
 */
class Flexi_Abandon_Cart_Recovery_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Flexi_Abandon_Cart_Recovery_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Flexi_Abandon_Cart_Recovery_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/flexi-abandon-cart-recovery-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Flexi_Abandon_Cart_Recovery_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Flexi_Abandon_Cart_Recovery_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/flexi-abandon-cart-recovery-public.js', array( 'jquery' ), $this->version, false );

	}
	/**
	 * Determine whether the plugin is configured to force guest users to log in.
	 *
	 * Developers can override this via the `flexi_allow_guest_checkout` filter.
	 * Return `true` from the filter to allow guest checkout regardless of the setting.
	 *
	 * @since    1.0.0
	 * @return bool True if guest login should be enforced, false otherwise.
	 */
	private function should_force_guest_login() {
		$setting = get_option( 'flexi_force_guest_login', 'off' );
		$force   = 'on' === $setting;

		/**
		 * Filter whether to allow guest checkout.
		 *
		 * Return true to allow guests to browse and purchase without logging in,
		 * overriding the admin setting.
		 *
		 * @since 1.0.0
		 * @param bool $allow_guest True to allow guest checkout, false to enforce login.
		 */
		$allow_guest = apply_filters( 'flexi_allow_guest_checkout', ! $force );

		return ! $allow_guest;
	}

	/**
	 * Modify the price HTML to prompt the user to log in if they are not logged in
	 * and the "Force Guest Users to Login" setting is enabled.
	 *
	 * Usage: This method is hooked to `woocommerce_get_price_html`. It only hides
	 * prices when the `flexi_force_guest_login` option is set to 'on'. Developers
	 * can override this behaviour using the `flexi_allow_guest_checkout` filter.
	 *
	 * @since    1.0.0
	 * @param string $price The HTML price markup.
	 * @return string Modified price HTML or login link if the user is not logged in
	 *                and guest login is enforced.
	 */
	public function flexi_cart_woocommerce_get_price_html( $price ) {
		if ( ! is_user_logged_in() && $this->should_force_guest_login() ) {
			return '<a href="' . get_permalink( wc_get_page_id( 'myaccount' ) ) . '">Login</a> to see prices';
		}

		return $price;
	}

	/**
	 * Determine if the product is purchasable based on user login status.
	 *
	 * Usage: This method is hooked to `woocommerce_is_purchasable`. It only blocks
	 * purchases when the `flexi_force_guest_login` option is set to 'on'. Developers
	 * can override this behaviour using the `flexi_allow_guest_checkout` filter.
	 *
	 * @since    1.0.0
	 * @param bool $is_purchasable Whether the product is purchasable.
	 * @return bool Modified purchasable status based on user login and the guest
	 *              login setting.
	 */
	public function flexi_cart_woocommerce_is_purchasable( $is_purchasable ) {
		if ( ! is_user_logged_in() && $this->should_force_guest_login() ) {
			return false;
		}

		return $is_purchasable;
	}
}
