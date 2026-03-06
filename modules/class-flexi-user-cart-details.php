<?php
/**
 * Provide an admin area view for the plugin.
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
	die;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Flexi_User_Cart_Details
 *
 * @since 1.0.0
 */
class Flexi_User_Cart_Details extends WP_List_Table {

	/**
	 * Flexi_User_Cart_Details constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'User List', 'flexi-abandon-cart-recovery'),
				'plural'   => __( 'Users List', 'flexi-abandon-cart-recovery'),
				'ajax'     => false,
			)
		);
	}

	/**
	 * Prepares items for display in columns
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {
			$per_page = 20;
			$columns  = $this->get_columns();
			$hidden   = array();
			$sortable = $this->get_sortable_columns();

			$this->_column_headers = array( $columns, $hidden, $sortable );

			$current_page = $this->get_pagenum();
			$offset       = ( $current_page - 1 ) * $per_page;

			$this->items = self::get_abandoned_carts( $per_page, $offset );
			$count       = self::get_count();

			$this->set_pagination_args(
				array(
					'total_items' => $count,
					'per_page'    => $per_page,
					'total_pages' => ceil( $count / $per_page ),
				)
			);

		if ( ! $this->current_action() ) {
			$this->render_html();
		} else {
			$this->process_bulk_action();
		}
	}

	/**
	 * Counts the number of emails in the result
	 *
	 * @since  1.0.0
	 * @return int The count of emails.
	 */
	public static function get_count() {
		// include_once FLEXI_ABANDON_CART_RECOVERY_DIR . 'admin/partials/class-flexi-database-queries.php';
		$abandoned_carts_queries = new Flexi_Database_Queries();
		$count                   = $abandoned_carts_queries->select_db_query( 'acr_users_cart_details', 'COUNT(id)', 'is_hidden = 0' );

		return ! empty( $count ) ? intval( $count[0]['COUNT(id)'] ) : 0;
	}

	/**
	 * Generates the checkbox column
	 *
	 * @param  array $item Item.
	 * @return string HTML for checkbox.
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="row_ids[]" value="%s" />',
			esc_attr( $item['id'] )
		);
	}

	/**
	 * Text displayed when no customer data is available
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No Abandoned Carts Found.', 'flexi-abandon-cart-recovery');
	}

	/**
	 * Gets the columns for the table
	 *
	 * @since  1.0.0
	 * @return array Associative array of columns.
	 */
	public function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox">',
			'client_name' => __( 'User Name', 'flexi-abandon-cart-recovery'),
			'user_email'  => __( 'User Email', 'flexi-abandon-cart-recovery'),
			'cart_status' => __( 'Order Status', 'flexi-abandon-cart-recovery'),
			'cart_date'   => __( 'Date Time', 'flexi-abandon-cart-recovery'),
		);
		return apply_filters( 'abandon_cart_recovery_user_list_columns', $columns );
	}

	/**
	 * Displays the client name column
	 *
	 * @param  array $item Item.
	 * @return string HTML for client name.
	 */
	public function column_client_name( $item ) {
		return esc_html( $item['fullname'] );
	}

	/**
	 * Displays the user email column
	 *
	 * @param  array $item Item.
	 * @return string HTML for user email.
	 */
	public function column_user_email( $item ) {
		return esc_html( $item['email'] );
	}

	/**
	 * Displays the cart status column
	 *
	 * @param  array $item Item.
	 * @return string HTML for cart status.
	 */
	public function column_cart_status( $item ) {
		return esc_html( ucfirst( $item['cart_status'] ) );
	}

	/**
	 * Displays the cart date column
	 *
	 * @param  array $item Item.
	 * @return string HTML for cart date.
	 */
	public function column_cart_date( $item ) {
		if ( 'abandoned' === $item['cart_status'] || 'removed' === $item['cart_status'] ) {
			return esc_html( $item['abandoned_time'] );
		} elseif ( 'purchased' === $item['cart_status'] ) {
			return esc_html( $item['purchased_time'] );
		} else {
			return esc_html( $item['purchased_time'] );
		}
	}

	/**
	 * Returns an associative array containing the bulk actions
	 *
	 * @since  1.0.0
	 * @return array The bulk actions.
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'Delete', 'flexi-abandon-cart-recovery'),
		);
		return $actions;
	}

	/**
	 * Returns an associative array of sortable columns
	 *
	 * @since  1.0.0
	 * @return array The sortable columns.
	 */
	public function get_sortable_columns() {
		return array(
			'cart_status' => array( 'cart_status', false ),
			'cart_date'   => array( 'cart_date', false ),
		);
	}

	/**
	 * Renders the HTML for the table
	 *
	 * @since 1.0.0
	 */
	public function render_html() {
		?>
		<div class="wrap cart_recovery_body">
            <!-- Notifications -->
            <?php
				if ( isset( $_GET['record-deleted'] ) && 'true' === $_GET['record-deleted'] ) {
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Record Deleted Successfully.', 'flexi-abandon-cart-recovery') . '</p></div>';
				}
            ?>  
            <br>
			           
            <div class="clear"></div>
            
            <div id="poststuff">
                <div id="post-body" class="metabox-holder">
                    <div class="meta-box-sortables ui-sortable">
                        <form method="post">
                            <?php
							wp_nonce_field( 'abandon_cart_user_lists', 'abandon_cart_user_listsactions' );
							$this->display();
                            ?>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="clear"></div>
        </div>
        <?php
    }

	/**
	 * Gets the current action
	 *
	 * @since  1.0.0
	 * @return string|false The action name or false.
	 */
	public function current_action() {
		return $this->get_current_action();
	}

	/**
	 * Retrieves the current action from request
	 *
	 * @since  1.0.0
	 * @return string|false The action name or false.
	 */
	public function get_current_action() {
		if ( isset( $_GET['panel'] ) ) {
			$action = sanitize_text_field( wp_unslash( $_GET['panel'] ) );
			return $action;
		} elseif ( isset( $_POST['action'] ) ) {
			if ( ! isset( $_POST['abandon_cart_user_listsactions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['abandon_cart_user_listsactions'] ) ), 'abandon_cart_user_lists' ) ) {
				return false;
			}
			$action = sanitize_text_field( wp_unslash( $_POST['action'] ) );
			return $action;
		} elseif ( isset( $_POST['action2'] ) ) {
			if ( ! isset( $_POST['abandon_cart_user_listsactions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['abandon_cart_user_listsactions'] ) ), 'abandon_cart_user_lists' ) ) {
				return false;
			}
			$action = sanitize_text_field( wp_unslash( $_POST['action2'] ) );
			return $action;
		}
		return false;
	}

	/**
	 * Processes bulk actions
	 *
	 * @since 1.0.0
	 */
	public function process_bulk_action() {
		$action = $this->current_action();
		if ( $action ) {
			switch ( $action ) {
				case 'bulk-delete':
					if ( ! isset( $_POST['abandon_cart_user_listsactions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['abandon_cart_user_listsactions'] ) ), 'abandon_cart_user_lists' ) ) {
						wp_die( esc_html__( 'Nonce verification failed!', 'flexi-abandon-cart-recovery') );
					}
					$this->bulk_delete_emails();
					break;
			}
		}
	}

	/**
	 * Handles bulk delete action
	 *
	 * @since 1.0.0
	 */
	public function bulk_delete_emails() {
		global $wpdb;
		if ( ! empty( $_POST['row_ids'] ) ) {
			if ( ! isset( $_POST['abandon_cart_user_listsactions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['abandon_cart_user_listsactions'] ) ), 'abandon_cart_user_lists' ) ) {
				wp_die( esc_html__( 'Nonce verification failed!', 'flexi-abandon-cart-recovery') );
			}

			$ids_to_delete = array_map( 'sanitize_text_field', wp_unslash( $_POST['row_ids'] ) );

			$abandoned_carts_queries = new Flexi_Database_Queries();

			foreach ( $ids_to_delete as $id_delete ) {
				$parms   = array( 'is_hidden' => 1 );
				$where   = $wpdb->prepare( 'id = %d', $id_delete );
				$results = $abandoned_carts_queries->update_db_query( 'acr_users_cart_details', $parms, $where );
			}

			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

			wp_safe_redirect( add_query_arg( 'record-deleted', 'true', $request_uri ) );
			exit;
		}
	}

	/**
	 * Gets the abandoned carts.
	 *
	 * @since 1.0.0
	 * @param int $per_page Items per page.
	 * @param int $offset offset page number.
	 * @return array Abandoned carts data.
	 */
	public function get_abandoned_carts( $per_page, $offset ) {

		// include_once FLEXI_ABANDON_CART_RECOVERY_DIR . '/includes/class-abandoned-cart-recovery-dataqueries.php';
		// $abandoned_carts_queries = new Flexi_Database_Queries();

		// $order   = isset( $_GET['order'] ) && in_array( strtoupper( wp_unslash( $_GET['order'] ) ), array( 'ASC', 'DESC' ), true ) ? strtoupper( wp_unslash( $_GET['order'] ) ) : 'DESC';
		// $orderby = isset( $_GET['orderby'] ) ? sanitize_sql_orderby( wp_unslash( $_GET['orderby'] ) ) : 'id';

		// if ( 'cart_date' === $orderby ) {
		// 	$orderby = 'CASE WHEN cart_status = "abandoned" OR cart_status = "removed" THEN abandon_time ELSE time_log END';
		// }

		// $results = $abandoned_carts_queries->get_sorted_result( 'acr_users_cart_details', '*', array( 'is_hidden = 0' ), $orderby, $order, $per_page, $offset );

		// $abandoned_carts = array();
		// if ( ! empty( $results ) ) {
		// 	foreach ( $results as $result ) {
		// 		$user_json = json_decode( $result['user_information'], true );

		// 		$user_email     = $user_json[0]['user_email'];
		// 		$first_name     = $user_json[0]['user_firstname'];
		// 		$last_name      = $user_json[0]['user_lastname'];
		// 		$full_name      = trim( $first_name . ' ' . $last_name );
		// 		$display_name   = isset( $user_json[0]['user_displayname'] ) ? $user_json[0]['user_displayname'] : '';
		// 		$nice_name      = isset( $user_json[0]['user_nicename'] ) ? $user_json[0]['user_nicename'] : '';
		// 		$abandoned_time = isset( $result['abandon_time'] ) ? $result['abandon_time'] : '';
		// 		$purchased_time = isset( $result['time_log'] ) ? $result['time_log'] : '';

		// 		$display_or_nicename = '' !== $display_name ? $display_name : $nice_name;

		// 		$abandoned_carts[] = array(
		// 			'id'             => $result['id'],
		// 			'email'          => $user_email,
		// 			'fullname'       => '' !== $full_name ? $full_name : $display_or_nicename,
		// 			'cart_status'    => $result['cart_status'],
		// 			'abandoned_time' => $abandoned_time,
		// 			'purchased_time' => $purchased_time,
		// 		);
		// 	}
		// }
		// return $abandoned_carts;
	}
}

$aband_cart_recov_user_list_obj = new Flexi_User_Cart_Details();
$aband_cart_recov_user_list_obj->prepare_items();
