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
    die;
}

if (!class_exists('WP_List_Table')) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Flexi_Cart_Rule_Sets
 *
 * @since 1.0.0
 */
class Flexi_Cart_Rule_Sets extends WP_List_Table
{

    /**
     * Flexi_Cart_Rule_Sets constructor
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct(
            array(
                'singular' => __('Rule Set', 'flexi-abandon-cart-recovery'),
                'plural'   => __('Rule Sets', 'flexi-abandon-cart-recovery'),
                'ajax'     => false,
            )
        );
    }

    /**
     * Prepares items for display in columns
     *
     * @since 1.0.0
     */
    public function prepare_items()
    {
        $per_page = 20;
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $current_page = $this->get_pagenum();
        $offset       = ($current_page - 1) * $per_page;

        $this->items = self::get_carts_rules_sets($per_page, $offset);
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
     * Counts the number of emails in the result
     *
     * @since  1.0.0
     * @return int The count of emails.
     */
    public static function get_count()
    {
        include_once FLEXI_ABANDON_CART_RECOVERY_DIR . 'admin/partials/class-flexi-database-queries.php';
        $abandoned_carts_queries = new Flexi_Database_Queries();
        $count                   = $abandoned_carts_queries->select_db_query('flexi_email_rules', 'COUNT(id)');

        return !empty($count) ? intval($count[ 0 ][ 'COUNT(id)' ]) : 0;
    }

    /**
     * Generates the checkbox column
     *
     * @param  array $item Item.
     * @return string HTML for checkbox.
     */
    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="row_ids[]" value="%s" />',
            esc_attr($item[ 'id' ])
        );
    }

    /**
     * Text displayed when no customer data is available
     *
     * @since 1.0.0
     */
    public function no_items()
    {
        esc_html_e('No Rule Sets For Carts Found.', 'flexi-abandon-cart-recovery');
    }

    /**
     * Gets the columns for the table
     *
     * @since  1.0.0
     * @return array Associative array of columns.
     */
    public function get_columns()
    {
        $columns = array(
            'cb'        => '<input type="checkbox">',
            'rule_name' => __('Rule Name', 'flexi-abandon-cart-recovery'),
            'rule_type' => __('Rule Type', 'flexi-abandon-cart-recovery'),
            'status'    => __('Status', 'flexi-abandon-cart-recovery'),
            'edit'      => __('Action', 'flexi-abandon-cart-recovery'),
        );
        return apply_filters('flexi_cart_rules_sets_columns', $columns);
    }

    public function column_edit($item)
    {
        $request_page    = isset($_REQUEST[ 'page' ]) ? sanitize_text_field(wp_unslash($_REQUEST[ 'page' ])) : '';
        $request_section = isset($_REQUEST[ 'section' ]) ? sanitize_text_field(wp_unslash($_REQUEST[ 'section' ])) : '';
        $action          = sprintf(
            '<a href="?page=%s&section=%s&tab=rule-set&panel=edit_rule&ruleSetID=%d">Edit</a>',
            esc_attr($request_page),
            esc_attr($request_section),
            esc_attr($item[ 'id' ])
        );
        $html = "<b>" . $action . "</b>";

        return $html;
    }

    /**
     * Displays the rule name column
     *
     * @param  array $item Item.
     * @return string HTML for rule name.
     */
    public function column_rule_name($item)
    {
        return esc_html($item[ 'rule_name' ]);
    }

    /**
     * Displays the rule type column
     *
     * @param  array $item Item.
     * @return string HTML for rule type.
     */
    public function column_rule_type($item)
    {
        return esc_html($item[ 'rule_type' ]);
    }

    /**
     * Displays the rule status column
     *
     * @param  array $item Item.
     * @return string HTML for rule status.
     */
    public function column_status($item)
    {
        return esc_html(ucfirst($item[ 'status' ]));
    }

    /**
     * Returns an associative array containing the bulk actions
     *
     * @since  1.0.0
     * @return array The bulk actions.
     */
    public function get_bulk_actions()
    {
        $actions = array(
            'bulk-active'   => __('Active', 'flexi-abandon-cart-recovery'),
            'bulk-delete'   => __('Delete', 'flexi-abandon-cart-recovery'),
            'bulk-inactive' => __('Inactive', 'flexi-abandon-cart-recovery'),
        );
        return $actions;
    }

    /**
     * Returns an associative array of sortable columns
     *
     * @since  1.0.0
     * @return array The sortable columns.
     */
    public function get_sortable_columns()
    {
        return array(
            'status'    => array('status', false),
            'rule_name' => array('rule_name', false),
            'rule_type' => array('rule_type', false),
        );
    }

    /**
     * Renders the HTML for the table
     *
     * @since 1.0.0
     */
    public function render_html()
    {
        ?>
<div class="wrap">
    <!-- Notifications -->
    <?php
if (isset($_GET[ 'record-delete' ]) && 'true' === $_GET[ 'record-delete' ]) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Record Deleted Successfully.', 'flexi-abandon-cart-recovery') . '</p></div>';
        } elseif (isset($_GET[ 'record-inactive' ]) && 'true' === $_GET[ 'record-inactive' ]) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Rules Inactivated Successfully.', 'flexi-abandon-cart-recovery') . '</p></div>';
        } elseif (isset($_GET[ 'record-active' ]) && 'true' === $_GET[ 'record-active' ]) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Rules Activated Successfully.', 'flexi-abandon-cart-recovery') . '</p></div>';
        }
        ?>
    <br>
    <!-- Action Button -->
    <div class="alignright">
        <a href="<?php echo esc_url('admin.php?page=flexi-cart-recovery-settings&section=email-settings&tab=rule-set&panel=add_rule'); ?>"
            id="email-template-creation" class="button button-primary">
            <?php esc_html_e('Add New Rule', 'flexi-abandon-cart-recovery');?>
        </a>
    </div>

    <div class="clear"></div>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div class="meta-box-sortables ui-sortable">
                <form method="post">
                    <?php
wp_nonce_field('flexi_rule_sets_lists', 'flexi_rule_sets_listsactions');
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
    public function current_action()
    {
        return $this->get_current_action();
    }

    /**
     * Retrieves the current action from request
     *
     * @since  1.0.0
     * @return string|false The action name or false.
     */
    public function get_current_action()
    {
        if (isset($_POST[ 'action' ]) && ('-1' === $_POST[ 'action' ]) || isset($_POST[ 'action2' ]) && ('-1' === $_POST[ 'action2' ])) {
            $notice       = __('No bulk action selected.', 'flexi-abandon-cart-recovery');
            $redirect_url = add_query_arg(
                array(
                    'page'    => 'flexi-cart-recovery-settings',
                    'section' => 'email-settings',
                    'tab'     => 'rule-set',
                    'notice'  => rawurlencode($notice),
                ),
                admin_url('admin.php')
            );
            wp_redirect($redirect_url);
            exit;
        } elseif (isset($_GET[ 'panel' ]) && ('add_rule' === $_GET[ 'panel' ] || ('edit_rule' === $_GET[ 'panel' ] && isset($_GET[ 'ruleSetID' ])))) {
            $file = FLEXI_ABANDON_CART_RECOVERY_DIR . 'modules/sub-modules/flexi-add-edit-rulesets.php';
            if (file_exists($file)) {
                include_once $file;
            }
            exit;
        }
        return isset($_REQUEST[ 'action' ]) ? sanitize_text_field(wp_unslash($_REQUEST[ 'action' ])) : false;
    }

    /**
     * Processes bulk actions
     *
     * @since 1.0.0
     */
    public function process_bulk_action()
    {
        $action = $this->current_action();
        if ($action) {
            switch ($action) {
                case 'bulk-delete':
                    if (!isset($_POST[ 'flexi_rule_sets_listsactions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'flexi_rule_sets_listsactions' ])), 'flexi_rule_sets_lists')) {
                        wp_die(esc_html__('Nonce verification failed!', 'flexi-abandon-cart-recovery'));
                    }
                    $this->bulk_action_perform('delete');
                    break;
                case 'bulk-inactive':
                    if (!isset($_POST[ 'flexi_rule_sets_listsactions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'flexi_rule_sets_listsactions' ])), 'flexi_rule_sets_lists')) {
                        wp_die(esc_html__('Nonce verification failed!', 'flexi-abandon-cart-recovery'));
                    }
                    $this->bulk_action_perform('inactive');
                    break;
                case 'bulk-active':
                    if (!isset($_POST[ 'flexi_rule_sets_listsactions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'flexi_rule_sets_listsactions' ])), 'flexi_rule_sets_lists')) {
                        wp_die(esc_html__('Nonce verification failed!', 'flexi-abandon-cart-recovery'));
                    }
                    $this->bulk_action_perform('active');
                    break;
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
        // echo"<pre>";
        // print_r($_POST);
        // echo"</pre>";
        // exit;
        if (!empty($_POST[ 'row_ids' ])) {
            if (!isset($_POST[ 'flexi_rule_sets_listsactions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'flexi_rule_sets_listsactions' ])), 'flexi_rule_sets_lists')) {
                wp_die(esc_html__('Nonce verification failed!', 'flexi-abandon-cart-recovery'));
            }

            $abandoned_carts_queries = new Flexi_Database_Queries();
            $ids_to_perform          = array_map('sanitize_text_field', wp_unslash($_POST[ 'row_ids' ]));
            $request_uri             = isset($_SERVER[ 'REQUEST_URI' ]) ? esc_url_raw(wp_unslash($_SERVER[ 'REQUEST_URI' ])) : '';

            foreach ($ids_to_perform as $id_action) {
                if ('delete' === $perform_acion) {

                    $where   = array('id' => $id_action);
                    $results = $abandoned_carts_queries->delete_db_query('flexi_email_rules', $where);

                    wp_safe_redirect(add_query_arg('record-' . $perform_acion, 'true', $request_uri));

                } elseif ('inactive' === $perform_acion) {

                    $parms   = array('status' => "inactive");
                    $where   = 'id = ' . $id_action;
                    $results = $abandoned_carts_queries->update_db_query('flexi_email_rules', $parms, $where);

                    $request_uri = isset($_SERVER[ 'REQUEST_URI' ]) ? esc_url_raw(wp_unslash($_SERVER[ 'REQUEST_URI' ])) : '';
                    wp_safe_redirect(add_query_arg('record-' . $perform_acion, 'true', $request_uri));

                } elseif ('active' === $perform_acion) {

                    $parms   = array('status' => "active");
                    $where   = 'id = ' . $id_action;
                    $results = $abandoned_carts_queries->update_db_query('flexi_email_rules', $parms, $where);

                    $request_uri = isset($_SERVER[ 'REQUEST_URI' ]) ? esc_url_raw(wp_unslash($_SERVER[ 'REQUEST_URI' ])) : '';
                    wp_safe_redirect(add_query_arg('record-' . $perform_acion, 'true', $request_uri));
                }
            }
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
    public function get_carts_rules_sets($per_page, $offset)
    {
        $abandoned_carts_queries = new Flexi_Database_Queries();

        $order   = isset($_GET[ 'order' ]) && in_array(strtoupper(wp_unslash($_GET[ 'order' ])), array('ASC', 'DESC'), true) ? strtoupper(wp_unslash($_GET[ 'order' ])) : 'DESC';
        $orderby = isset($_GET[ 'orderby' ]) ? sanitize_sql_orderby(wp_unslash($_GET[ 'orderby' ])) : 'id';

        $results = $abandoned_carts_queries->get_sorted_result('flexi_email_rules', '*', array(), $orderby, $order, $per_page, $offset);

        return is_array($results) ? $results : array();
    }
}

$aband_cart_recov_user_list_obj = new Flexi_Cart_Rule_Sets();
$aband_cart_recov_user_list_obj->prepare_items();