<?php
/**
 * Provide an admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link  https://abandoned-cart-recovery
 * @since 1.0.0
 *
 * @package    Abandoned_Cart_Recovery
 * @subpackage Abandoned_Cart_Recovery/admin/partials/
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Flexi_Cart_Maillogs.
 *
 * @since 1.0.0
 */
class Flexi_Cart_Maillogs extends WP_List_Table {


	/**
	 * Flexi_Cart_Maillogs construct.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Email Log', 'flexi-abandon-cart-recovery' ),
				'plural'   => __( 'Email Logs', 'flexi-abandon-cart-recovery' ),
				'ajax'     => false,
			)
		);
	}

	/**
	 * Function for preparing profile data to be displayed in columns.
	 *
	 * @since 1.0.0
	 */
	public function prepareItems() {
		$per_page = apply_filters( 'aband_cart_recov_email_logs_list_per_page', 10 );

		$count    = $this->getCount();
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->getSortableColumns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		0;
		$offset = ( $current_page - 1 ) * $per_page;

		$this->items = $this->get_email_activity( $per_page, $offset );

		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		if ( ! $this->current_action() ) {
			$this->renderHTML();
		} else {
			$this->process_bulk_action();
		}
	}
	/**
	 * Function to count number of emails in result.
	 *
	 * @since  1.0.0
	 *
	 * @return int The count of emails.
	 */
	public static function getCount() {
		$abandoned_carts_queries = new Flexi_Database_Queries();

		$count = $abandoned_carts_queries->select_db_query( 'flexi_email_logs', 'COUNT(id)' );
		return ! empty( $count ) ? intval( $count[0]['COUNT(id)'] ) : 0;
	}

	/**
	 * Function for the checkbox column.
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
	 * Text displayed when no customer data is available.
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No Logs About Mails Found.', 'flexi-abandon-cart-recovery' );
	}

	/**
	 * Associative array of columns
	 *
	 * @since  1.0.0
	 * @return array Associative array of columns.
	 */
	public function get_columns() {
		$columns = array(
			'cb'         => '<input type="checkbox">',
			'subject'    => __( 'Subject', 'flexi-abandon-cart-recovery' ),
			'email_from' => __( 'Email From', 'flexi-abandon-cart-recovery' ),
			'email_to'   => __( 'Email To', 'flexi-abandon-cart-recovery' ),
			'send_count' => __( 'Send Count', 'flexi-abandon-cart-recovery' ),
			'status'     => __( 'Status', 'flexi-abandon-cart-recovery' ),
			'response'   => __( 'Response', 'flexi-abandon-cart-recovery' ),
			'date'       => __( 'Time', 'flexi-abandon-cart-recovery' ),
			'template_used'       => __( 'Template Used', 'flexi-abandon-cart-recovery' ),
			'coupon_applied'       => __( 'Coupon Applied', 'flexi-abandon-cart-recovery' ),
		);
		return apply_filters( 'abandon_cart_recovery_email_activity_columns', $columns );
	}

	/**
	 * Get sortable columns
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function getSortableColumns() {
		return array(
			'subject'    => array( 'subject', false ),
			'email_from' => array( 'email_from', false ),
			'email_to'   => array( 'email_to', false ),
			'send_count' => array( 'send_count', false ),
			'response'   => array( 'message', false ),
			'status'     => array( 'status', false ),
			'date'       => array( 'date', false ),
			'template_used'       =>array( 'template_used', false ),
			'coupon_applied'       =>array( 'coupon_applied', false ),
		);
	}

	/**
	 * Outputs the subject column value for each row.
	 *
	 * @param array $item An associative array containing the email data for the current row.
	 * @return string Escaped subject text for display.
	 */
	public function column_subject( $item ) {
		return esc_html( $item['subject'] );
	}

	/**
	 * Outputs the "From" email address for each row.
	 *
	 * @param array $item An associative array containing the email data for the current row.
	 * @return string Escaped "from" email address for display.
	 */
	public function column_email_from( $item ) {
		return esc_html( $item['email_from'] );
	}

	/**
	 * Outputs the "To" email address for each row.
	 *
	 * @param array $item An associative array containing the email data for the current row.
	 * @return string Escaped "to" email address for display.
	 */
	public function column_email_to( $item ) {
		return esc_html( $item['email_to'] );
	}

	/**
	 * Outputs the email response message for each row.
	 *
	 * @param array $item An associative array containing the email data for the current row.
	 * @return string Escaped response message for display.
	 */
	public function column_response( $item ) {
		return esc_html( $item['message'] );
	}

	/**
	 * Outputs the date and time based on the email status.
	 * If the status is 'success', it shows the send time.
	 * If the status is 'error', it shows the error time.
	 *
	 * @param array $item An associative array containing the email data for the current row.
	 * @return string The send time or error time based on the status, escaped for display.
	 */
	public function column_date( $item ) {
		if ( 'success' === $item['status'] ) {
			$datetime = isset( $item['send_time'] ) ? esc_html( $item['send_time'] ) : '';
			return $datetime;
		}
		if ( 'error' === $item['status'] ) {
			$datetime = isset( $item['error_time'] ) ? esc_html( $item['error_time'] ) : '';
			return $datetime;
		}
	}

	/**
	 * Outputs the email status for each row.
	 *
	 * @param array $item An associative array containing the email data for the current row.
	 * @return string Escaped email status for display.
	 */
	public function column_status( $item ) {
		return esc_html( $item['status'] );
	}

	public function column_template_used( $item ) {
		return esc_html( $item['template_used'] );
	}
	public function column_coupon_applied( $item ) {
		return esc_html( $item['coupon_applied'] );
	}
	/**
	 * Outputs the send or error count based on the email status.
	 * If the status is 'success', it returns the success count.
	 * If the status is 'error', it returns the error count.
	 *
	 * @param array $item An associative array containing the email data for the current row.
	 * @return string The success or error count, escaped for display.
	 */
	public function column_send_count( $item ) {
		if ( 'success' === $item['status'] ) {
			return esc_html( $item['success_count'] );
		}
		if ( 'error' === $item['status'] ) {
			return esc_html( $item['error_count'] );
		}
	}

	/**
	 * Defines the available bulk actions for the email log table.
	 *
	 * @return array An array of bulk actions, with the action identifier as the key and label as the value.
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => esc_html__( 'Delete', 'flexi-abandon-cart-recovery' ),
		);

		return $actions;
	}

	/**
	 * Function to render the HTML.
	 *
	 * @since 1.0.0
	 */
	public function renderHTML() {
		?>
  <div class="wrap cart_recovery_body">
            <!-- Notifications -->
            <?php
				if ( isset( $_GET['log-deleted'] ) && 'true' === $_GET['log-deleted'] ) {
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Record Deleted Successfully.', 'flexi-abandon-cart-recovery' ) . '</p></div>';
				}
            ?>  
            <br>
			           
            <div class="clear"></div>
            
            <div id="poststuff">
                <div id="post-body" class="metabox-holder">
                    <div class="meta-box-sortables ui-sortable">
                        <form method="post">
                            <?php
							wp_nonce_field( 'flexi_cart_mail_logs', 'abandon_cart_email_logs_actions' );
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
	 * Function for getting current action.
	 *
	 * @since  1.0.0
	 * @return string|false The action name or false.
	 */
	public function current_action() {
		return $this->get_current_action();
	}

	/**
	 * Retrieves the current action being performed.
	 *
	 * This method is used to obtain the action that is currently being executed. It can be useful for determining
	 * which action is in progress and handling it appropriately.
	 *
	 * @return string The name of the current action.
	 */
	public function get_current_action() {
		if ( isset( $_GET['panel'] ) ) {
			$action = isset( $_GET['panel'] ) ? sanitize_text_field( wp_unslash( $_GET['panel'] ) ) : '';
			return $action;
		} elseif ( isset( $_POST['action'] ) ) {

			if ( ! isset( $_POST['abandon_cart_email_logs_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['abandon_cart_email_logs_actions'] ) ), 'flexi_cart_mail_logs' ) ) {
				return;
			}

			$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
			return $action;
		} elseif ( isset( $_POST['action2'] ) ) {

			if ( ! isset( $_POST['abandon_cart_email_logs_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['abandon_cart_email_logs_actions'] ) ), 'flexi_cart_mail_logs' ) ) {
				return;
			}

			$action = isset( $_POST['action2'] ) ? sanitize_text_field( wp_unslash( $_POST['action2'] ) ) : '';
			return $action;
		}
	}
	/**
	 * Function for processing bulk actions.
	 *
	 * @since 1.0.0
	 */
	public function process_bulk_action() {
		$action = $this->current_action();
		if ( $action ) {
			switch ( $action ) {
				case 'bulk-delete':
					// Call a method to handle the bulk delete action.
					$this->bulk_delete_emails();
					break;
			}
		}
	}

	/**
	 * Handle bulk delete action.
	 *
	 * @since 1.0.0
	 */
	public function bulk_delete_emails() {
		global $wpdb;
		if ( ! empty( $_POST['row_ids'] ) ) {

			if ( ! isset( $_POST['abandon_cart_email_logs_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['abandon_cart_email_logs_actions'] ) ), 'flexi_cart_mail_logs' ) ) {
				return;
			}
			$ids_to_delete = sanitize_text_field( wp_unslash( $_POST['row_ids'] ) );

			$abandoned_carts_queries = new Flexi_Database_Queries();
			foreach ( $ids_to_delete as $id_delete ) {
				$where   = array('id' => $id_delete);
				$results = $abandoned_carts_queries->delete_db_query( 'flexi_email_logs', $where );
			}

			wp_safe_redirect( add_query_arg( 'log-deleted', 'true', ! empty( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) );
			exit;
		}
	}

	/**
	 * Retrieves email activity log data with pagination and sorting.
	 *
	 * This function fetches a list of email activity entries based on the provided pagination and sorting options.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $per_page Number of records to retrieve per page.
	 * @param int    $offset   Offset for the query, used for pagination.
	 * @param string $orderby  Column to order the results by.
	 * @param string $order    Sort order, either 'ASC' or 'DESC'.
	 *
	 * @return array An array containing the email activity log data.
	 */
	public function get_email_activity( $per_page, $offset ) {
		$abandoned_carts_queries = new Flexi_Database_Queries();

		$order   = isset( $_GET['order'] ) && in_array( strtoupper( wp_unslash( $_GET['order'] ) ), array( 'ASC', 'DESC' ), true ) ? strtoupper( wp_unslash( $_GET['order'] ) ) : 'DESC';
		$orderby = isset( $_GET['orderby'] ) ? sanitize_sql_orderby( wp_unslash( $_GET['orderby'] ) ) : 'id';

		if ( 'date' === $orderby ) {
			$orderby = 'CASE WHEN status = "success" OR status = "error" THEN send_time ELSE error_time END';
		} elseif ( 'send_count' === $orderby ) {
			$orderby = 'CASE WHEN status = "success" OR status = "error" THEN success_count ELSE error_count END';
		}
		// Fetch data with sorting and pagination.
		$results = $abandoned_carts_queries->get_sorted_result( 'flexi_email_logs', '*', array(), $orderby, $order, $per_page, $offset );

		$activities = array();

		if ( isset( $results ) ) {
			foreach ( $results as $result ) {
				$activities[] = array(
					'id'            => $result['id'],
					'subject'       => $result['subject'],
					'email_from'    => $result['email_from'],
					'email_to'      => $result['email_to'],
					'send_time'     => $result['send_time'],
					'status'        => $result['status'],
					'message'       => $result['message'],
					'success_count' => $result['success_count'],
					'error_count'   => $result['error_count'],
					'error_time'    => $result['error_time'],
					'template_used' => $result['template_used'],
					'coupon_applied'    => $result['coupon_applied'],
				);
			}
		}
		return $activities;
	}
}

$aband_cart_recov_user_list_obj = new Flexi_Cart_Maillogs();
$aband_cart_recov_user_list_obj->prepareItems();
