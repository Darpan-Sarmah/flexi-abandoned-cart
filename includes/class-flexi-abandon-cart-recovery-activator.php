<?php

/**
 * Fired during plugin activation
 *
 * @link       https://test
 * @since      1.0.0
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/includes
 * @author     Start and Grow <test@gmailcom>
 */
class Flexi_Abandon_Cart_Recovery_Activator {
    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
         global $wpdb;
        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset_collate = $wpdb->get_charset_collate();

        // Define table creation queries.
        $tables = array(
            $wpdb->prefix . 'flexi_users_cart_details' => "
                CREATE TABLE IF NOT EXISTS {$wpdb->prefix}flexi_users_cart_details (
					id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					user_id BIGINT(20) UNSIGNED NOT NULL,
					cart_status VARCHAR(20) NOT NULL,
					created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
					abandon_time DATETIME DEFAULT NULL,
					last_interaction_at DATETIME DEFAULT NULL,
					is_expired TINYINT(1) NOT NULL DEFAULT 0,
					is_hidden TINYINT(1) NOT NULL DEFAULT 0,
					is_purchased TINYINT(1) NOT NULL DEFAULT 0,
					extra_data TEXT,

					PRIMARY KEY (id),
					UNIQUE KEY unique_cart (id)

                ) $charset_collate;",

            $wpdb->prefix . 'flexi_abandon_cart_users' => "
				CREATE TABLE IF NOT EXISTS {$wpdb->prefix}flexi_abandon_cart_users (
					id BIGINT(20) UNSIGNED DEFAULT NULL,
					user_information LONGTEXT NOT NULL,
					PRIMARY KEY (id)
				) $charset_collate;",

            $wpdb->prefix . 'flexi_users_cart_items'   => "
                CREATE TABLE IF NOT EXISTS {$wpdb->prefix}flexi_users_cart_items (
                    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    cart_id BIGINT(20) UNSIGNED NOT NULL,
                    item_id BIGINT(20) UNSIGNED NOT NULL,
					item_name VARCHAR(100) NOT NULL,
                    quantity INT NOT NULL,
                    price DECIMAL(10, 2) NOT NULL,
                    PRIMARY KEY (id),
                    FOREIGN KEY (cart_id) REFERENCES {$wpdb->prefix}flexi_users_cart_details(id)

                ) $charset_collate;",

            $wpdb->prefix . 'flexi_email_logs'         => "
                CREATE TABLE IF NOT EXISTS {$wpdb->prefix}flexi_email_logs (
                    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    user_id VARCHAR(20) NOT NULL,
                    subject VARCHAR(255) NOT NULL,
                    email_from VARCHAR(255) NOT NULL,
                    email_to VARCHAR(255) NOT NULL,
                    send_time DATETIME NOT NULL,
                    status VARCHAR(10) NOT NULL,
                    message TEXT DEFAULT NULL,
                    error_time DATETIME NOT NULL,
                    success_count INT DEFAULT 0,
                    error_count INT DEFAULT 0,
                    template_used VARCHAR(255) NOT NULL,
                    coupon_applied VARCHAR(255) NOT NULL,
                    opened INT(11) DEFAULT 0 NOT NULL,
                    clicked INT(11) DEFAULT 0 NOT NULL,
                    purchased INT(11) DEFAULT 0 NOT NULL,
                    extra_data TEXT,
                    
                    PRIMARY KEY (id)
            ) $charset_collate;",

            $wpdb->prefix . 'flexi_email_templates'    => "
				CREATE TABLE IF NOT EXISTS {$wpdb->prefix}flexi_email_templates (
					id BIGINT(50) UNSIGNED NOT NULL AUTO_INCREMENT,
					temp_language VARCHAR(50) NOT NULL,
					template_name VARCHAR(255) NOT NULL,
					template_type TINYINT(1) NOT NULL DEFAULT 0,
					email_subject VARCHAR(255) NOT NULL,
					email_body TEXT NOT NULL,
					coupon_status VARCHAR(10) NOT NULL,
					coupon_type VARCHAR(255) NOT NULL,
					coupon_name VARCHAR(255) NOT NULL,
					status VARCHAR(10) NOT NULL,
					created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
					extra_data TEXT,

					PRIMARY KEY (id)
				) $charset_collate;",

            $wpdb->prefix . 'flexi_cart_coupons'       => "
				CREATE TABLE IF NOT EXISTS {$wpdb->prefix}flexi_cart_coupons (
					id BIGINT(50) UNSIGNED NOT NULL AUTO_INCREMENT,
					status VARCHAR(10) NOT NULL,
					name_code  VARCHAR(255) NOT NULL,
					discount_type VARCHAR(100) NOT NULL,
					discount_amt VARCHAR(100) NOT NULL,
					is_individual_use TINYINT(1) NOT NULL DEFAULT 0,
					coupon_limit INT(11) DEFAULT 0 NOT NULL,
					total_coupon_used INT(11) DEFAULT 0 NOT NULL,
					coupon_filter TEXT NOT NULL,
					expiry_date VARCHAR(100) NOT NULL,
					restricted_emails LONGTEXT NULL,
					created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
					is_expired TINYINT(1) NOT NULL DEFAULT 0,
					extra_data TEXT,

					PRIMARY KEY (id)
				) $charset_collate;",

            $wpdb->prefix . 'flexi_email_rules'        => "
				CREATE TABLE IF NOT EXISTS {$wpdb->prefix}flexi_email_rules (
					id BIGINT(50) UNSIGNED NOT NULL AUTO_INCREMENT,
					rule_name  VARCHAR(255) NOT NULL,
					rule_type VARCHAR(100) NOT NULL,
					rule_filter LONGTEXT NOT NULL,
					status VARCHAR(10) NOT NULL,
					extra_data TEXT,

					PRIMARY KEY (id)
				) $charset_collate;",

            $wpdb->prefix . 'flexi_email_triggers'     => "
				CREATE TABLE IF NOT EXISTS {$wpdb->prefix}flexi_email_triggers (
					id BIGINT(50) UNSIGNED NOT NULL AUTO_INCREMENT,
					trigger_name  VARCHAR(255) NOT NULL,
					trigger_temp_name VARCHAR(100) NOT NULL,
					trigger_rule  VARCHAR(100) NOT NULL,
					status  TINYINT(1) NOT NULL DEFAULT 0,
					send_after_num VARCHAR(100) NOT NULL,
					send_after_span VARCHAR(100) NOT NULL,
					extra_data TEXT,

					PRIMARY KEY (id)
				) $charset_collate;",
        );

        // Create tables.
        foreach ( $tables as $table_name => $sql ) {
            dbDelta( $sql );
        }

        self::default_option_saving();
        self::insert_default_email_template();
        //saving default email template
        //saving default rule set
    }

    /**
     * Save default options.
     *
     * @since 1.0.0
     */
    public static function default_option_saving() {
         $default_setting = array(
			 'enable_tracking'             => 'on',
			 'cart_abandon_time'           => 10,
			 'cart_abandon_time_duration'  => 'minutes',
			 'resend_email_after'          => 10,
			 'resend_email_after_duration' => 'minutes',
			 'cart_expire_after'           => 30,
			 'cart_expire_after_duration'  => 'days',
			 'email_from'                  => 'info@flexi.com',
			 'email_name'                  => get_bloginfo( 'name' ),
			 'gdpr_message'                => 'We use your contact details to send cart reminders and inform you about the new products and offers.',
			 'email_and_plugin_language'   => 'en_US',
		 );

		 $result = update_option( 'abandon_cart_recovery_plugin_setting', wp_json_encode( $default_setting ), true );

		 // Check for errors during option saving.
		 if ( !$result ) {
			 error_log( 'Error saving default options.' );
		 }
    }

    private static function insert_default_email_template() {
         global $wpdb;
        $table_name          = $wpdb->prefix . 'flexi_email_templates';
        $default_template_id = 1;
        update_option( 'flexi_default_aband_cart_tempID', $default_template_id );

        // Prepare and execute the query.
        $existing_record = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT id FROM %s WHERE id = %d',
                $table_name,
                $default_template_id
            )
        );

        $html  = '';
        $html .= '<div style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">';

        $html .= ' <div style="max-width: 600px; margin: 20px auto; background-color: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">';
        $html .= '<h1 style="color: #333; text-align: center;">Complete Your Purchase</h1>';
        $html .= '<p style="text-align: center;">Looks like you left some items in your cart. Don’t miss out!</p>';
        $html .= '{{cart_details}}';
        $html .= '<div style="display: block; text-align: center; background-color: #dee5ed; padding: 15px 20px; border-radius: 5px; font-size: 18px; margin-top: 20px;" >';
        $html .= '{{cart_checkout_url}} </div></div>';
        $html .= '<hr><p>Thank you.</p><br>';
        $html .= '<p>{{email_name}}</p><br></div>';

        $body = wp_kses_post( $html );

        if ( !$existing_record ) {
            $record_data = array(
                'id'            => $default_template_id,
                'temp_language' => 'en_US',
                'template_name' => 'Abandon Cart Recovery Email Template',
                'template_type' => '1',
                'email_subject' => 'Your cart is wondering where you went',
                'email_body'    => $body,
                'status'        => 'on',
                'created_at'    => gmdate( 'Y-m-d H:i:s' ),
            );
            $wpdb->insert( $table_name, $record_data );
        }

        $default_coupon_tem_id = 2;
        update_option( 'flexi_default_coupon_tempID', $default_coupon_tem_id );

        // Prepare and execute the query.
        $existing_record2 = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT id FROM %s WHERE id = %d',
                $table_name,
                $default_coupon_tem_id
            )
        );

        $html2  = '';
        $html2 .= '<div style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">';
        $html2 .= ' <div style="max-width: 600px; margin: 20px auto; background-color: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">';
        $html2 .= '<h1 style="color: #333; text-align: center;">Your favorite items are still waiting for you</h1>';
        $html2 .= '<p style="text-align: center;">Complete your order now and enjoy {{coupon_discount}} off!</p>';
        $html2 .= '<p style="text-align: center;">With the following {{coupon_code}}</p>';
        $html2 .= '{{cart_details}}';
        $html2 .= '<div style="display: block; text-align: center; background-color: #dee5ed; padding: 15px 20px; border-radius: 5px; font-size: 18px; margin-top: 20px;" >';
        $html2 .= '{{cart_checkout_url}} </div></div>';
        $html2 .= '<hr><p>Thank you.</p><br>';
        $html2 .= '<p>{{email_name}}</p><br></div>';

        $body2 = wp_kses_post( $html2 );

        if ( !$existing_record2 ) {
            $record_data = array(
                'id'            => $default_coupon_tem_id,
                'temp_language' => 'en_US',
                'template_name' => 'Flexi Coupon Email Template',
                'template_type' => '1',
                'email_subject' => 'Your cart has some discount for you',
                'email_body'    => $body2,
                'status'        => 'on',
                'coupon_status' => 'on',
                'created_at'    => gmdate( 'Y-m-d H:i:s' ),
            );
            $wpdb->insert( $table_name, $record_data );
        }

        if ( $wpdb->last_error ) {
            error_log( 'Error inserting default email template.' );
        }

    }

}
