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
 * @subpackage Flexi_Abandon_Cart_Recovery/modules/sub-modules
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('WP_List_Table')) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Flexi_Cart_Dynamic_Coupon
 *
 * @since 1.0.0
 */
class Flexi_Cart_Dynamic_Coupon extends WP_List_Table
{

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct(
            array(
                'singular' => __('Flexi Coupon', 'flexi-abandon-cart-recovery'),
                'plural'   => __('Flexi Coupons', 'flexi-abandon-cart-recovery'),
                'ajax'     => true,
            )
        );
    }

    /**
     * Prepare items for display
     *
     * @since 1.0.0
     */
    public function prepare_items()
    {
        $per_page = apply_filters('flexi_cart_coupon_per_page', 20);
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $current_page = $this->get_pagenum();
        $offset       = ($current_page - 1) * $per_page;

        $this->items = self::get_flexi_dynamic_coupon($per_page, $offset);
        $count       = self::get_count();

        $this->set_pagination_args(
            array(
                'total_items' => $count,
                'per_page'    => $per_page,
                'total_pages' => ceil($count / $per_page),
            )
        );

        if (!$this->current_action()) {
            $this->render_html();
        } else {
            $this->process_bulk_action();
        }
    }

    /**
     * Get columns
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            'cb'          => '<input type="checkbox" />',
            'code'        => __('Coupon Code', 'flexi-abandon-cart-recovery'),
            'coupon_type' => __('Coupon Type', 'flexi-abandon-cart-recovery'),
            'coupon_amt'  => __('Coupon Amount', 'flexi-abandon-cart-recovery'),
            'limit'       => __('Usage/Limit', 'flexi-abandon-cart-recovery'),
            'status'      => __('Status', 'flexi-abandon-cart-recovery'),
            'expiry_date' => __('Expiry Date', 'flexi-abandon-cart-recovery'),
            'edit_action' => __('Action ', 'flexi-abandon-cart-recovery'),
        );

        return apply_filters('flexi_cart_dynamic_coupon_columns', $columns);
    }

    /**
     * Get bulk actions
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_bulk_actions()
    {
        return array(
            'bulk-delete'   => __('Delete', 'flexi-abandon-cart-recovery'),
            'bulk-active'   => __('Active', 'flexi-abandon-cart-recovery'),
            'bulk-inactive' => __('Inactive', 'flexi-abandon-cart-recovery'),
        );
    }

    /**
     * Display no items message
     *
     * @since 1.0.0
     */
    public function no_items()
    {
        esc_html_e('No Coupons To Display.', 'flexi-abandon-cart-recovery');
    }

    public function column_edit_action($item)
    {
        $request_page    = isset($_REQUEST[ 'page' ]) ? sanitize_text_field(wp_unslash($_REQUEST[ 'page' ])) : '';
        $request_section = isset($_REQUEST[ 'section' ]) ? sanitize_text_field(wp_unslash($_REQUEST[ 'section' ])) : '';

        $action = sprintf(
            '<a href="?page=%s&section=%s&tabs=view-coupon&panel=edit&couponID=%d">Edit</a>',
            esc_attr($request_page),
            esc_attr($request_section),
            esc_attr($item[ 'id' ])
        );
        $html = "<p>" . $action . "</p>";

        return $html;
    }
    /**
     * Display checkbox column
     *
     * @param array $items The items data.
     * @return string
     */
    public function column_cb($items)
    {
        $coupon_ids = isset($items[ 'id' ]) ? $items[ 'id' ] : '';
        return sprintf(
            '<input type="checkbox" name="coupon_ids[]" value="%s" />',
            esc_attr($coupon_ids)
        );
    }

    /**
     * Display code column
     *
     * @param array $items The items data.
     * @return string
     */
    public function column_code($items)
    {
        return isset($items[ 'name_code' ]) ? $items[ 'name_code' ] : "";
    }

    /**
     * Display coupon_type column
     *
     * @param array $items The items data.
     * @return string
     */
    public function column_coupon_type($items)
    {
        return ($items[ 'discount_type' ]);

    }

    /**
     * Display expiry_date column
     *
     * @param array $items The items data.
     * @return string
     */
    public function column_expiry_date($items)
    {
        $input_date     = isset($items[ 'expiry_date' ]) ? $items[ 'expiry_date' ] : "";

        if(!empty($input_date)){
        // $date           = date($input_date);
        $formatted_date = date_i18n('d-m-Y h:i A' , strtotime($input_date));
        }else{
            $formatted_date = "Never";
        }
        return $formatted_date;
        // return ;
    }

    /**
     * Display coupon_amt column
     *
     * @param array $items The items data.
     * @return string
     */
    public function column_coupon_amt($items)
    {
        return ($items[ 'discount_amt' ]);
    }

    /**
     * Display coupon_amt column
     *
     * @param array $items The items data.
     * @return string
     */
    public function column_limit($items)
    {
        $coupon_limit = isset($items[ 'coupon_limit' ]) && "0" !== $items[ 'coupon_limit' ] ? $items[ 'coupon_limit' ] : "∞";
        return ("0/".$coupon_limit);
    }

    /**
     * Display coupon status column
     *
     * @param array $items The items data.
     * @return string
     */
    public function column_status($items)
    {
        return isset($items[ 'status' ]) ? ucfirst($items[ 'status' ]) : ucfirst($items[ 'status' ]);
    }

    /**
     * Get count of email coupons
     *
     * @since 1.0.0
     *
     * @return int
     */
    public function get_count()
    {
        $abandoned_carts_queries = new Flexi_Database_Queries();
        $count                   = $abandoned_carts_queries->select_db_query('flexi_cart_coupons', 'COUNT(id)', array('is_expired' => "0"));

        return !empty($count) ? intval($count[ 0 ][ 'COUNT(id)' ]) : 0;
    }

    /**
     * Get sortable columns
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        return array(
            'code'        => array('name_code', false),
            'coupon_type' => array('discount_type', false),
            'coupon_amt'  => array('discount_amt', false),
            'limit'       => array('coupon_limit', false),
            'expiry_date' => array('expiry_date', false),
        );
    }

    /**
     * Render HTML content
     *
     * @since 1.0.0
     */
    public function render_html()
    {
        if (isset($_GET[ 'record-delete' ]) && 'true' === $_GET[ 'record-delete' ]) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Coupon(s) Deleted Successfully.', 'flexi-abandon-cart-recovery') . '</p></div>';
        } elseif (isset($_GET[ 'record-inactive' ]) && 'true' === $_GET[ 'record-inactive' ]) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Coupon(s) Inactivated Successfully.', 'flexi-abandon-cart-recovery') . '</p></div>';
        } elseif (isset($_GET[ 'record-active' ]) && 'true' === $_GET[ 'record-active' ]) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Coupon(s) Activated Successfully.', 'flexi-abandon-cart-recovery') . '</p></div>';
        }
        ?>
<div class="wrap cart_recovery_body">
    <!-- Notifications -->
    <?php
if (isset($_GET[ 'notice' ])) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html(sanitize_text_field(wp_unslash($_GET[ 'notice' ]))) . '</p></div>';
        }
        ?>
    <br>
    <div class="clear"></div>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div class="meta-box-sortables ui-sortable">
                <form method="post">
                    <?php
wp_nonce_field('abandon_cart_coupons', 'abandon_cart_coupons_actions');
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
     * Get the current action
     *
     * @since 1.0.0
     * @return string|null
     */
    public function current_action()
    {
        if (isset($_POST[ 'action' ])) {
            if (!isset($_POST[ 'abandon_cart_coupons_actions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'abandon_cart_coupons_actions' ])), 'abandon_cart_coupons')) {
                return null;
            }

            return sanitize_text_field(wp_unslash($_POST[ 'action' ]));
        } elseif (isset($_POST[ 'action2' ])) {
            if (!isset($_POST[ 'abandon_cart_coupons_actions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'abandon_cart_coupons_actions' ])), 'abandon_cart_coupons')) {
                return null;
            }
            return sanitize_text_field(wp_unslash($_POST[ 'action2' ]));
        } elseif (isset($_GET[ 'panel' ]) && 'edit' === $_GET[ 'panel' ] && isset($_GET[ 'couponID' ])) {

            $file = FLEXI_ABANDON_CART_RECOVERY_DIR . 'modules/flexi-create-dynamic-coupons.php';
            if (file_exists($file)) {
                include_once $file;
            }
            die;
        }
    }

    /**
     * Process bulk actions
     *
     * @since 1.0.0
     */
    public function process_bulk_action()
    {
        if (isset($_POST[ 'action' ]) && isset($_POST[ 'action2' ])) {

            if (('-1' === $_POST[ 'action' ]) || ('-1' === $_POST[ 'action2' ])) {
                $notice       = __('No bulk action selected.', 'flexi-abandon-cart-recovery');
                $redirect_url = add_query_arg(
                    array(
                        'page'    => 'flexi-abandon-cart-recovery-coupons',
                        'section' => 'view-coupon',
                        'notice'  => rawurlencode($notice),
                    ),
                    admin_url('admin.php')
                );
                wp_safe_redirect($redirect_url);
                exit;
            } elseif ('bulk-delete' === $_POST[ 'action' ] && 'bulk-delete' === $_POST[ 'action2' ]) {
                if (!isset($_POST[ 'abandon_cart_coupons_actions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'abandon_cart_coupons_actions' ])), 'abandon_cart_coupons')) {
                    wp_die(esc_html__('Nonce verification failed!', 'flexi-abandon-cart-recovery'));
                }
                $this->bulk_action_perform('delete');

            } elseif ('bulk-active' === $_POST[ 'action' ] && 'bulk-active' === $_POST[ 'action2' ]) {
                if (!isset($_POST[ 'abandon_cart_coupons_actions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'abandon_cart_coupons_actions' ])), 'abandon_cart_coupons')) {
                    wp_die(esc_html__('Nonce verification failed!', 'flexi-abandon-cart-recovery'));
                }
                $this->bulk_action_perform('active');
            } elseif ('bulk-inactive' === $_POST[ 'action' ] && 'bulk-inactive' === $_POST[ 'action2' ]) {
                if (!isset($_POST[ 'abandon_cart_coupons_actions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'abandon_cart_coupons_actions' ])), 'abandon_cart_coupons')) {
                    wp_die(esc_html__('Nonce verification failed!', 'flexi-abandon-cart-recovery'));
                }
                $this->bulk_action_perform('inactive');
            }

        }
    }

    /**
     * Handles bulk_action_perform action
     *
     * @since 1.0.0
     */
    public function bulk_action_perform($perform_acion)
    {

        if (!empty($_POST[ 'coupon_ids' ])) {
            if (!isset($_POST[ 'abandon_cart_coupons_actions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'abandon_cart_coupons_actions' ])), 'abandon_cart_coupons')) {
                wp_die(esc_html__('Nonce verification failed!', 'flexi-abandon-cart-recovery'));
            }

            $abandoned_carts_queries = new Flexi_Database_Queries();
            $ids_to_perform          = array_map('sanitize_text_field', wp_unslash($_POST[ 'coupon_ids' ]));
            $request_uri             = isset($_SERVER[ 'REQUEST_URI' ]) ? esc_url_raw(wp_unslash($_SERVER[ 'REQUEST_URI' ])) : '';

            foreach ($ids_to_perform as $id_action) {
                if ('delete' === $perform_acion) {

                    $where   = array('id' => $id_action);
                    $results = $abandoned_carts_queries->delete_db_query('flexi_cart_coupons', $where);

                    wp_safe_redirect(add_query_arg('record-' . $perform_acion, 'true', $request_uri));

                } elseif ('inactive' === $perform_acion) {

                    $parms   = array('status' => "inactive");
                    $where   = 'id = ' . $id_action;
                    $results = $abandoned_carts_queries->update_db_query('flexi_cart_coupons', $parms, $where);

                    $request_uri = isset($_SERVER[ 'REQUEST_URI' ]) ? esc_url_raw(wp_unslash($_SERVER[ 'REQUEST_URI' ])) : '';
                    wp_safe_redirect(add_query_arg('record-' . $perform_acion, 'true', $request_uri));

                } elseif ('active' === $perform_acion) {

                    $parms   = array('status' => "active");
                    $where   = 'id = ' . $id_action;
                    $results = $abandoned_carts_queries->update_db_query('flexi_cart_coupons', $parms, $where);

                    $request_uri = isset($_SERVER[ 'REQUEST_URI' ]) ? esc_url_raw(wp_unslash($_SERVER[ 'REQUEST_URI' ])) : '';
                    wp_safe_redirect(add_query_arg('record-' . $perform_acion, 'true', $request_uri));
                }
            }
            exit;
        }
    }
    /**
     * Retrieve email coupons data
     *
     * @since 1.0.0
     * @param int $per_page Number of items per page.
     * @param int $offset Current page number.
     * @return array
     */
    public function get_flexi_dynamic_coupon($per_page, $offset)
    {
        $abandoned_carts_queries = new Flexi_Database_Queries();
        $order                   = isset($_GET[ 'order' ]) && in_array(strtoupper(wp_unslash($_GET[ 'order' ])), array('ASC', 'DESC'), true) ? strtoupper(wp_unslash($_GET[ 'order' ])) : 'ASC';
        $orderby                 = isset($_GET[ 'orderby' ]) ? sanitize_sql_orderby(wp_unslash($_GET[ 'orderby' ])) : 'id';

        $results = $abandoned_carts_queries->get_sorted_result('flexi_cart_coupons', '*', array('is_expired' => 0), $orderby, $order, $per_page, $offset);

        return is_array($results) ? $results : array();

    }
}

$abandoned_cart_rec_temp_obj = new Flexi_Cart_Dynamic_Coupon();
$abandoned_cart_rec_temp_obj->prepare_items();