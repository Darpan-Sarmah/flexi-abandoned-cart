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
class Flexi_Cart_Expired_Coupons extends WP_List_Table
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
                'singular' => __('Flexi Expired Coupon', 'flexi-abandon-cart-recovery'),
                'plural'   => __('Flexi Expired Coupons', 'flexi-abandon-cart-recovery'),
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
        $per_page = apply_filters('flexi_cart_expired_coupon_per_page', 20);
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $current_page = $this->get_pagenum();
        $offset       = ($current_page - 1) * $per_page;

        $this->items = self::get_flexi_expired_coupon($per_page, $offset);
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
        );

        return apply_filters('flexi_cart_expired_coupon_columns', $columns);
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
        );
    }

    /**
     * Display no items message
     *
     * @since 1.0.0
     */
    public function no_items()
    {
        esc_html_e('No Expired Coupons To Display.', 'flexi-abandon-cart-recovery');
    }

    /**
     * Display checkbox column
     *
     * @param array $items The item data.
     * @return string
     */
    public function column_cb($item)
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
     * @param array $items The item data.
     * @return string
     */
    public function column_code($item)
    {
    }

    /**
     * Display coupon_type column
     *
     * @param array $items The item data.
     * @return string
     */
    public function column_coupon_type($item)
    {
    }

    /**
     * Display expiry_date column
     *
     * @param array $items The item data.
     * @return string
     */
    public function column_expiry_date($item)
    {
    }

    /**
     * Display coupon_amt column
     *
     * @param array $items The item data.
     * @return string
     */
    public function column_coupon_amt($item)
    {
    }

    /**
     * Display coupon_amt column
     *
     * @param array $items The item data.
     * @return string
     */
    public function column_limit($item)
    {
    }

    /**
     * Display coupon status column
     *
     * @param array $items The item data.
     * @return string
     */
    public function column_status($items)
    {
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
            'code'        => array('coupon_name', false),
            'coupon_type' => array('coupon_type', false),
            'coupon_amt'  => array('coupon_amt', false),
            'limit'       => array('limit', false),
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
wp_nonce_field('flexi_cart_expired_coupons', 'flexi_cart_expired_coupons_actions');
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
        if (isset($_GET[ 'panel' ])) {
            return sanitize_text_field(wp_unslash($_GET[ 'panel' ]));
        } elseif (isset($_POST[ 'action' ])) {
            if (!isset($_POST[ 'flexi_cart_expired_coupons_actions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'flexi_cart_expired_coupons_actions' ])), 'flexi_cart_expired_coupons')) {
                return null;
            }

            return sanitize_text_field(wp_unslash($_POST[ 'action' ]));
        } elseif (isset($_POST[ 'action2' ])) {
            if (!isset($_POST[ 'flexi_cart_expired_coupons_actions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'flexi_cart_expired_coupons_actions' ])), 'flexi_cart_expired_coupons')) {
                return null;
            }
            return sanitize_text_field(wp_unslash($_POST[ 'action2' ]));
        }

        return null;
    }

    /**
     * Process bulk actions
     *
     * @since 1.0.0
     */
    public function process_bulk_action()
    {
        if (('-1' === $_POST[ 'action' ]) || ('-1' === $_POST[ 'action2' ])) {
            $notice       = __('No bulk action selected.', 'flexi-abandon-cart-recovery');
            $redirect_url = add_query_arg(
                array(
                    'page'    => 'flexi-abandon-cart-recovery-coupons',
                    'section' => 'expired-coupon',
                    'notice'  => rawurlencode($notice),
                ),
                admin_url('admin.php')
            );
            wp_safe_redirect($redirect_url);
            exit;
        } elseif ((isset($_POST[ 'action' ]) && 'bulk-delete' === $_POST[ 'action' ]) || (isset($_POST[ 'action2' ]) && 'bulk-delete' === $_POST[ 'action2' ])) {
            if (!isset($_POST[ 'flexi_cart_expired_coupons_actions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'flexi_cart_expired_coupons_actions' ])), 'flexi_cart_expired_coupons')) {
                return;
            }
            $coupon_ids = isset($_POST[ 'coupon_ids' ]) ? array_map('sanitize_text_field', (array) wp_unslash($_POST[ 'coupon_ids' ])) : array();

            $abandoned_carts_queries = new Flexi_Database_Queries();
            $notice = '';

            foreach ($coupon_ids as $coupon_id) {
               $abandoned_carts_queries->delete_db_query('flexi_email_coupons', array('id ' => esc_sql($coupon_id)));
               $notice = __('Coupons Cannot Be Deleted.', 'flexi-abandon-cart-recovery');
            }

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
        } elseif ((isset($_GET[ 'couponID' ]))
            || (isset($_GET[ 'panel' ]) && 'email-coupon-creation' === $_GET[ 'panel' ])) {
            $file = FLEXI_ABANDON_CART_RECOVERY_DIR . 'modules/sub-modules/flexi-add-edit-coupons.php';
            if (file_exists($file)) {
                include_once $file;
            }
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
    public function get_flexi_expired_coupon($per_page, $offset)
    {
        $abandoned_carts_queries = new Flexi_Database_Queries();
        $order                   = isset($_GET[ 'order' ]) && in_array(strtoupper(wp_unslash($_GET[ 'order' ])), array('ASC', 'DESC'), true) ? strtoupper(wp_unslash($_GET[ 'order' ])) : 'ASC';
        $orderby                 = isset($_GET[ 'orderby' ]) ? sanitize_sql_orderby(wp_unslash($_GET[ 'orderby' ])) : 'id';

        $results = $abandoned_carts_queries->get_sorted_result('flexi_cart_coupons', '*', array('is_expired' => 1), $orderby, $order, $per_page, $offset);

        return is_array($results) ? $results : array();
    }
}

$abandoned_cart_rec_temp_obj = new Flexi_Cart_Expired_Coupons();
$abandoned_cart_rec_temp_obj->prepare_items();
