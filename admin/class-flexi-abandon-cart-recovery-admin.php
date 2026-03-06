<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://test
 * @since      1.0.0
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/admin
 * @author     Start and Grow <test@gmailcom>
 */

class Flexi_Abandon_Cart_Recovery_Admin {


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
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
         ini_set( 'display_errors', 1 );
        ini_set( 'display_startup_errors', 1 );
        error_reporting( E_ALL );

        $this->plugin_name = $plugin_name;
        $this->version     = $version;
        include_once FLEXI_ABANDON_CART_RECOVERY_DIR . 'admin/partials/class-flexi-database-queries.php';
    }

    /**
     * Register the stylesheets for the admin area.
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

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/flexi-abandon-cart-recovery-admin.css', array(), $this->version, 'all' );

        $page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
        if ( isset( $page ) && ( ( 'flexi-cart-recovery-settings' === $page ) || 'flexi-abandon-cart-recovery-coupons' === $page ) ) {
            // Enqueue Select2 CSS
            wp_register_style( 'select2-css', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all' );
            wp_enqueue_style( 'select2-css' );
        }

    }

    /**
     * Register the JavaScript for the admin area.
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

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/flexi-abandon-cart-recovery-admin.js', array( 'jquery' ), $this->version, false );

        wp_localize_script(
            $this->plugin_name, // Handle used in wp_enqueue_script.
            'flexi_abandon_cart_recovery_obj', // JavaScript object name.
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )
        );
        /**
         * Including custo tinymce js.
         */

        $page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
        if ( isset( $page ) && 'flexi-cart-recovery-settings' === $page ) {
            wp_enqueue_script(
                'flexi_custom_tinymce_shortcode',
                plugin_dir_url( __FILE__ ) . 'js/flexi_custom_tinymce_shortcode.js',
                array( 'jquery' ),
                '1.0.0',
                true
            );
            wp_localize_script(
                'flexi_custom_tinymce_shortcode', // Handle used in wp_enqueue_script.
                'flexi_custom_tinymce_shortcode_obj', // JavaScript object name.
                array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )
            );
        }
        if ( isset( $page ) && ( 'flexi-abandon-cart-recovery-coupons' === $page || 'flexi-cart-recovery-settings' === $page ) ) {
            // Registering Select2 js.
            wp_register_script( 'select2', plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array( 'jquery' ), $this->version );
            wp_enqueue_script( 'select2' );

        }
    }

    public function flexi_acr_admin_menu() {
        add_menu_page(
            __( 'Flexi Abandoned Cart Recovery', 'flexi-abandon-cart-recovery' ),
            __( 'Flexi Abandoned Cart Recovery', 'flexi-abandon-cart-recovery' ),
            'manage_options',
            'flexi-abandon-cart-recovery',
            array( $this, 'flexi_abandon_cart_recovery' ),
            'dashicons-cart',
            50
		);
        // Add submenu page for Dashboard.
        add_submenu_page(
            'flexi-abandon-cart-recovery',
            __( 'Dashboard', 'flexi-abandon-cart-recovery' ),
            __( 'Dashboard', 'flexi-abandon-cart-recovery' ),
            'manage_options',
            'flexi-abandon-cart-recovery',
            array( $this, 'flexi_abandon_cart_recovery' ),
        );

        // Add submenu page for Coupon.
        add_submenu_page(
            'flexi-abandon-cart-recovery',
            __( 'Coupon', 'flexi-abandon-cart-recovery' ),
            __( 'Coupon', 'flexi-abandon-cart-recovery' ),
            'manage_options',
            'flexi-abandon-cart-recovery-coupons',
            array( $this, 'flexi_coupon_page' )
        );

        // Add submenu page for Settings.
        add_submenu_page(
            'flexi-abandon-cart-recovery',
            __( 'Settings', 'flexi-abandon-cart-recovery' ),
            __( 'Settings', 'flexi-abandon-cart-recovery' ),
            'manage_options',
            'flexi-cart-recovery-settings',
            array( $this, 'flexi_settings_page' )
        );
    }

    /**
     * Displays the Abandoned Cart Recovery dashboard page.
     *
     * This method includes the PHP file responsible for rendering
     * the dashboard content of the plugin's admin page.
     *
     * @since 1.0.0
     * @access public
     */
    public function flexi_abandon_cart_recovery() {
         $file = FLEXI_ABANDON_CART_RECOVERY_DIR . '/modules/flexi-abandon-cart-dashboard.php';
        if ( file_exists( $file ) ) {
            include $file;
        }
    }

    /**
     * Displays the Abandoned Cart Recovery Coupons page.
     *
     * This method includes the PHP file responsible for rendering
     * the coupons content of the plugin's admin page.
     *
     * @since 1.0.0
     * @access public
     */
    public function flexi_coupon_page() {
         include FLEXI_ABANDON_CART_RECOVERY_DIR . '/admin/partials/flexi-coupons-header-tabs.php';
        $section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'create-coupon';

        switch ( $section ) {
            case 'create-coupon':
            default:
                include FLEXI_ABANDON_CART_RECOVERY_DIR . '/modules/flexi-create-dynamic-coupons.php';
                break;
            case 'coupons':
                include FLEXI_ABANDON_CART_RECOVERY_DIR . '/modules/flexi-cart-dynamic-coupons.php';
                break;

        }

    }

    /**
     * Displays the Abandoned Cart Recovery settings page.
     *
     * This method includes the header tabs file and renders the appropriate settings
     * section based on the provided query parameter.
     *
     * @since 1.0.0
     * @access public
     */
    public function flexi_settings_page() {
         include FLEXI_ABANDON_CART_RECOVERY_DIR . '/admin/partials/flexi-header-tabs.php';

        $section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'settings-view';

        switch ( $section ) {
            case 'carts-list':
                include FLEXI_ABANDON_CART_RECOVERY_DIR . '/modules/class-flexi-user-cart-details.php';
                break;
            case 'email-settings':
                include FLEXI_ABANDON_CART_RECOVERY_DIR . '/modules/flexi-cart-email-settings.php';
                break;
            case 'logs-view':
                include FLEXI_ABANDON_CART_RECOVERY_DIR . '/modules/flexi-cart-logs-details.php';
                break;
            case 'admin-setting':
                include FLEXI_ABANDON_CART_RECOVERY_DIR . '/modules/flexi-cart-admin-settings.php';
                break;
            case 'settings-view':
            default:
                include FLEXI_ABANDON_CART_RECOVERY_DIR . '/modules/flexi-cart-global-settings.php';
                break;

        }
    }
    /**
     * Adds a custom button to the TinyMCE editor.
     *
     * This method appends a custom button to the array of TinyMCE buttons,
     * allowing it to be displayed in the editor toolbar.
     *
     * @param array $admin_btn The array of existing TinyMCE buttons.
     * @return array The modified array of TinyMCE buttons.
     */
    public function flexi_tinymce_admin_btn( $buttons ) {
         array_push( $buttons, 'flexi_admin_shortcodes' );
        return $buttons;
    }

    /**
     * Registers a custom TinyMCE plugin.
     *
     * This method adds a custom TinyMCE plugin to the editor,
     * specifying the path to the JavaScript file that defines the plugin's functionality.
     *
     * @param array $plugins The array of existing TinyMCE plugins.
     * @return array The modified array of TinyMCE plugins.
     */
    public function flexi_admin_filter_mce_plugin( $plugins ) {
         $plugins['flexi_admin_shortcodes'] = FLEXI_ABANDON_CART_RECOVERY_URL . 'admin/js/flexi_custom_tinymce_shortcode.js';
        return $plugins;
    }

    public function flexi_save_trigger_data() {
         $abandoned_carts_queries = new Flexi_Database_Queries();
        $sanitized_array          = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
        $form_data                = isset( $sanitized_array['trigger_formdata'] ) ? ( $sanitized_array['trigger_formdata'] ) : '';
        parse_str( $form_data, $form_data_array );

        $trigger_id      = isset( $form_data_array['trigger_id'] ) ? sanitize_text_field( wp_unslash( $form_data_array['trigger_id'] ) ) : '';
        $status          = isset( $form_data_array['status'] ) ? 1 : 0;
        $trigger_name    = isset( $form_data_array['trigger_name'] ) ? sanitize_text_field( wp_unslash( $form_data_array['trigger_name'] ) ) : '';
        $template_name   = isset( $form_data_array['template_name'] ) ? sanitize_text_field( wp_unslash( $form_data_array['template_name'] ) ) : '';
        $rule_name       = isset( $form_data_array['rule_name'] ) ? sanitize_text_field( wp_unslash( $form_data_array['rule_name'] ) ) : '';
        $send_after_num  = isset( $form_data_array['send_after_num'] ) ? sanitize_text_field( wp_unslash( $form_data_array['send_after_num'] ) ) : '';
        $send_after_span = isset( $form_data_array['send_after_span'] ) ? sanitize_text_field( wp_unslash( $form_data_array['send_after_span'] ) ) : '';

        $parms = array(
            'trigger_name'      => $trigger_name,
            'trigger_temp_name' => $template_name,
            'trigger_rule'      => $rule_name,
            'status'            => $status,
            'send_after_num'    => $send_after_num,
            'send_after_span'   => $send_after_span,
        );

        $existing_trigger = $abandoned_carts_queries->select_db_query( 'flexi_email_triggers', 'id ', 'id =' . $trigger_id );
        if ( $existing_trigger[0]['id'] ) {

            $where         = 'id = ' . $trigger_id;
            $update_result = $abandoned_carts_queries->update_db_query( 'flexi_email_triggers', $parms, $where );
            if ( 1 === $update_result || 'true' === $update_result ) {
                wp_send_json_success( 'Record Updated Sucessfully !' );
            } else {
                wp_send_json_error( 'Error while updating record !' );
            }
        } else {
            $insert_result = $abandoned_carts_queries->insert_db_query( 'flexi_email_triggers', $parms );
            if ( 1 === $insert_result || 'true' === $insert_result ) {
                wp_send_json_success( 'Record Entered Sucessfully !' );
            } else {
                wp_send_json_error( 'Error while entering record !' );
            }
        }
    }

    public function flexi_woocommerce_get_all_products() {
		if ( !class_exists( 'WooCommerce' ) ) {
            wp_send_json_error( 'WooCommerce not found' );
		}

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1, // Get all products
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        );

        $loop     = new WP_Query( $args );
        $products = array();

        if ( $loop->have_posts() ) {
            while ( $loop->have_posts() ) {
                $loop->the_post();
                $product = wc_get_product( get_the_ID() );
                if ( $product ) {
                    $products[] = array(
                        'id'   => $product->get_id(),
                        'text' => $product->get_id() . ' - ' . $product->get_name(), // ID and product name
                    );
                }
            }
        }

        wp_reset_postdata();
        wp_send_json( $products );
    }

    public function flexi_woocommerce_get_all_categories() {
        $categories = get_terms(
            array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
            )
		);

        $category_options = array();
        foreach ( $categories as $category ) {
            $category_options[] = array(
                'id'   => $category->term_id,
                'text' => $category->name,
            );
        }

        wp_send_json( $category_options );
    }

    public function flexi_woocommerce_get_all_user_roles() {
         global $wp_roles;
        $roles = $wp_roles->roles;

        $role_options = array();
        foreach ( $roles as $role_key => $role ) {
            $role_options[] = array(
                'id'   => $role_key,
                'text' => $role['name'],
            );
        }

        wp_send_json( $role_options );
    }

	// Function to handle the test email sending
    public function flexi_send_test_mail() {
		if ( isset( $_POST['send_to'] ) && isset( $_POST['email_subject'] ) && isset( $_POST['email_body'] ) ) {
            $send_to       = sanitize_email( $_POST['send_to'] );
            $email_subject = sanitize_text_field( $_POST['email_subject'] );
            $email_body    = wp_kses_post( $_POST['email_body'] );

            if ( !is_email( $send_to ) ) {
                wp_send_json_error( 'Invalid email address.' );
                wp_die();
            }
            $parms     = array(
				'to'      => $send_to,
				'subject' => $email_subject,
				'message' => $email_body,
            );
            $headers   = array( 'Content-Type: text/html; charset=UTF-8' );
            $mail_sent = $this->send_mail_through_wp_mail( $headers, $parms );
            if ( $mail_sent ) {
                wp_send_json_success( 'Test mail sent successfully.' );
            } else {
                wp_send_json_error( 'Failed to send test mail.' );
            }
		} else {
			wp_send_json_error( 'Missing required parameters.' );
		}
        wp_die();
    }

    public function send_mail_through_wp_mail( $headers, $parms ) {
        $send_to       = $parms['to'];
        $email_subject = $parms['subject'];
        $email_body    = $parms['message'];

        $mail_sent = wp_mail( $send_to, $email_subject, $email_body, $headers );
        return $mail_sent;
    }

    public function flexi_send_report_over_mail() {
		if ( !isset( $_POST['send_to'] ) || !is_email( $_POST['send_to'] ) ) {
            wp_send_json_error( 'Invalid email address.' );
            return;
		}

        $send_to          = sanitize_email( $_POST['send_to'] );
        $email_subject    = sanitize_text_field( $_POST['email_subject'] );
        $selected_columns = isset( $_POST['selected_columns'] ) ? array_map( 'sanitize_text_field', $_POST['selected_columns'] ) : array();

		if ( empty( $selected_columns ) ) {
			wp_send_json_error( 'No columns selected.' );
			return;
		}

        // Generate the CSV file and get the file path
        $file_path = $this->flexi_generate_csv_report( $selected_columns );

		if ( !$file_path || !file_exists( $file_path ) ) {
			wp_send_json_error( 'Error generating CSV report.' );
			return;
		}

        // Email content and attachment
        $headers       = array( 'Content-Type: text/html; charset=UTF-8' );
        $email_message = 'Please find the attached report below.';
        $attachments   = array( $file_path );

        $mail_sent = wp_mail( $send_to, $email_subject, $email_message, $headers, $attachments );

        unlink( $file_path );

		if ( $mail_sent ) {
			wp_send_json_success( 'Report sent successfully.' );
		} else {
			wp_send_json_error( 'Failed to send the report.' );
		}

        wp_die();
    }

    public function flexi_generate_csv_report( $selected_columns ) {
         $upload_dir = wp_upload_dir();
        $file_path   = $upload_dir['basedir'] . '/flexi-abandon-cart-report.csv';

        $file = fopen( $file_path, 'w' );
        if ( !$file ) {
            return false;
        }
        fputcsv( $file, $selected_columns );
        $data = $this->retrieve_data_from_database();

        foreach ( $data as $rowData ) {
            fputcsv( $file, $rowData );
        }

        fclose( $file );

        return $file_path;
    }

    private function retrieve_data_from_database() {
         // Mock data, replace with your actual database queries
        return array(
            array( 'Product A', 100, 'Category 1' ),
            array( 'Product B', 150, 'Category 2' ),
            array( 'Product C', 200, 'Category 3' ),
		);
    }

    private function flexi_get_global_setting() {
         $abandoned_cart_setting = json_decode( get_option( 'flexi_abandon_cart_plugin_global_setting' ), true );
        if ( is_array( $abandoned_cart_setting ) ) {
            return $abandoned_cart_setting;
        }

        return false;
    }

    // abandon cart capture
    public function flexi_capture_abandoned_cart_data( $cart_item_key ) {
         $abandoned_cart_setting = $this->flexi_get_global_setting();
        if ( !( isset( $abandoned_cart_setting['enable_tracking'] ) && 'on' === $abandoned_cart_setting['enable_tracking'] ) ) {
            return false;
        }

        $user_id      = get_current_user_id();
        $current_time = gmdate( 'Y-m-d H:i:s' );
        if ( $user_id ) {
            // Fetch user information
            $user_meta  = get_userdata( $user_id );
            $user_email = $user_meta->user_email;

            if ( ( isset( $abandoned_cart_setting['capture_valid_email'] ) && 'on' === $abandoned_cart_setting['capture_valid_email'] ) ) {
                if ( !filter_var( $user_email, FILTER_VALIDATE_EMAIL ) ) {
                    return false;
                }
            }

            $user_details = array(
                'user_id'          => $user_id,
                'user_email'       => $user_email,
                'user_displayname' => $user_meta->display_name,
                'user_nicename'    => $user_meta->user_nicename,
                'user_firstname'   => $user_meta->first_name,
                'user_lastname'    => $user_meta->last_name,
                'user_role'        => $user_meta->roles,
            );

            $cart = WC()->cart;
            // Check if cart is empty
            if ( $cart->is_empty() ) {
                $this->flexi_handle_empty_cart( $user_id, $current_time );
            } else {
                $cart_items         = $cart->get_cart_contents();
                $cart_items_details = $this->flexi_get_cart_details( $cart_items );
                $this->flexi_save_cart_n_user_data( $cart_item_key, $user_id, $cart_items_details, $user_details, $current_time );
            }
            $abandon_check_cart_track = $this->calculate_abandon_check_duration( $abandoned_cart_setting, 'abandon_time_check' );

            // wp_clear_scheduled_hook( 'flexi_check_for_abandon_carts_event', array( $user_id ) );
            // wp_clear_scheduled_hook( 'acr_woocom_resend_email', array( $user_id ) );

            $this->flexi_check_for_abandoned_carts( $user_id );
            // wp_schedule_single_event(time() + $abandon_check_cart_track, 'flexi_check_for_abandon_carts_event', array($user_id));
        }
    }

    private function calculate_abandon_check_duration( $settings, $caluate_duration_for ) {
		if ( 'abandon_time_check' === $caluate_duration_for ) {
            if ( 'minutes' === strtolower( $settings['cart_abandon_time_duration'] ) ) {
                return $settings['cart_abandon_time'] * 60;
            } elseif ( 'hours' === strtolower( $settings['cart_abandon_time_duration'] ) ) {
                return $settings['cart_abandon_time'] * 3600;
            } else {
                return $settings['cart_abandon_time']; // Default 1 second for testing.
            }
		} elseif ( 'resend_after_interval' === $caluate_duration_for ) {

			$resend_email_after = isset( $settings['resend_email_after'] ) ? $settings['resend_email_after'] : 10;
			$interval_unit      = isset( $settings['resend_email_after_duration'] ) ? strtolower( $settings['resend_email_after_duration'] ) : 'minutes';

			if ( 'minutes' === $interval_unit ) {
				$interval = 60;
			} elseif ( 'hours' === $interval_unit ) {
				$interval = 3600;
			} elseif ( 'seconds' === $interval_unit ) {
				$interval = 1;
			}
			$resend_time_interval = $resend_email_after * $interval;

			return $resend_time_interval;
		}
    }

    private function flexi_handle_empty_cart( $user_id, $current_time ) {
         $new_status = 'removed';
        $parms       = array(
            'cart_status'         => $new_status,
            'abandon_time'        => $current_time,
            'last_interaction_at' => null,
        );
        $where       = 'user_id =' . $user_id . ' AND is_purchased = 0';

        // Update the cart status to 'removed'
        $abandoned_carts_queries = new Flexi_Database_Queries();
        $update_cart             = $abandoned_carts_queries->update_db_query( 'flexi_users_cart_details', $parms, $where );

    }

    private function flexi_get_cart_details( $cart_items ) {
         $total_sub_total     = 0;
        $total_cost           = 0;
        $processed_cart_items = array();

        foreach ( $cart_items as $acr_keys => $cart_item ) {

            $product_id      = $cart_item['product_id'];
            $variation_id    = isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0;
            $product_name    = $cart_item['data']->get_name();
            $quantity        = $cart_item['quantity'];
            $total_cost_item = isset( $cart_item['line_total'] ) ? $cart_item['line_total'] : $cart_item['data']->get_regular_price();
            $sub_total_item  = isset( $cart_item['line_subtotal'] ) ? $cart_item['line_subtotal'] : $cart_item['data']->get_sale_price();

            $total_sub_total += (float) $sub_total_item;
            $total_cost      += (float) $total_cost_item;

            $price                  = '' !== $sub_total_item ? $sub_total_item : $total_cost;
            $processed_cart_items[] = array(
                'product_id'   => $product_id,
                'product_name' => $product_name,
                'variation_id' => $variation_id,
                'quantity'     => $quantity,
                'price'        => round( $price, 2 ),
            );
        }

        return $processed_cart_items;
    }

    private function flexi_save_cart_n_user_data( $cart_item_key, $user_id, $cart_items_details, $user_details, $current_time ) {
         $abandoned_carts_queries = new Flexi_Database_Queries();
        $existing_cart            = $abandoned_carts_queries->select_db_query( 'flexi_users_cart_details', 'id, cart_status', 'user_id = ' . $user_id );
        $user_parms               = array( 'user_information' => wp_json_encode( $user_details ) );

        $existing_user = $abandoned_carts_queries->select_db_query( 'flexi_abandon_cart_users', 'id', 'id = ' . $user_id );

        if ( !empty( $existing_user ) ) {
            $abandoned_carts_queries->update_db_query( 'flexi_abandon_cart_users', $user_parms, 'id = ' . $user_id );
        } else {
            $user_parms['id'] = $user_id;
            $abandoned_carts_queries->insert_db_query( 'flexi_abandon_cart_users', $user_parms );
        }
        if ( !empty( $existing_cart ) && 'purchased' !== $existing_cart[0]['cart_status'] ) {
            // Update existing cart if not purchased
            $cart_id       = $existing_cart[0]['id'];
            $update_params = array(
                'cart_status' => 'active',
                'created_at'  => $current_time,
                'is_hidden'   => 0,
            );
            $abandoned_carts_queries->update_db_query( 'flexi_users_cart_details', $update_params, 'user_id = ' . $user_id );

            $current_item_ids = array();
            foreach ( $cart_items_details as $item ) {
                $item_id       = isset( $item['product_id'] ) && $item['product_id'] ? $item['product_id'] : $item['variation_id'];
                $cart_items    = array(
                    'cart_id'   => $cart_id,
                    'item_id'   => $item_id,
                    'item_name' => $item['product_name'],
                    'quantity'  => $item['quantity'],
                    'price'     => $item['price'],
                );
                $existing_item = $abandoned_carts_queries->select_db_query( 'flexi_users_cart_items', 'id', 'cart_id = ' . $cart_id . ' AND item_id = ' . $item_id );
                if ( !empty( $existing_item ) ) {
                    // Update existing item
                    $abandoned_carts_queries->update_db_query( 'flexi_users_cart_items', $cart_items, 'id = ' . $existing_item[0]['id'] );
                } else {
                    // Insert new item
                    $abandoned_carts_queries->insert_db_query( 'flexi_users_cart_items', $cart_items );
                }
                $current_item_ids[] = $item_id;
            }
            $this->flexi_delete_removed_items( $cart_id, $current_item_ids );
            $abandoned_carts_queries->update_db_query( 'flexi_abandon_cart_users', $user_parms, 'id = ' . $user_id );
        } else {

            $insert_params = array(
                'user_id'      => $user_id,
                'cart_status'  => 'active',
                'created_at'   => $current_time,
                'is_hidden'    => 0,
                'is_expired'   => 0,
                'is_purchased' => 0,
            );
            $cart_id       = $abandoned_carts_queries->insert_db_query( 'flexi_users_cart_details', $insert_params );

            foreach ( $cart_items_details as $item ) {
                $cart_items = array(
                    'cart_id'   => $cart_id,
                    'item_id'   => isset( $item['product_id'] ) && $item['product_id'] ? $item['product_id'] : $item['variation_id'],
                    'item_name' => $item['product_name'],
                    'quantity'  => $item['quantity'],
                    'price'     => $item['price'],
                );
                $abandoned_carts_queries->insert_db_query( 'flexi_users_cart_items', $cart_items );
            }
        }
    }

    // Function to remove items that are no longer in the current cart
    private function flexi_delete_removed_items( $cart_id, $current_item_ids ) {
         $abandoned_carts_queries = new Flexi_Database_Queries();
        $existing_items           = $abandoned_carts_queries->select_db_query( 'flexi_users_cart_items', 'id, item_id', 'cart_id = ' . $cart_id );

        $existing_item_ids = array_column( $existing_items, 'item_id' );
        $items_to_remove   = array_diff( $existing_item_ids, $current_item_ids );
        foreach ( $items_to_remove as $item_id ) {
            $where = array( 'cart_id' => $cart_id );
            $and   = array( 'item_id' => $item_id );
            $abandoned_carts_queries->delete_db_query( 'flexi_users_cart_items', $where, $and );
        }
    }

    public function flexi_check_for_abandoned_carts( $user_id ) {
         $abandoned_cart_setting = $this->flexi_get_global_setting();
        if ( !( isset( $abandoned_cart_setting['enable_tracking'] ) && 'on' === $abandoned_cart_setting['enable_tracking'] ) ) {
            return false;
        }
        // $abandon_check_cart_track = $abandoned_cart_setting['cart_abandon_time'];
        // $resnd_mail_after         = $abandoned_cart_setting['resend_email_after'];
        $abandoned_carts_queries = new Flexi_Database_Queries();
        $parms                   = array( 'id', 'cart_status', 'created_at', 'abandon_time', 'last_interaction_at' );
        $results                 = $abandoned_carts_queries->select_db_query( 'flexi_users_cart_details', $parms, 'cart_status = "active"', 'user_id =' . $user_id . '' );

        $current_time = gmdate( 'Y-m-d H:i:s' );

        foreach ( $results as $result ) {
            $time_log        = $result['created_at'];
            $time_difference = time() - strtotime( $time_log );
            // if ($time_difference >= $abandon_check_cart_track) {

            $new_status = empty( $result['cart_status'] ) ? 'removed' : 'abandoned';
            $parms      = array(
                'cart_status'  => $new_status,
                'abandon_time' => $current_time,
            );
            $where      = 'id = ' . $result['id'] . '';

            $abandoned_carts_queries->update_db_query( 'flexi_users_cart_details', $parms, $where );
            if ( 'abandoned' === $new_status ) {
                $first_mail = $this->enable_send_mail_for_abandon_carts( $user_id );
            }
            // }
        }
    }

    public function enable_send_mail_for_abandon_carts( $user_id ) {
         $abandoned_cart_setting = $this->flexi_get_global_setting();
        if ( !( isset( $abandoned_cart_setting['enable_tracking'] ) && 'on' === $abandoned_cart_setting['enable_tracking'] ) ) {
            return false;
        }
        $users_n_cart_data = $this->get_abandoned_cart_users_and_details( $user_id ); // Get user who have abandoned carts.

        $user_info    = json_decode( $users_n_cart_data['user_details']['user_information'], true );
        $cart_details = $users_n_cart_data['user_cart_details'];
        $mail_sent_at = strtotime( $users_n_cart_data['last_interaction_at'] );

        $resend_interval = $this->calculate_abandon_check_duration( $abandoned_cart_setting, 'resend_after_interval' );
        $current_time    = time();

        $from_email = isset( $abandoned_cart_setting['email_from'] ) ? $abandoned_cart_setting['email_from'] : '';
        $useremail  = $user_info['user_email'];

        if ( 0 === $mail_sent_at || empty( $mail_sent_at ) ) {
            $enable_email_temps = $this->get_template_type_and_data( 'first', $user_id, $user_info, $cart_details );

            if ( isset( $enable_email_temps ) && '' !== $enable_email_temps ) {
                $email_body       = $enable_email_temps['message'];
                $subject          = $enable_email_temps['subject'];
                $simple_mail_body = wp_strip_all_tags( $enable_email_temps['simple_mail_body'] );

                $headers  = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                $headers .= "From: $from_email\r\n";

                $mail_sent = wp_mail( $useremail, $subject, $email_body, $headers );

                if ( $mail_sent ) {
                    $this->flexi_email_logs_activity( $user_id, $subject, $from_email, $useremail, 'success', $simple_mail_body, $enable_email_temps['temp_name'] );

                } else {
                    $error_message = error_get_last()['message'];
                    $this->flexi_email_logs_activity( $user_id, $subject, $from_email, $useremail, 'error', $error_message, $enable_email_temps['temp_name'] );
                }
            }
            // } elseif (($current_time - $mail_sent_at) >= $resend_interval) {
        } else {

            // echo "---second--";

            $enable_email_temps = $this->get_template_type_and_data( 'second', $user_id, $user_info, $cart_details );
            // echo "<pre>";
            // print_r($enable_email_temps);
            // die;
            if ( isset( $enable_email_temps ) && '' !== $enable_email_temps ) {
                $email_body       = $enable_email_temps['message'];
                $subject          = $enable_email_temps['subject'];
                $simple_mail_body = wp_strip_all_tags( $enable_email_temps['simple_mail_body'] );

                $headers  = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                $headers .= "From: $from_email\r\n";

                $mail_sent = wp_mail( $useremail, $subject, $email_body, $headers );

                if ( $mail_sent ) {
                    $this->flexi_email_logs_activity( $user_id, $subject, $from_email, $useremail, 'success', $simple_mail_body, $enable_email_temps['temp_name'] );

                } else {
                    $error_message = error_get_last()['message'];
                    $this->flexi_email_logs_activity( $user_id, $subject, $from_email, $useremail, 'error', $error_message, $enable_email_temps['temp_name'] );
                }
            }
        }
    }

    private function get_template_type_and_data( $number, $user_id, $user_info, $cart_details ) {
         $language               = get_option( 'aban_cart_rec_language' );
        $abandoned_carts_queries = new Flexi_Database_Queries();
        $form_data               = $abandoned_carts_queries->select_db_query( 'flexi_email_templates', '*', 'temp_language = "' . $language . '"', 'status = "on"' );

        $tracking_pixel_url = add_query_arg(
            array(
                'action'  => 'track_email_open',
                'user_id' => $user_id,
            ),
            admin_url( 'admin-post.php' )
        );

        $tracking_link_url = add_query_arg(
            array(
                'action'  => 'track_link_click',
                'user_id' => $user_id,
            ),
            wc_get_checkout_url()
        );

        if ( isset( $form_data ) ) {
            foreach ( $form_data as $data ) {
                // cart recovery mail
                if ( '1' === $data['template_type'] && 'first' === $number ) {
                    $simple_email = $this->flexi_send_customer_mail( $data, $user_info, $tracking_link_url, $tracking_pixel_url, $cart_details );
                    return $simple_email;
                }
                if ( '0' === $data['template_type'] && 'second' === $number ) {
                    if ( 'on' === $data['coupon_status'] ) {
                        $get_coupon_details = $this->flexi_coupon_is_executeable( $data['coupon_type'], $data['coupon_name'], $user_info, $cart_details );

                        if ( false === $get_coupon_details ) {
                            foreach ( $form_data as $fallback_data ) {
                                if ( '1' === $fallback_data['template_type'] ) {

                                    $simple_email = $this->flexi_send_customer_mail( $fallback_data, $user_info, $tracking_link_url, $tracking_pixel_url, $cart_details );
                                    return $simple_email;
                                }
                            }
                        } else {
                            $send_coupon_email = $this->flexi_send_customer_mail( $data, $user_info, $tracking_link_url, $tracking_pixel_url, $cart_details );
                            return $send_coupon_email;
                        }
                    } else {
                        $send_coupon_email = $this->flexi_send_customer_mail( $data, $user_info, $tracking_link_url, $tracking_pixel_url, $cart_details );
                        return $send_coupon_email;
                    }
                }
            }
        }
    }

    private function flexi_send_customer_mail( $email_data, $user_info, $tracking_link_url, $tracking_pixel_url, $cart_details ) {
        $coupon_type = isset( $email_data['coupon_type'] ) ? $email_data['coupon_type'] : '';
        $coupon_name = isset( $email_data['coupon_name'] ) ? $email_data['coupon_name'] : '';

        if ( !empty( $coupon_name ) ) {
            $tracking_link_url = add_query_arg( array( 'coupon_code' => $coupon_name ), $tracking_link_url );
        }

        $cart_rec_subject       = $email_data['email_subject'];
        $filtered_email_subject = $this->flexi_replace_email_shortcodes( $cart_rec_subject, $user_info, $tracking_link_url, $cart_details, $coupon_type, $coupon_name );

        $cart_rec_body       = $email_data['email_body'];
        $filtered_email_body = $this->flexi_replace_email_shortcodes( $cart_rec_body, $user_info, $tracking_link_url, $cart_details, $coupon_type, $coupon_name );

        $simple_mail_body = $filtered_email_body;

        $email_message_to_send  = "\n\n<img src='" . esc_url_raw( $tracking_pixel_url ) . "' width='1' height='1' alt='' />";
        $email_message_to_send .= $filtered_email_body;

        $email_parts = array(
            'temp_name'        => $email_data['template_name'],
            'subject'          => $filtered_email_subject,
            'message'          => $email_message_to_send,
            'simple_mail_body' => $simple_mail_body,
        );
        return $email_parts;

    }
    private function flexi_coupon_is_executeable( $coupon_type, $coupon_name, $user_info, $cart_details ) {
		if ( 'woocommerce_coupon' === $coupon_type ) {
            $woo_coupon = new WC_Coupon( $coupon_name );
            if ( $woo_coupon->is_valid() ) {
                return true;
            } else {
                return false;
            }
		}
		if ( 'flexi_dynamic_coupon' === $coupon_type ) {
			$user_email  = $user_info['user_email'];
			$coupon_data = $this->fetch_flexi_coupons_data( $coupon_name );

			// Email check
			$restricted_emails = isset( $coupon_data['restricted_emails'] )
			? array_map( 'trim', json_decode( $coupon_data['restricted_emails'], true ) )
			: array();
			if ( in_array( $user_email, $restricted_emails ) ) {
				return false; // User's email is restricted
			}

			// Expiry date check
			$current_date = new DateTime();
			$expiry_date  = new DateTime( $coupon_data['expiry_date'] );
			if ( $current_date > $expiry_date ) {
				return false; // Coupon is expired
			}

			// Coupon filter usage
			$coupon_filter_data   = json_decode( $coupon_data['coupon_filter'], true );
			$is_coupon_applicable = $this->apply_coupon_filter( $coupon_filter_data, $cart_details );
			if ( !$is_coupon_applicable ) {
				return false; // Coupon does not apply based on cart details
			}

			// Usage limit check
			$usage_limit        = $coupon_data['coupon_limit'];
			$total_coupon_usage = $coupon_data['total_coupon_used'];
			if ( $usage_limit > 0 && $total_coupon_usage >= $usage_limit ) {
				return false; // Usage limit has been exceeded
			}

			return true;
		}

        return false;
    }

    private function apply_coupon_filter( $coupon_filter, $cart_items ) {
         $product_ids_in_cart = array_column( $cart_items, 'item_id' );
        $category_ids_in_cart = $this->get_cart_categories( $cart_items );
        // Loop through the coupon filter rules
        foreach ( $coupon_filter as $filter ) {
            if ( $filter['based_on'] == 'product_id' ) {
                if ( $filter['include_exclude'] == 'include' ) {
                    if ( empty( array_intersect( $filter['product_ids'], $product_ids_in_cart ) ) ) {
                        return false;
                    }
                } elseif ( $filter['include_exclude'] == 'exclude' ) {
                    if ( !empty( array_intersect( $filter['product_ids'], $product_ids_in_cart ) ) ) {
                        return false;
                    }
                }
            } elseif ( $filter['based_on'] == 'category_id' ) {
                if ( $filter['include_exclude'] == 'include' ) {
                    if ( empty( array_intersect( $filter['category_ids'], $category_ids_in_cart ) ) ) {
                        return false;
                    }
                } elseif ( $filter['include_exclude'] == 'exclude' ) {
                    if ( !empty( array_intersect( $filter['category_ids'], $category_ids_in_cart ) ) ) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function get_cart_categories( $cart_items ) {
         $category_ids = array();
        foreach ( $cart_items as $item ) {
            $product_id = $item['item_id'];
            $categories = get_the_terms( $product_id, 'product_cat' );
            if ( $categories ) {
                foreach ( $categories as $category ) {
                    $category_ids[] = $category->term_id;
                }
            }
        }
        return $category_ids;
    }

    private function fetch_flexi_coupons_data( $coupon_name ) {
         $abandoned_carts_queries = new Flexi_Database_Queries();
        $coupon_data              = $abandoned_carts_queries->select_db_query( 'flexi_cart_coupons', '*', 'name_code = "' . $coupon_name . '"', 'status = "active"' );

        return isset( $coupon_data[0] ) ? $coupon_data[0] : 'NO SUCH COUPON EXISTS';
    }

    public function flexi_replace_email_shortcodes( $email_content, $user_meta, $tracking_link_url, $cart_details, $coupon_type = '', $coupon_name = '' ) {
         $matches = $this->get_inbetween_strings( '{{', '}}', $email_content );
        if ( !empty( $matches ) ) {
            foreach ( $matches as $field_name ) {
                $search        = '{{' . $field_name . '}}';
                $value         = $this->flexi_replace_field_values_in_email_body( $field_name, $user_meta, $cart_details, $tracking_link_url, $coupon_type, $coupon_name, );
                $email_content = str_replace( $search, $value, $email_content );
            }
        }
        return $email_content;
    }

    private function get_inbetween_strings( $start, $end, $content ) {
         $matches = array();
        preg_match_all( '/\{\{(.*?)\}\}/', $content, $matches );
        return $matches[1];
    }

    public function flexi_replace_field_values_in_email_body( $field_name, $user_meta, $cart_details, $tracking_link_url, $coupon_type = '', $coupon_name = '' ) {
         $field_values = array();
        ( strpos( $field_name, 'user_' ) !== false ) ? $user_field_name = $field_name : $field_name;

        $abandoned_cart_setting = json_decode( get_option( 'flexi_abandon_cart_plugin_global_setting' ), true );
        if ( isset( $abandoned_cart_setting[ $field_name ] ) ) {
            return $abandoned_cart_setting[ $field_name ];
        }

        if ( isset( $user_meta[ $field_name ] ) ) {
            return $user_meta[ $field_name ];
        }

        if ( 'site_url' === $field_name ) {
            return ( "<a href='" . esc_url( site_url() ) . "'>" . get_bloginfo( 'name' ) . '</a>' );
        }

        if ( 'cart_checkout_url' === $field_name ) {
            return ( "<a href='" . $tracking_link_url . "'>Complete Your Purchase</a>" );
        }

        if ( 'cart_details' === $field_name ) {
            $cart_html = '';

            foreach ( $cart_details as $index => $cart_items ) {
                $product_id        = isset( $cart_items['item_id'] ) ? intval( $cart_items['item_id'] ) : 0;
                $product_name      = isset( $cart_items['item_name'] ) ? $cart_items['item_name'] : '';
                $product_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'thumbnail' );
                $quantity          = isset( $cart_items['quantity'] ) ? intval( $cart_items['quantity'] ) : 1;
                $price             = isset( $cart_items['price'] ) ? floatval( $cart_items['price'] ) : 0.00;

                // Build the HTML for the product
                $cart_html .= "
                <div style='display: flex; align-items: center; margin-bottom: 20px;'>
                    <div style='margin-right: 20px;'>
                        <img src='" . esc_url( $product_image_url[0] ) . "' alt='" . esc_attr( $product_name ) . "' style='width: 100px; height: 100px; object-fit: cover;'/>
                    </div>
                    <div>
                        <h4 style='margin: 0;'>" . esc_html( $product_name ) . "</h4>
                        <p style='margin: 5px 0;'>Quantity: " . esc_html( $quantity ) . ' — Total: $' . esc_html( number_format( $price, 2 ) ) . '</p>
                    </div>
                </div>';
            }
            return $cart_html;
        }

        if ( 'coupon_code' === $field_name ) {
            return ( '<b>' . esc_html( $coupon_name ) . '</b>' );
        }

        $is_executable = $this->flexi_coupon_is_executeable( $coupon_type, $coupon_name, $user_meta, $cart_details );

        if ( $is_executable ) {
            // Handle Flexi Dynamic Coupon
            if ( 'flexi_dynamic_coupon' === $coupon_type ) {
                $coupon_data     = $this->fetch_flexi_coupons_data( $coupon_name );
                $discount_amount = ( $coupon_data['discount_type'] === 'fixed_price' )
                ? get_woocommerce_currency_symbol() . $coupon_data['discount_amt']
                : $coupon_data['discount_amt'] . '%';

                if ( 'coupon_discount' === $field_name ) {
                    return $discount_amount;
                }

                // Handle WooCommerce Coupon
            } else {
                $woo_coupon      = new WC_Coupon( $coupon_name );
                $discount_amount = $woo_coupon->get_amount() . ( $woo_coupon->get_discount_type() === 'fixed_cart' ? get_woocommerce_currency_symbol() : '%' );

                if ( 'coupon_discount' === $field_name ) {
                    return $discount_amount;
                }
            }
        }

    }
    private function get_abandoned_cart_users_and_details( $user_id ) {
		if ( !empty( $user_id ) ) {
            $abandoned_carts_queries = new Flexi_Database_Queries();

            $where = array(
				'cart_status'  => 'abandoned',
				'is_expired'   => 0,
				'is_hidden'    => 0,
				'is_purchased' => 0,
            );

            $result            = $abandoned_carts_queries->select_db_query( 'flexi_users_cart_details', 'id , last_interaction_at', 'user_id =' . $user_id, $where, 1 );
            $user_details      = $abandoned_carts_queries->select_db_query( 'flexi_abandon_cart_users', '*', 'id =' . $user_id, 1 );
            $user_cart_details = $abandoned_carts_queries->select_db_query( 'flexi_users_cart_items', '*', 'cart_id =' . $result[0]['id'] );

            return array(
				'last_interaction_at' => $result[0]['last_interaction_at'],
				'user_details'        => $user_details[0],
				'user_cart_details'   => $user_cart_details,
            );
		}
    }

    public function flexi_email_logs_activity( $user_id, $subject, $email_from, $email_to, $status, $message, $temp_name ) {
         $subject = wp_kses_post( $subject );
        $message  = wp_kses_post( $message );

        $abandoned_carts_queries = new Flexi_Database_Queries();
        $abandoned_carts_queries->update_db_query( 'flexi_users_cart_details', array( 'last_interaction_at' => gmdate( 'Y-m-d H:i:s' ) ), 'user_id = ' . $user_id );

        $current_time = gmdate( 'd-M-y h:i:sA', (int) time() );
        $params       = array(
            'email_from'    => $email_from,
            'email_to'      => $email_to,
            'status'        => $status,
            'template_used' => $temp_name,
        );

        $existing_log = $abandoned_carts_queries->select_db_query( 'flexi_email_logs', '*', 'subject = "' . $subject . '"', $params );

        $log_data = array(
            'user_id'       => $user_id,
            'subject'       => $subject,
            'email_from'    => $email_from,
            'email_to'      => $email_to,
            'message'       => $message,
            'status'        => $status,
            'template_used' => $temp_name,
        );

        if ( !empty( $existing_log ) ) {
            $log_id = $existing_log[0]['id'];

            if ( 'success' === $status ) {
                $log_data['send_time']     = $current_time;
                $log_data['success_count'] = $existing_log[0]['success_count'] + 1;
            } elseif ( 'error' === $status ) {
                $log_data['error_time']  = $current_time;
                $log_data['error_count'] = $existing_log[0]['error_count'] + 1;
            }
            $where = 'id = ' . $log_id;

            $abandoned_carts_queries->update_db_query( 'flexi_email_logs', $log_data, $where );
        } else {
            if ( 'success' === $status ) {
                $log_data['send_time']     = $current_time;
                $log_data['success_count'] = 1;
                $log_data['opened']        = 0;
                $log_data['clicked']       = 0;
                $log_data['purchased']     = 0;

            } elseif ( 'error' === $status ) {
                $log_data['error_time']  = $current_time;
                $log_data['error_count'] = 1;
            }

            $abandoned_carts_queries->insert_db_query( 'flexi_email_logs', $log_data );
        }

    }

    public function track_email_open() {
		if ( isset( $_GET['action'] ) && 'track_email_open' === $_GET['action'] && isset( $_GET['user_id'] ) ) {
            $user_id = sanitize_text_field( wp_unslash( $_GET['user_id'] ) );

            $this->increment_open_count( $user_id );
            // Output a transparent 1x1 pixel GIF.
            header( 'Content-Type: image/gif' );

            echo base64_decode( 'R0lGODlhAQABAIAAAAUEBAgAAAAAAABAAEAAAIBRAA7' );
            exit;
		}
    }
    public function increment_open_count( $user_id ) {
         $abandoned_carts_queries = new Flexi_Database_Queries();
        $parms                    = array( 'opened' => 'opened + 1' );
        $where                    = 'user_id = ' . $user_id . '';
        $abandoned_carts_queries->update_db_query( 'flexi_email_logs', $parms, $where );
    }

    public function track_link_click() {
		if ( isset( $_GET['action'] ) && 'track_link_click' === $_GET['action'] && isset( $_GET['user_id'] ) ) {

            $user_id = sanitize_text_field( wp_unslash( $_GET['user_id'] ) );
            $this->increment_click_count( $user_id );
            if ( isset( $_GET['coupon_code'] ) && !empty( $_GET['coupon_code'] ) ) {
                $coupon_code = sanitize_text_field( wp_unslash( $_GET['coupon_code'] ) );

                // Check if it's a WooCommerce coupon
                if ( $this->is_woocommerce_coupon( $coupon_code ) ) {
                    $applied = WC()->cart->apply_coupon( $coupon_code );
                    if ( !$applied ) {
                        wc_add_notice( __( 'WooCommerce Coupon code is invalid or not applicable.', 'woocommerce' ), 'error' );
                    }
                } else {

                    if ( $coupon_code ) {
                        $this->apply_flexi_coupon_discount( $coupon_code, $user_id );
                    }
                }
                wp_safe_redirect( wc_get_checkout_url() );
                exit;
            }
		}

    }

    public function apply_flexi_coupon_discount( $coupon_code, $user_id ) {
		if ( isset( $user_id ) ) {
            $user_id = sanitize_text_field( wp_unslash( $user_id ) );
		} else {
			$user_id = get_current_user_id();
		}
        $user_meta  = get_userdata( $user_id );
        $user_email = $user_meta->user_email;

        // $coupon_code  = "BOGo";
        $flexi_coupon = $this->fetch_flexi_coupons_data( $coupon_code );

		if ( isset( $flexi_coupon['status'] ) && 'active' === $flexi_coupon['status'] ) {
			if ( $flexi_coupon['coupon_limit'] > 0 && $flexi_coupon['total_coupon_used'] >= $flexi_coupon['coupon_limit'] ) {
				wc_add_notice( __( 'Limit for using this coupon has been reached.', 'woocommerce' ), 'error' );
				return;
			}

			$restricted_emails = isset( $flexi_coupon['restricted_emails'] )
			? array_map( 'trim', json_decode( $flexi_coupon['restricted_emails'], true ) )
			: array();
			if ( in_array( $user_email, $restricted_emails ) ) {
				wc_add_notice( __( 'Email is restricted', 'woocommerce' ), 'error' );
				return;
			}

			// Expiry date check
			$current_date = new DateTime();
			$expiry_date  = new DateTime( $flexi_coupon['expiry_date'] );
			if ( $current_date > $expiry_date ) {
				wc_add_notice( __( 'Coupon is Expired.', 'woocommerce' ), 'error' );
				return;
			}

			// Coupon filter usage
			$coupon_filter_data   = json_decode( $flexi_coupon['coupon_filter'], true );
			$users_n_cart_data    = $this->get_abandoned_cart_users_and_details( $user_id );
			$cart_details         = $users_n_cart_data['user_cart_details'];
			$is_coupon_applicable = $this->apply_coupon_filter( $coupon_filter_data, $cart_details );
			if ( !$is_coupon_applicable ) {
				wc_add_notice( __( "Doesn't apply on the current cart items.", 'woocommerce' ), 'error' );
				return;
			}

			if ( '1' === $flexi_coupon['is_individual_use'] ) {
				wc_add_notice( __( 'Cannot be used with other coupon.', 'woocommerce' ), 'success' );
				WC()->cart->remove_coupon( $coupon_code );
			}

			// discount not getting applied
			$discount_amount = $flexi_coupon['discount_amt'];
			if ( 'fixed_price' === $flexi_coupon['discount_type'] ) {
				WC()->cart->add_fee( __( $coupon_code, 'flexi-abandon-cart-recovery' ), -$discount_amount );

			} else {
				$total           = WC()->cart->get_cart_contents_total();
				$discount_amount = ( $discount_amount / 100 ) * $total;
				WC()->cart->add_fee( __( $coupon_code, 'flexi-abandon-cart-recovery' ), -$discount_amount );
			}
		}
    }

    private function is_woocommerce_coupon( $coupon_code ) {
         $coupon = new WC_Coupon( $coupon_code );
        return $coupon->get_id() > 0; // Returns true if it's a valid WooCommerce coupon.
    }

    public function increment_click_count( $user_id ) {
         $abandoned_carts_queries = new Flexi_Database_Queries();
        $parms                    = array( 'clicked' => 'clicked + 1' );
        $where                    = 'user_id = ' . $user_id . '';
        $abandoned_carts_queries->update_db_query( 'flexi_email_logs', $parms, $where );
    }

    public function track_purchase( $order_id ) {
         $order  = wc_get_order( $order_id );
        $user_id = $order->get_user_id();
        $coupons = $order->get_coupon_codes();

        if ( !empty( $coupons ) ) {
            foreach ( $coupons as $coupon_code ) {
                $this->mark_purchase_completed( $user_id, $coupon_code );
            }
        }
        if ( $user_id ) {
            $this->mark_purchase_completed( $user_id );
        }
    }

    private function mark_purchase_completed( $user_id, $coupon_code = '' ) {
         $abandoned_carts_queries = new Flexi_Database_Queries();
        $current_time             = gmdate( 'Y-m-d H:i:s' );
        $parms                    = array(
            'cart_status'         => 'purchased',
            'is_purchased'        => 1,
            'last_interaction_at' => $current_time,
            'created_at'          => $current_time,
        );
        $where                    = 'user_id = ' . $user_id . '';

        $update = $abandoned_carts_queries->update_db_query( 'flexi_users_cart_details', $parms, $where );

        if ( !empty( $coupon_code ) ) {
            $update_params = array( 'total_coupon_used' => 'total_coupon_used' );
            $where         = 'name_code = ' . $coupon_code . '';
            $and_param     = 'is_expired = 0';
            $abandoned_carts_queries->update_db_query( 'flexi_cart_coupons', $update_params, $where, $and_param );
        }
        $em_parms = array(
            'coupon_applied' => $coupon_code,
            'purchased'      => '1',
        );
        $em_where = 'user_id = ' . $user_id . '';
        $abandoned_carts_queries->update_db_query( 'flexi_email_logs', $em_parms, $em_where );
        // Remove items associated with the purchased cart
        $this->remove_purchased_items( $user_id );
    }

    private function remove_purchased_items( $user_id ) {
         $abandoned_carts_queries = new Flexi_Database_Queries();
        $cart_details             = $abandoned_carts_queries->select_db_query( 'flexi_users_cart_details', 'id', 'user_id = ' . $user_id . ' AND is_purchased = 1' );

        if ( !empty( $cart_details ) ) {
            $cart_id = $cart_details[0]['id'];
            $abandoned_carts_queries->delete_db_query( 'flexi_users_cart_items', 'cart_id = ' . $cart_id );
        }
    }

    public function flexi_add_custom_scheduler() {
         $schedules['every_five_minutes'] = array(
			 'interval' => 300,
			 'display'  => __( 'Every 5 Minutes' ),
		 );
		 $schedules['every_two_minutes']  = array(
			 'interval' => 120,
			 'display'  => __( 'Every 2 Minutes' ),
		 );
		 return $schedules;
    }

    public function flexi_coupon_cart_expiry_scheduler() {
		if ( !wp_next_scheduled( 'flexi_check_cart_expiry' ) ) {
            wp_schedule_event( time(), 'every_five_minutes', 'flexi_check_cart_expiry' );
		}

		if ( !wp_next_scheduled( 'flexi_check_coupon_expiry' ) ) {
			wp_schedule_event( time(), 'every_two_minutes', 'flexi_check_coupon_expiry' );
		}
    }

    public function mark_flexi_coupons_expiry() {
         // error_log("inside_coupon");
        $abandoned_carts_queries = new Flexi_Database_Queries();
        $coupons                 = $abandoned_carts_queries->select_db_query( 'flexi_cart_coupons', '*', "status = 'active'" );
        $current_time            = gmdate( 'Y-m-d H:i:s' );

        foreach ( $coupons as $coupon ) {
            $expiry_date = $coupon['expiry_date'];

            $expiry_timestamp  = strtotime( $expiry_date );
            $current_timestamp = strtotime( $current_time );

            // error_log($expiry_timestamp . "=====" . $coupon[ 'expiry_date' ]);
            // error_log($current_timestamp . "=====" . $current_time);

            if ( $current_timestamp > $expiry_timestamp ) {
                error_log( $current_timestamp );

                $em_parms = array(
                    'status'     => 'expired',
                    'is_expired' => '1',
                );
                $abandoned_carts_queries->update_db_query( 'flexi_cart_coupons', $em_parms, 'id = ' . $coupon['id'] );
            }
		}
    }

    public function mark_flexi_cart_expiry() {
         // error_log("inside_cart");

        $abandoned_carts_queries = new Flexi_Database_Queries();
        $carts_to_check          = get_option( 'flexi_cart_to_check_for_expiry', array() );
        if ( empty( $carts_to_check ) ) {
            $store_carts = $abandoned_carts_queries->select_db_query( 'flexi_users_cart_details', 'id', 'cart_status != "active"' );
            $store_carts = wp_list_pluck( $store_carts, 'id' );

            $carts_to_check = array_chunk( $store_carts, 5 );
        }

        if ( !empty( $carts_to_check[0] ) && is_array( $carts_to_check[0] ) && !empty( $carts_to_check[0] ) ) {
            $update_cart_status = $this->flexi_update_cart_status( $carts_to_check[0] );
            unset( $carts_to_check[0] );
            $carts_to_check = array_values( $carts_to_check );
            update_option( 'flexi_cart_to_check_for_expiry', $carts_to_check );
        }
		//  error_log(var_dump($store_carts));
    }

    private function flexi_update_cart_status( $cart_ids ) {
         // Fetch global settings
        $abandoned_cart_setting     = $this->flexi_get_global_setting();
        $cart_expire_after          = $abandoned_cart_setting['cart_expire_after']; // Number
        $cart_expire_after_duration = $abandoned_cart_setting['cart_expire_after_duration']; // 'days' or 'hours'

        // Convert expiry time based on duration
        $expiration_period       = ( $cart_expire_after_duration === 'days' ) ? $cart_expire_after * DAY_IN_SECONDS : $cart_expire_after * HOUR_IN_SECONDS;
        $current_time            = time();
        $abandoned_carts_queries = new Flexi_Database_Queries();

        if ( !empty( $cart_ids ) ) {
            foreach ( $cart_ids as $index => $cart_id ) {
                $aband_time = $abandoned_carts_queries->select_db_query( 'flexi_users_cart_details', 'abandon_time', 'cart_status != "active" AND id = ' . $cart_id );

                $aband_time        = $aband_time[0]['abandon_time'];
                $abandon_timestamp = strtotime( $aband_time );

                $time_diff = $current_time - $abandon_timestamp;

                if ( $time_diff >= $expiration_period ) {
                    $parms = array(
                        'cart_status'         => 'expired',
                        'last_interaction_at' => $current_time,
                        'is_expired'          => '1',
                    );
                    $where = 'id = ' . $cart_id;

                    $abandoned_carts_queries->update_db_query( 'flexi_users_cart_details', $parms, $where );

                    continue;
                } else {
                    continue;
                }
            }
        }
    }
}
// left to do
// 1. trigger and rule implementation
// 2. cart status update check
// 3. coupon expiry check
//4. adding default setting on activation
