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
 * Flexi_Cart_Email_Triggers
 *
 * @since 1.0.0
 */
class Flexi_Cart_Email_Triggers extends WP_List_Table
{

    /**
     * Flexi_Cart_Email_Triggers constructor
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct(
            array(
                'singular' => __('Email Trigger', 'flexi-abandon-cart-recovery'),
                'plural'   => __('Email Triggers', 'flexi-abandon-cart-recovery'),
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

        $this->items = self::get_carts_triggers($per_page, $offset);
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
        esc_html_e('No Triggers For Carts Found.', 'flexi-abandon-cart-recovery');
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
            'cb'            => '<input type="checkbox">',
            'trigger_name'  => __('Trigger Name', 'flexi-abandon-cart-recovery'),
            'template_name' => __('Template Name', 'flexi-abandon-cart-recovery'),
            'rule_name'     => __('Rule Name', 'flexi-abandon-cart-recovery'),
            'send_after'    => __('Send Email After', 'flexi-abandon-cart-recovery'),
            'status'        => __('Status', 'flexi-abandon-cart-recovery'),
            'edit_action'   => __('Action ', 'flexi-abandon-cart-recovery'),
        );
        return apply_filters('flexi_cart_triggers_columns', $columns);
    }

    /**
     * Displays the rule name column
     *
     * @param  array $item Item.
     * @return string HTML for rule name.
     */
    public function column_trigger_name($item)
    {
        return esc_html($item[ 'trigger_name' ]);
    }

    /**
     * Displays the template_name column
     *
     * @param  array $item Item.
     * @return string HTML for Template name.
     */
    public function column_template_name($item)
    {
        return esc_html($item[ 'trigger_temp_name' ]);
    }

    /**
     * Displays the rule type column
     *
     * @param  array $item Item.
     * @return string HTML for rule type.
     */
    public function column_rule_name($item)
    {
        return esc_html($item[ 'trigger_rule' ]);
    }

    /**
     * Displays the send after column
     *
     * @param  array $item Item.
     * @return string HTML for send after.
     */
    public function column_send_after($item)
    {
        $span = $item[ 'send_after_span' ];
        return esc_html($item[ 'send_after_num' ] . " " . ucfirst($span));
    }

    /**
     * Displays the rule status column
     *
     * @param  array $item Item.
     * @return string HTML for rule status.
     */
    public function column_status($item)
    {

        if ("1" === $item[ 'status' ]) {
            $status = 'Active';
        } else {
            $status = 'Inactive';
        }
        return esc_html($status);
    }

    public function column_edit_action($item)
    {
        $request_page    = isset($_REQUEST[ 'page' ]) ? sanitize_text_field(wp_unslash($_REQUEST[ 'page' ])) : '';
        $request_section = isset($_REQUEST[ 'section' ]) ? sanitize_text_field(wp_unslash($_REQUEST[ 'section' ])) : '';

        $action = sprintf(
            '<a href="?page=%s&section=%s&tab=email-trigger&triggerID=%s&#open-edit-modal" target="_self">Edit</a>',
            esc_attr($request_page),
            esc_attr($request_section),
            esc_attr($item[ 'id' ])
        );
        $html = "<p>" . $action . "</p>";

        return $html;
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
            'send_after'    => array('send_after_num', false),
            'trigger_name'  => array('trigger_name', false),
            'template_name' => array('trigger_temp_name', false),
            'rule_name'     => array('trigger_rule', false),
        );
    }

    /**
     * Gets the template.
     */
    public function get_template_list()
    {
        include_once FLEXI_ABANDON_CART_RECOVERY_DIR . 'admin/partials/class-flexi-database-queries.php';
        $abandoned_carts_queries = new Flexi_Database_Queries();
        $query_results           = $abandoned_carts_queries->select_db_query('flexi_email_templates', 'id , template_name' , 'status = "on"');
        return $query_results;
    }

     /**
     * Gets the template.
     */
    public function get_rule_set_list()
    {
        include_once FLEXI_ABANDON_CART_RECOVERY_DIR . 'admin/partials/class-flexi-database-queries.php';
        $abandoned_carts_queries = new Flexi_Database_Queries();
        $query_results           = $abandoned_carts_queries->select_db_query('flexi_email_rules', 'id , rule_name' , 'status = "active"');
        return $query_results;
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

        $request_page    = isset($_REQUEST[ 'page' ]) ? sanitize_text_field(wp_unslash($_REQUEST[ 'page' ])) : '';
        $request_section = isset($_REQUEST[ 'section' ]) ? sanitize_text_field(wp_unslash($_REQUEST[ 'section' ])) : '';

        $action = sprintf("?page=%s&section=%s&tab=email-trigger&#open-modal",
            esc_attr($request_page),
            esc_attr($request_section)
        );
        ?>
    <div class="append_notice"></div>
    <br>
    <!-- Action Button -->
    <div class="alignleft">
        <a href='<?php echo $action; ?>' target='_self' id="display_trigger_form" class="button alignleft">
            <?php esc_html_e('Add Trigger', 'flexi-abandon-cart-recovery');?>
        </a>
    </div>


    <div class="clear"></div>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div class="meta-box-sortables ui-sortable">
                <form method="post">
                    <?php
wp_nonce_field('flexi_triggers_lists', 'flexi_triggers_listsactions');
        $this->display();
        ?>
                </form>
            </div>
        </div>
    </div>

    <div class="clear"></div>
</div>

<?php
if (isset($_GET[ 'triggerID' ]) && sanitize_text_field(wp_unslash($_GET[ 'triggerID' ]))) {
            $triggerID = sanitize_text_field($_GET[ 'triggerID' ]);

            $triggerID_data = $this->get_trigger_id_data($triggerID);

            $checked       = ('active' === $triggerID_data || "1" === $triggerID_data[ 'status' ]) ? "checked" : "";
            $template_name = isset($triggerID_data[ 'trigger_name' ]) ? $triggerID_data[ 'trigger_name' ] : "";
            ?>
<div id="open-edit-modal" class="modal-window">
    <div class="modal-content">
        <div class="modal-header">
            <h2><?php esc_html_e('Edit Trigger', 'flexi-abandon-cart-recovery');?></h2>
            <a href="#" title="Close" id="modal_close" class="modal-close">X</a>
            <div class="append_notice_error"></div>
        </div>
        <div class="modal-body">
            <form id="trigger_form" method="post">
                <table>
                    <tr>
                        <input type="hidden" name="trigger_id" value="<?php echo $triggerID ;?>" >
                        <th>
                            <label
                                for="status"><?php echo esc_html__('Status', 'flexi-abandon-cart-recovery'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="status" value="active" name="status" <?php echo $checked; ?>>
                            <?php echo esc_html__('Active', 'flexi-abandon-cart-recovery'); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label
                                for="trigger_name"><?php echo esc_html__('Trigger Name', 'flexi-abandon-cart-recovery'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="trigger_name" name="trigger_name"
                                value="<?php echo $template_name; ?>" required>
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label
                                for="template_name"><?php echo esc_html__('Template Name', 'flexi-abandon-cart-recovery'); ?></label>
                        </th>
                        <td>
                            <select id="template_name" name="template_name">
                                <?php
$templates = $this->get_template_list();
            if (!empty($templates)) {
                foreach ($templates as $template) {

                    $selected = ($triggerID_data[ 'trigger_temp_name' ] === $template[ 'template_name' ]) ? "selected" : "";
                    echo '<option value="' . esc_attr($template[ 'template_name' ]) . '" data-tempId= "' . esc_attr($template[ 'id' ]) . '"' . $selected . '>' . esc_html($template[ 'template_name' ]) . '</option>';
                }
            } else {
                echo '<option value="">' . esc_html__('No Templates found', 'flexi-abandon-cart-recovery') . '</option>';
            }
            ?>
                            </select>

                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label
                                for="rule_name"><?php echo esc_html__('Rule Name', 'flexi-abandon-cart-recovery'); ?></label>
                        </th>
                        <td>
                        <select id="rule_name" name="rule_name">
    <?php
    $rules = $this->get_rule_set_list();
    if (!empty($rules)) {
        foreach ($rules as $rule) {

            $selected = (isset($triggerID_data['trigger_rule']) && $triggerID_data['trigger_rule'] === $rule['rule_name']) ? "selected" : "";

            echo '<option value="' . esc_attr($rule['rule_name']) . '" data-tempId="' . esc_attr($rule['id']) . '" ' . $selected . '>' . esc_html($rule['rule_name']) . '</option>';
        }
    } else {
        echo '<option value="">' . esc_html__('No Rules found', 'flexi-abandon-cart-recovery') . '</option>';
    }
    ?>
</select>

                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label
                                for="send_after"><?php echo esc_html__('Send Email After', 'flexi-abandon-cart-recovery'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="send_after_num" id="send_after_num"
                                value="<?php echo isset($triggerID_data[ 'send_after_num' ]) ? $triggerID_data[ 'send_after_num' ] : "" ?>">

                            <select id="send_after" name="send_after_span" id="send_after_span">
                                <option value="minute"
                                    <?php echo (isset($triggerID_data[ 'send_after_span' ]) && 'minutes' === $triggerID_data[ 'send_after_span' ]) ? 'selected' : ''; ?>>
                                    <?php echo esc_html__('minute(s)', 'flexi-abandon-cart-recovery'); ?></option>
                                <option value="hours"
                                    <?php echo (!isset($triggerID_data[ 'send_after_span' ]) || 'hours' === $triggerID_data[ 'send_after_span' ]) ? 'selected' : ''; ?>>
                                    <?php echo esc_html__('hour(s)', 'flexi-abandon-cart-recovery'); ?></option>

                                <option value="days"
                                    <?php echo (isset($triggerID_data[ 'send_after_span' ]) && 'days' === $triggerID_data[ 'send_after_span' ]) ? 'selected' : ''; ?>>
                                    <?php echo esc_html__('day(s)', 'flexi-abandon-cart-recovery'); ?>
                                </option>
                            </select>

                        </td>
                    </tr>


                </table>
            </form>
        </div>
        <div class="modal-footer">
            <button class="button button-primary" type="submit" id="flexi_save_trigger"
                name="save_trigger">Update</button>

        </div>
    </div>
    <?php
}?>

    <div id="open-modal" class="modal-window">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php esc_html_e('Add Trigger', 'flexi-abandon-cart-recovery');?></h2>
                <a href="#" title="Close" id="modal_close" class="modal-close">X</a>
                <div class="append_notice_error"></div>
            </div>
            <div class="modal-body">
                <form id="trigger_form" method="post">
                    <table>
                        <tr>
                            <th>
                                <label
                                    for="status"><?php echo esc_html__('Status', 'flexi-abandon-cart-recovery'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="status" value="active" name="status">
                                <?php echo esc_html__('Active', 'flexi-abandon-cart-recovery'); ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label
                                    for="trigger_name"><?php echo esc_html__('Trigger Name', 'flexi-abandon-cart-recovery'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="trigger_name" name="trigger_name" required>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label
                                    for="template_name"><?php echo esc_html__('Template Name', 'flexi-abandon-cart-recovery'); ?></label>
                            </th>
                            <td>
                                <select id="template_name" name="template_name">
                                    <?php
$templates = $this->get_template_list();
        if (!empty($templates)) {
            foreach ($templates as $template) {

                echo '<option value="' . esc_attr($template[ 'template_name' ]) . '" data-tempId= "' . esc_attr($template[ 'id' ]) . '"' . $selected . '>' . esc_html($template[ 'template_name' ]) . '</option>';
            }
        } else {
            echo '<option value="">' . esc_html__('No Templates found', 'flexi-abandon-cart-recovery') . '</option>';
        }
        ?>
                                </select>

                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label
                                    for="rule_name"><?php echo esc_html__('Rule Name', 'flexi-abandon-cart-recovery'); ?></label>
                            </th>
                            <td>
                                <select id="rule_name" name="rule_name">
                                    <?php
$rules = $this->get_rule_set_list();
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                echo '<option value="' . esc_attr($rule[ 'rule_name' ]) . '" data-ruleId= "' . esc_attr($rule[ 'id' ]) . '" ' . $selected . '>' . esc_html($rule[ 'rule_name' ]) . '</option>';
            }
        } else {
            echo '<option value="">' . esc_html__('No Rules found', 'flexi-abandon-cart-recovery') . '</option>';
        }
        ?>
                                </select>

                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label
                                    for="send_after"><?php echo esc_html__('Send Email After', 'flexi-abandon-cart-recovery'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="send_after_num" id="send_after_num">

                                <select id="send_after" name="send_after_span" id="send_after_span">
                                    <option value="minute">
                                        <?php echo esc_html__('minute(s)', 'flexi-abandon-cart-recovery'); ?></option>
                                    <option value="hours">
                                        <?php echo esc_html__('hour(s)', 'flexi-abandon-cart-recovery'); ?></option>

                                    <option value="days">
                                        <?php echo esc_html__('day(s)', 'flexi-abandon-cart-recovery'); ?>
                                    </option>
                                </select>

                            </td>
                        </tr>


                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button class="button button-primary" type="submit" id="flexi_save_trigger"
                    name="save_trigger">Save</button>
            </div>
        </div>
    </div>
</div>

<?php
}

    private function get_trigger_id_data($triggerID)
    {
        $abandoned_carts_queries = new Flexi_Database_Queries();
        $trigger_data            = $abandoned_carts_queries->select_db_query('flexi_email_triggers', '*', 'id =' . $triggerID);

        return $trigger_data[ 0 ];
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
                    'tab'     => 'email-trigger',
                    'notice'  => rawurlencode($notice),
                ),
                admin_url('admin.php')
            );
            wp_redirect($redirect_url);
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
                    if (!isset($_POST[ 'flexi_triggers_listsactions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'flexi_triggers_listsactions' ])), 'flexi_triggers_lists')) {
                        wp_die(esc_html__('Nonce verification failed!', 'flexi-abandon-cart-recovery'));
                    }
                    $this->bulk_action_perform('delete');
                    break;
                case 'bulk-inactive':
                    if (!isset($_POST[ 'flexi_triggers_listsactions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'flexi_triggers_listsactions' ])), 'flexi_triggers_lists')) {
                        wp_die(esc_html__('Nonce verification failed!', 'flexi-abandon-cart-recovery'));
                    }
                    $this->bulk_action_perform('inactive');
                    break;
                case 'bulk-active':
                    if (!isset($_POST[ 'flexi_triggers_listsactions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'flexi_triggers_listsactions' ])), 'flexi_triggers_lists')) {
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

        if (!empty($_POST[ 'row_ids' ])) {
            if (!isset($_POST[ 'flexi_triggers_listsactions' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'flexi_triggers_listsactions' ])), 'flexi_triggers_lists')) {
                wp_die(esc_html__('Nonce verification failed!', 'flexi-abandon-cart-recovery'));
            }

            $abandoned_carts_queries = new Flexi_Database_Queries();
            $ids_to_perform          = array_map('sanitize_text_field', wp_unslash($_POST[ 'row_ids' ]));
            $request_uri             = isset($_SERVER[ 'REQUEST_URI' ]) ? esc_url_raw(wp_unslash($_SERVER[ 'REQUEST_URI' ])) : '';

            foreach ($ids_to_perform as $id_action) {
                if ('delete' === $perform_acion) {

                    $where   = array('id' => $id_action);
                    $results = $abandoned_carts_queries->delete_db_query('flexi_email_triggers', $where);

                    wp_safe_redirect(add_query_arg('record-' . $perform_acion, 'true', $request_uri));

                } elseif ('inactive' === $perform_acion) {

                    $parms   = array('status' => "0");
                    $where   = 'id = ' . $id_action;
                    $results = $abandoned_carts_queries->update_db_query('flexi_email_triggers', $parms, $where);

                    $request_uri = isset($_SERVER[ 'REQUEST_URI' ]) ? esc_url_raw(wp_unslash($_SERVER[ 'REQUEST_URI' ])) : '';
                    wp_safe_redirect(add_query_arg('record-' . $perform_acion, 'true', $request_uri));

                } elseif ('active' === $perform_acion) {

                    $parms   = array('status' => "1");
                    $where   = 'id = ' . $id_action;
                    $results = $abandoned_carts_queries->update_db_query('flexi_email_triggers', $parms, $where);

                    var_dump($results);

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
    public function get_carts_triggers($per_page, $offset)
    {
        $abandoned_carts_queries = new Flexi_Database_Queries();

        $order   = isset($_GET[ 'order' ]) && in_array(strtoupper(wp_unslash($_GET[ 'order' ])), array('ASC', 'DESC'), true) ? strtoupper(wp_unslash($_GET[ 'order' ])) : 'ASC';
        $orderby = isset($_GET[ 'orderby' ]) ? sanitize_sql_orderby(wp_unslash($_GET[ 'orderby' ])) : 'id';

        $results = $abandoned_carts_queries->get_sorted_result('flexi_email_triggers', '*', array(), $orderby, $order, $per_page, $offset);

        return is_array($results) ? $results : array();
    }

}

$aband_cart_recov_user_list_obj = new Flexi_Cart_Email_Triggers();
$aband_cart_recov_user_list_obj->get_rule_set_list();
$aband_cart_recov_user_list_obj->prepare_items();
