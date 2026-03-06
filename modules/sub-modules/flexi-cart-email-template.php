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
 * Flexi_Cart_Email_Template
 *
 * @since 1.0.0
 */
class Flexi_Cart_Email_Template extends WP_List_Table
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
                'singular' => __('Email Template', 'flexi-abandon-cart-recovery'),
                'plural'   => __('Email Templates', 'flexi-abandon-cart-recovery'),
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
        $per_page = apply_filters('aband_cart_rec_temp_list_per_page', 20);
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $current_page = $this->get_pagenum();
        $offset       = ($current_page - 1) * $per_page;

        $this->items = self::email_templates_data($per_page, $offset);
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
     * Get bulk actions
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_bulk_actions()
    {
        return array(
            'bulk-delete' => __('Delete', 'flexi-abandon-cart-recovery'),
        );
    }

    /**
     * Get count of email templates
     *
     * @since 1.0.0
     *
     * @return int
     */
    public function get_count()
    {
        $language = get_option('aban_cart_rec_language');
        include_once FLEXI_ABANDON_CART_RECOVERY_DIR . 'admin/partials/class-flexi-database-queries.php';
        $abandoned_carts_queries = new Flexi_Database_Queries();
        $query_results           = $abandoned_carts_queries->select_db_query('flexi_email_templates', 'COUNT(id)', 'temp_language = "' . esc_sql($language) . '"');

        return !empty($query_results) ? intval($query_results[ 0 ][ 'COUNT(id)' ]) : 0;
    }

    /**
     * Display no items message
     *
     * @since 1.0.0
     */
    public function no_items()
    {
        esc_html_e('No Templates To Display.', 'flexi-abandon-cart-recovery');
    }

    /**
     * Display template name column
     *
     * @param array $items The item data.
     * @return string
     */
    public function column_temp_name($items)
    {
        if (empty($items[ 'template_name' ]) || empty($items[ 'id' ])) {
            return '';
        }

        $temp_name = '<p><strong>' . esc_html($items[ 'template_name' ]) . '</strong></p>';

        $request_page    = isset($_REQUEST[ 'page' ]) ? sanitize_text_field(wp_unslash($_REQUEST[ 'page' ])) : '';
        $request_section = isset($_REQUEST[ 'section' ]) ? sanitize_text_field(wp_unslash($_REQUEST[ 'section' ])) : '';

        $actions = array(
            'edit' => sprintf('<a href="?page=%s&section=%s&panel=edit&templateID=%s">Edit</a>', esc_attr($request_page), esc_attr($request_section), esc_attr($items[ 'id' ])),
        );

        return $temp_name . $this->row_actions($actions);
    }

    /**
     * Display template type column
     *
     * @param array $items The item data.
     * @return string
     */
    public function column_temp_type($items)
    {
        return isset($items[ 'template_type' ]) ? $items[ 'template_type' ] : '';
    }

    /**
     * Display template status column
     *
     * @param array $items The item data.
     * @return string
     */
    public function column_temp_status($items)
    {
        return isset($items[ 'status' ]) && 'on' === $items[ 'status' ] ? 'Active' : 'Inactive';
    }

    /**
     * Display template subject column
     *
     * @param array $items The item data.
     * @return string
     */
    public function column_temp_subj($items)
    {
        return isset($items[ 'email_subject' ]) ? esc_html($items[ 'email_subject' ]) : '';
    }

    /**
     * Display template coupon type column
     *
     * @param array $items The item data.
     * @return string
     */
    public function column_coupon_type($items)
    {
        return isset($items[ 'coupon_type' ]) && '' !== $items[ 'coupon_type' ] ? esc_html($items[ 'coupon_type' ]) : '--';
    }

    /**
     * Display template coupon name column
     *
     * @param array $items The item data.
     * @return string
     */
    public function column_coupon_name($items)
    {
        return isset($items[ 'coupon_name' ]) && '' !== $items[ 'coupon_name' ] ? esc_html($items[ 'coupon_name' ]) : '--';
    }

    /**
     * Display checkbox column
     *
     * @param array $items The item data.
     * @return string
     */
    public function column_cb($items)
    {
        $template_ids = isset($items[ 'id' ]) ? $items[ 'id' ] : '';
        return sprintf(
            '<input type="checkbox" name="template_ids[]" value="%s" />',
            esc_attr($template_ids)
        );
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
            'temp_name'   => __('Template Name', 'flexi-abandon-cart-recovery'),
            'temp_type'   => __('Template Type', 'flexi-abandon-cart-recovery'),
            'temp_subj'   => __('Template Subject', 'flexi-abandon-cart-recovery'),
            'temp_status' => __('Status', 'flexi-abandon-cart-recovery'),
            'coupon_type' => __('Coupon Type', 'flexi-abandon-cart-recovery'),
            'coupon_name' => __('Coupon Name', 'flexi-abandon-cart-recovery'),
        );

        return apply_filters('cart_recovery_email_temp_columns', $columns);
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
            'temp_name'   => array('template_name', false),
            'temp_type'   => array('template_type', false),
            'temp_subj'   => array('email_subject', false),
            'temp_status' => array('status', false),
            'coupon_type' => array('coupon_type', false),
            'coupon_name' => array('coupon_name', false),
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
            <!-- Action Button -->
            <div class="alignleft">
            <a href="<?php echo esc_url('admin.php?page=flexi-cart-recovery-settings&section=email-settings&tab=email-templates&panel=email-template-creation'); ?>"
						id="email-template-creation"
						class="button alignleft"><?php esc_html_e('Create new template', 'flexi-abandon-cart-recovery');?></a>
            </div>

            <div class="clear"></div>

            <div id="poststuff">
                <div id="post-body" class="metabox-holder">
                    <div class="meta-box-sortables ui-sortable">
                        <form method="post">
                            <?php
wp_nonce_field('abandon_cart_templates', 'abandon_cart_templates_actions');
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
            if (!isset($_POST[ 'abandon_cart_templates_actions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'abandon_cart_templates_actions' ])), 'abandon_cart_templates')) {
                return null;
            }

            return sanitize_text_field(wp_unslash($_POST[ 'action' ]));
        } elseif (isset($_POST[ 'action2' ])) {
            if (!isset($_POST[ 'abandon_cart_templates_actions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'abandon_cart_templates_actions' ])), 'abandon_cart_templates')) {
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
                    'page'    => 'flexi-cart-recovery-settings',
                    'section' => 'email-settings',
                    'notice'  => rawurlencode($notice),
                ),
                admin_url('admin.php')
            );
            wp_safe_redirect($redirect_url);
            exit;
        } elseif ((isset($_POST[ 'action' ]) && 'bulk-delete' === $_POST[ 'action' ]) || (isset($_POST[ 'action2' ]) && 'bulk-delete' === $_POST[ 'action2' ])) {
            if (!isset($_POST[ 'abandon_cart_templates_actions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'abandon_cart_templates_actions' ])), 'abandon_cart_templates')) {
                return;
            }
            $template_ids = isset($_POST[ 'template_ids' ]) ? array_map('sanitize_text_field', (array) wp_unslash($_POST[ 'template_ids' ])) : array();

            $abandoned_carts_queries = new Flexi_Database_Queries();
            $default_template_id     = get_option('acr_default_tempID') ? get_option('acr_default_tempID') : 1;

            $notice = '';

            foreach ($template_ids as $template_id) {
                if (!is_numeric($template_id)) {
                    continue; // Skip non-numeric IDs.
                }

                if ($template_id === $default_template_id) {
                    $notice = __('The default template cannot be deleted.', 'flexi-abandon-cart-recovery');
                    continue;
                }

                $template_data = $abandoned_carts_queries->select_db_query('flexi_email_templates', '*', 'id = ' . esc_sql($template_id));
                if (!empty($template_data) && isset($template_data[ 0 ][ 'status' ]) && 'on' === $template_data[ 0 ][ 'status' ]) {
                    $notice = __('Active templates cannot be deleted.', 'flexi-abandon-cart-recovery');
                    continue;
                }

                $abandoned_carts_queries->delete_db_query('flexi_email_templates', array('id ' => esc_sql($template_id)));
            }

            $redirect_url = add_query_arg(
                array(
                    'page'    => 'flexi-cart-recovery-settings',
                    'section' => 'email-settings',
                    'notice'  => rawurlencode($notice),
                ),
                admin_url('admin.php')
            );
            wp_safe_redirect($redirect_url);
            exit;
        } elseif ((isset($_GET[ 'templateID' ]))
            || (isset($_GET[ 'panel' ]) && 'email-template-creation' === $_GET[ 'panel' ])) {
            $file = FLEXI_ABANDON_CART_RECOVERY_DIR . 'modules/sub-modules/flexi-add-edit-template.php';
            if (file_exists($file)) {
                include_once $file;
            }
        }
    }

    /**
     * Retrieve email templates data
     *
     * @since 1.0.0
     * @param int $per_page Number of items per page.
     * @param int $offset Current page number.
     * @return array
     */
    public function email_templates_data($per_page, $offset)
    {
        $language                = get_option('aban_cart_rec_language');
        $abandoned_carts_queries = new Flexi_Database_Queries();

        $order   = isset($_GET[ 'order' ]) && in_array(strtoupper(wp_unslash($_GET[ 'order' ])), array('ASC', 'DESC'), true) ? strtoupper(wp_unslash($_GET[ 'order' ])) : 'ASC';
        $orderby = isset($_GET[ 'orderby' ]) ? sanitize_sql_orderby(wp_unslash($_GET[ 'orderby' ])) : 'id';

        $results = $abandoned_carts_queries->get_sorted_result('flexi_email_templates', '*', array('temp_language = "' . esc_sql($language) . '"'), $orderby, $order, $per_page, $offset);

        return is_array($results) ? $results : array();
    }
}

$abandoned_cart_rec_temp_obj = new Flexi_Cart_Email_Template();
$abandoned_cart_rec_temp_obj->prepare_items();
