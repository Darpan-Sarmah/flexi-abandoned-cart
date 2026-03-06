<?php
$language                   = get_option('aban_cart_rec_language');
$flexi_carts_dbqueries      = new Flexi_Database_Queries();
$default_template_id        = get_option('acr_default_tempID') !== '' ? get_option('acr_default_tempID') : 1;
$default_template_coupon_id = 1; // Default coupon template
$default_template_cart_id   = 2; // Default abandon cart template

// ensure_active_temp($flexi_carts_dbqueries, $default_template_coupon_id, $default_template_cart_id);

if (isset($_GET[ 'panel' ]) && 'email-template-creation' === $_GET[ 'panel' ]) {
    if (isset($_SERVER[ 'REQUEST_METHOD' ]) && 'POST' === $_SERVER[ 'REQUEST_METHOD' ] && isset($_POST[ 'save_email_template' ])) {

        if (!isset($_POST[ 'cart_abandon_email_temp_data' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'cart_abandon_email_temp_data' ])), 'abandon_cart_email_template')) {
            wp_die(esc_html__('Nonce verification failed', 'flexi-abandon-cart-recovery'));
        }
        // Get form data.
        $temp_status   = isset($_POST[ 'status' ]) ? sanitize_text_field(wp_unslash($_POST[ 'status' ])) : '';
        $template_name = isset($_POST[ 'template_name' ]) ? sanitize_text_field(wp_unslash($_POST[ 'template_name' ])) : '';
        $template_type = isset($_POST[ 'template_type' ]) ? sanitize_text_field(wp_unslash($_POST[ 'template_type' ])) : '0';
        $email_subject = isset($_POST[ 'template_subject' ]) ? wp_kses_post(wp_unslash($_POST[ 'template_subject' ])) : '';
        $email_body    = isset($_POST[ 'email_temp_body' ]) ? wp_kses_post(wp_unslash($_POST[ 'email_temp_body' ])) : '';
        $coupon_status = isset($_POST[ 'coupon_status' ]) ? sanitize_text_field(wp_unslash($_POST[ 'coupon_status' ])) : '';

        // A/B testing and scheduling extra data.
        $extra_data_new = array(
            'ab_test_enabled' => !empty($_POST['ab_test_enabled']) ? 1 : 0,
            'ab_subject_b'    => isset($_POST['ab_subject_b']) ? sanitize_text_field(wp_unslash($_POST['ab_subject_b'])) : '',
            'send_days'       => isset($_POST['send_days']) ? array_map('absint', (array) $_POST['send_days']) : array(),
            'send_hour'       => isset($_POST['send_hour']) ? intval($_POST['send_hour']) : -1,
        );

        if ('on' === $coupon_status) {
            $coupon_type = isset($_POST[ 'coupon_type' ]) ? sanitize_text_field(wp_unslash($_POST[ 'coupon_type' ])) : '';

            $woocommerce_coupon_name = isset($_POST[ 'woocommerce_coupon_name' ]) ? sanitize_text_field(wp_unslash($_POST[ 'woocommerce_coupon_name' ])) : '';

            $flexi_coupon_name = isset($_POST[ 'flexi_coupon_name' ]) ? sanitize_text_field(wp_unslash($_POST[ 'flexi_coupon_name' ])) : '';

        }

        $coupon_name = isset($woocommerce_coupon_name) && ('' !== $woocommerce_coupon_name) ? $woocommerce_coupon_name : $flexi_coupon_name;

        $existing_active_template = $flexi_carts_dbqueries->select_db_query('flexi_email_templates', 'id , template_type', 'status = "on"');

        $parms = array(
            'temp_language' => $language,
            'template_name' => $template_name,
            'template_type' => $template_type,
            'email_subject' => $email_subject,
            'email_body'    => $email_body,
            'status'        => $temp_status,
            'coupon_status' => $coupon_status,
            'coupon_type'   => $coupon_type,
            'coupon_name'   => $coupon_name,
            'extra_data'    => wp_json_encode($extra_data_new),
        );

        // Check if new template is to be activated.
        if ('on' === $temp_status) {
            if (!empty($existing_active_template)) {
                $notice            = esc_html__('Another active email template already exists. The new template will be added but will not be activated.', 'abandoned-cart-recovery');
                $parms[ 'status' ] = '';
            } else {
                $parms[ 'status' ] = 'on';
                $notice            = esc_html__('Email Template Saved Successfully.', 'abandoned-cart-recovery');
            }
        } else {
            $parms[ 'status' ] = '';
            $notice            = esc_html__('Email Template Saved Successfully.', 'abandoned-cart-recovery');
        }

        // Insert new template data.
        $flexi_carts_dbqueries->insert_db_query('flexi_email_templates', $parms);
        // $notice      = esc_html__('Email Template Saved Successfully.', 'flexi-abandon-cart-recovery');
        $request_uri = isset($_SERVER[ 'REQUEST_URI' ]) ? esc_url_raw(wp_unslash($_SERVER[ 'REQUEST_URI' ])) : '';

        $redirect_url = add_query_arg('message', rawurlencode($notice), $request_uri);
        wp_safe_redirect($redirect_url);
        exit;
    }
} elseif (isset($_GET[ 'templateID' ]) && isset($_GET[ 'panel' ]) && 'edit' === $_GET[ 'panel' ]) {

    $template_id   = intval($_GET[ 'templateID' ]);
    $query_results = $flexi_carts_dbqueries->select_db_query('flexi_email_templates', '*', 'temp_language = "' . esc_sql($language) . '"', 'id = ' . $template_id);

    $form_data = $query_results[ 0 ];
    $heading   = 'Edit Template (' . esc_html($form_data[ 'template_name' ]) . ')';

    if (isset($_SERVER[ 'REQUEST_METHOD' ]) && 'POST' === $_SERVER[ 'REQUEST_METHOD' ] && isset($_POST[ 'save_email_template' ])) {
        if (!isset($_POST[ 'cart_abandon_email_temp_data' ]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ 'cart_abandon_email_temp_data' ])), 'abandon_cart_email_template')) {
            wp_die(esc_html__('Nonce verification failed', 'flexi-abandon-cart-recovery'));
        }

        // Get form data.
        $temp_status   = isset($_POST[ 'status' ]) ? sanitize_text_field(wp_unslash($_POST[ 'status' ])) : '';
        $template_name = isset($_POST[ 'template_name' ]) ? sanitize_text_field(wp_unslash($_POST[ 'template_name' ])) : '';
        $template_type = isset($_POST[ 'template_type' ]) ? sanitize_text_field(wp_unslash($_POST[ 'template_type' ])) : '0';
        $email_subject = isset($_POST[ 'template_subject' ]) ? wp_kses_post(wp_unslash($_POST[ 'template_subject' ])) : '';
        $email_body    = isset($_POST[ 'email_temp_body' ]) ? wp_kses_post(wp_unslash($_POST[ 'email_temp_body' ])) : '';
        $coupon_status = isset($_POST[ 'coupon_status' ]) ? sanitize_text_field(wp_unslash($_POST[ 'coupon_status' ])) : '';

        // A/B testing and scheduling extra data.
        $extra_data_new = array(
            'ab_test_enabled' => !empty($_POST['ab_test_enabled']) ? 1 : 0,
            'ab_subject_b'    => isset($_POST['ab_subject_b']) ? sanitize_text_field(wp_unslash($_POST['ab_subject_b'])) : '',
            'send_days'       => isset($_POST['send_days']) ? array_map('absint', (array) $_POST['send_days']) : array(),
            'send_hour'       => isset($_POST['send_hour']) ? intval($_POST['send_hour']) : -1,
        );

        if ('on' === $coupon_status) {
            $coupon_type = isset($_POST[ 'coupon_type' ]) ? sanitize_text_field(wp_unslash($_POST[ 'coupon_type' ])) : '';

            $woocommerce_coupon_name = isset($_POST[ 'woocommerce_coupon_name' ]) ? sanitize_text_field(wp_unslash($_POST[ 'woocommerce_coupon_name' ])) : '';
            $flexi_coupon_name       = isset($_POST[ 'flexi_coupon_name' ]) ? sanitize_text_field(wp_unslash($_POST[ 'flexi_coupon_name' ])) : '';
        }

        $coupon_name = isset($woocommerce_coupon_name) && ('' !== $woocommerce_coupon_name) ? $woocommerce_coupon_name : $flexi_coupon_name;
        $parms       = array(
            'temp_language' => $language,
            'template_name' => $template_name,
            'template_type' => $template_type,
            'email_subject' => $email_subject,
            'email_body'    => $email_body,
            'status'        => $temp_status,
            'coupon_status' => $coupon_status,
            'coupon_type'   => $coupon_type,
            'coupon_name'   => $coupon_name,
            'extra_data'    => wp_json_encode($extra_data_new),
        );
        $where = ('id =' . $template_id);
        if ('on' === $temp_status) {

            $flexi_carts_dbqueries->update_db_query('flexi_email_templates', array('status' => ''), 'template_type = "' . esc_sql($template_type) . '" AND id != ' . intval($template_id));
            $flexi_carts_dbqueries->update_db_query('flexi_email_templates', $parms, 'id = ' . intval($template_id));
            ensure_active_temp($flexi_carts_dbqueries, $default_template_coupon_id, $default_template_cart_id);
        } else {
            $flexi_carts_dbqueries->update_db_query('flexi_email_templates', $parms, 'id = ' . intval($template_id));
        }

        if ((intval($default_template_coupon_id) === $template_id || intval($default_template_cart_id) === $template_id) && "" === $temp_status) {
            $temp_newstatus = array('status' => 'on');
            $new_params     = array_merge($parms, $temp_newstatus);
            $flexi_carts_dbqueries->update_db_query('flexi_email_templates', $new_params, 'id = ' . intval($template_id));
            $notice = esc_html__('Email Template Updated Successfully But Status Cannot Be Changed.', 'flexi-abandon-cart-recovery');
        } else {
            $notice = esc_html__('Email Template Updated Successfully.', 'flexi-abandon-cart-recovery');
        }

        //     if (intval($default_template_id) === $template_id && "" === $temp_status) {

        //         $temp_newstatus = array('status' => 'on');
        //         $new_params     = array_merge($parms, $temp_newstatus);
        //         $flexi_carts_dbqueries->update_db_query('flexi_email_templates', $new_params, $where);
        //         $notice = esc_html__('Email Template Updated Successfully But Status Cannot Be Changed.', 'flexi-abandon-cart-recovery');
        //     } else {
        //         $notice = esc_html__('Email Template Updated Successfully.', 'flexi-abandon-cart-recovery');
        //         $flexi_carts_dbqueries->update_db_query('flexi_email_templates', $parms, $where);
        //         ensure_active_temp($flexi_carts_dbqueries, $default_template_id);
        //     }

        $request_uri  = isset($_SERVER[ 'REQUEST_URI' ]) ? esc_url_raw(wp_unslash($_SERVER[ 'REQUEST_URI' ])) : '';
        $redirect_url = add_query_arg('message', rawurlencode($notice), $request_uri);
        wp_safe_redirect($redirect_url);
        exit;
    }
}

function ensure_active_temp($flexi_carts_dbqueries, $default_template_coupon_id, $default_template_cart_id)
{
    // Check if there's any active template for each type.
    $active_coupon_template = $flexi_carts_dbqueries->select_db_query('flexi_email_templates', '*', 'status = "on" AND template_type = 0');
    $active_cart_template   = $flexi_carts_dbqueries->select_db_query('flexi_email_templates', '*', 'status = "on" AND template_type = 1');

    // Activate default templates if no active template exists for each type.
    if (empty($active_coupon_template)) {
        $flexi_carts_dbqueries->update_db_query('flexi_email_templates', array('status' => 'on'), ('id =' . $default_template_coupon_id));
    }

    if (empty($active_cart_template)) {
        $flexi_carts_dbqueries->update_db_query('flexi_email_templates', array('status' => 'on'), ('id =' . $default_template_cart_id));
    }
}

$lang_disp_name = 'en_US' === $language ? esc_html__('English', 'flexi-abandon-cart-recovery') : esc_html__('Italian', 'flexi-abandon-cart-recovery');
$default_body   = '<p>This is the default email body for ' . $lang_disp_name . '</p>';
$initial_data   = isset($form_data[ 'email_body' ])
? wp_kses_post($form_data[ 'email_body' ])
: wp_kses_post($default_body);

$editor_id = 'email_temp_body';
$settings  = array(
    'acr_email_temp_body',
    array(
        'media_buttons' => true,
        'textarea_rows' => 8,
        'tabindex'      => 4,
        'tinymce'       => array(
            'theme_advanced_buttons1' => 'bold,italic,underline,|,bullist,numlist,blockquote,|,link,unlink,|,spellchecker,fullscreen,|,formatselect,styleselect',
        ),
    ),
);
?>

<div class="wrap">
    <div id="message_custom"></div>
    <?php
if (isset($_GET[ 'message' ]) && sanitize_text_field(wp_unslash($_GET[ 'message' ]))) {
    $message = sanitize_text_field(wp_unslash($_GET[ 'message' ]));
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
}
?>
    <h1><?php
echo isset($heading) ? esc_html($heading) : esc_html__('Add New Template', 'flexi-abandon-cart-recovery'); ?>
    </h1>

    <form method="POST" id="form_template">
        <?php wp_nonce_field('abandon_cart_email_template', 'cart_abandon_email_temp_data');?>
        <table class="form-table">
            <tr>
                <th><?php echo esc_html__('Status', 'flexi-abandon-cart-recovery'); ?></th>
                <td>
                    <label for="status">
                        <?php
$temp_active = isset($form_data[ 'status' ]) ? $form_data[ 'status' ] : '';
$checked     = ('on' === $temp_active) ? 'checked' : '';
$temp_id     = isset($template_id) ? $template_id : '';
?>
                        <input name="status" type="checkbox" id="status" <?php echo esc_attr($checked); ?>>
                        <input type="hidden" id="template_id" value="<?php echo esc_attr($temp_id); ?>">
                        <?php echo esc_html__('Active', 'flexi-abandon-cart-recovery'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label
                        for="template_name"><?php echo esc_html__('Template Name', 'flexi-abandon-cart-recovery'); ?></label>
                </th>
                <td>
                    <input name="template_name" type="text" id="template_name" class="regular-text"
                        value="<?php echo isset($form_data[ 'template_name' ]) ? esc_attr($form_data[ 'template_name' ]) : ''; ?>"
                        required>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label
                        for="template_type"><?php echo esc_html__('Template Type', 'flexi-abandon-cart-recovery'); ?></label>
                </th>
                <td>
                    <?php $template_type = isset($form_data[ 'template_type' ]) ? $form_data[ 'template_type' ] : 0?>
                    <select name="template_type" id="template_type" class="template_type" required>
                        <option value="0" <?php selected($template_type, 0);?> > <?php echo esc_html__('Coupon Template', 'flexi-abandon-cart-recovery'); ?></option>
                        <option value="1" <?php selected($template_type, 1);?>> <?php echo esc_html__('Cart Recovery Template', 'flexi-abandon-cart-recovery'); ?></option>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label
                        for="template_subject"><?php echo esc_html__('Enter Email Subject Or Select Any Shortcode', 'flexi-abandon-cart-recovery'); ?></label>
                </th>
                <td>
                    <input name="template_subject" type="text" id="template_subject" class="regular-text"
                        value="<?php echo isset($form_data[ 'email_subject' ]) ? esc_attr($form_data[ 'email_subject' ]) : ''; ?>"
                        required>
                    &nbsp; &nbsp;
                    <select id="shortcode_dropdown" class="shortcode-dropdown">
                        <option value=""><?php echo esc_html__('Select Shortcode', 'flexi-abandon-cart-recovery'); ?>
                        </option>
                        <option value="{{user_email}}">
                            <?php echo esc_html__('Customer Email', 'flexi-abandon-cart-recovery'); ?></option>
                        <option value="{{user_lastname}}">
                            <?php echo esc_html__('Customer Last Name', 'flexi-abandon-cart-recovery'); ?></option>
                        <option value="{{user_firstname}}">
                            <?php echo esc_html__('Customer First Name', 'flexi-abandon-cart-recovery'); ?></option>
                        <option value="{{site_url}}">
                            <?php echo esc_html__('Site Url', 'flexi-abandon-cart-recovery'); ?></option>
                        <option value="{{product_name}}">
                            <?php echo esc_html__('Carts Products', 'flexi-abandon-cart-recovery'); ?></option>
                        <option value="{{email_from}}">
                            <?php echo esc_html__('From Email', 'flexi-abandon-cart-recovery'); ?></option>
                        <option value="{{email_name}}">
                            <?php echo esc_html__('From Name', 'flexi-abandon-cart-recovery'); ?></option>
                        <option value="{{total_cost}}">
                            <?php echo esc_html__('Total Cost', 'flexi-abandon-cart-recovery'); ?></option>
                        <option value="{{coupon_code}}">
                            <?php echo esc_html__('Coupon Code', 'flexi-abandon-cart-recovery'); ?></option>
                        <option value="{{cart_link}}">
                            <?php echo esc_html__('Cart Link', 'flexi-abandon-cart-recovery'); ?></option>
                        <option value="{{coupon_discount}}">
                            <?php echo esc_html__('Coupon Discount', 'flexi-abandon-cart-recovery'); ?></option>
                        <option value="{{unsubscribe_url}}">
                            <?php echo esc_html__('Unsubscribe Link (GDPR)', 'flexi-abandon-cart-recovery'); ?></option>

                    </select>
                </td>
            </tr>
            <!-- A/B Testing for Subject Line -->
            <tr>
                <th scope="row">
                    <label for="ab_test_enabled"><?php echo esc_html__('A/B Test Subject Line', 'flexi-abandon-cart-recovery'); ?></label>
                </th>
                <td>
                    <?php
$extra_data_raw = isset($form_data['extra_data']) ? $form_data['extra_data'] : '';
$extra_data     = !empty($extra_data_raw) ? json_decode($extra_data_raw, true) : array();
if ( ! is_array($extra_data) ) {
    $extra_data = array();
}
$ab_enabled    = !empty($extra_data['ab_test_enabled']) ? 'checked' : '';
$ab_subject_b  = isset($extra_data['ab_subject_b']) ? esc_attr($extra_data['ab_subject_b']) : '';
?>
                    <label>
                        <input type="checkbox" name="ab_test_enabled" id="ab_test_enabled" value="1" <?php echo esc_attr($ab_enabled); ?>>
                        <?php echo esc_html__('Enable A/B Testing', 'flexi-abandon-cart-recovery'); ?>
                    </label>
                </td>
            </tr>
            <tr id="ab_test_row" style="<?php echo $ab_enabled ? '' : 'display:none;'; ?>">
                <th scope="row">
                    <label for="ab_subject_b"><?php echo esc_html__('Subject Line B (Variant)', 'flexi-abandon-cart-recovery'); ?></label>
                </th>
                <td>
                    <input name="ab_subject_b" type="text" id="ab_subject_b" class="regular-text"
                        value="<?php echo $ab_subject_b; ?>"
                        placeholder="<?php echo esc_attr__('Alternative subject line for 50% of recipients', 'flexi-abandon-cart-recovery'); ?>">
                    <p class="description"><?php echo esc_html__('50% of recipients will receive subject A, 50% will receive subject B. The better-performing variant is tracked in email logs.', 'flexi-abandon-cart-recovery'); ?></p>
                </td>
            </tr>
            <!-- Send Schedule -->
            <tr>
                <th scope="row">
                    <label for="send_days"><?php echo esc_html__('Send On Days', 'flexi-abandon-cart-recovery'); ?></label>
                </th>
                <td>
                    <?php
$send_days = isset($extra_data['send_days']) ? (array) $extra_data['send_days'] : array();
$days      = array(
    '1' => __('Monday', 'flexi-abandon-cart-recovery'),
    '2' => __('Tuesday', 'flexi-abandon-cart-recovery'),
    '3' => __('Wednesday', 'flexi-abandon-cart-recovery'),
    '4' => __('Thursday', 'flexi-abandon-cart-recovery'),
    '5' => __('Friday', 'flexi-abandon-cart-recovery'),
    '6' => __('Saturday', 'flexi-abandon-cart-recovery'),
    '7' => __('Sunday', 'flexi-abandon-cart-recovery'),
);
foreach ($days as $day_num => $day_name) {
    $checked_day = in_array($day_num, $send_days, true) ? 'checked' : '';
    echo '<label style="margin-right:10px;"><input type="checkbox" name="send_days[]" value="' . esc_attr($day_num) . '" ' . esc_attr($checked_day) . '> ' . esc_html($day_name) . '</label>';
}
?>
                    <p class="description"><?php echo esc_html__('Leave all unchecked to send any day.', 'flexi-abandon-cart-recovery'); ?></p>
                </td>
            </tr>
            <!-- Time Zone Aware Scheduling -->
            <tr>
                <th scope="row">
                    <label for="send_hour"><?php echo esc_html__('Preferred Send Hour', 'flexi-abandon-cart-recovery'); ?></label>
                </th>
                <td>
                    <?php $send_hour = isset($extra_data['send_hour']) ? intval($extra_data['send_hour']) : -1; ?>
                    <select name="send_hour" id="send_hour">
                        <option value="-1" <?php selected($send_hour, -1); ?>><?php echo esc_html__('Any hour', 'flexi-abandon-cart-recovery'); ?></option>
                        <?php for ($h = 0; $h <= 23; $h++) { ?>
                        <option value="<?php echo esc_attr($h); ?>" <?php selected($send_hour, $h); ?>>
                            <?php echo esc_html(sprintf('%02d:00', $h)); ?>
                        </option>
                        <?php } ?>
                    </select>
                    <p class="description"><?php echo esc_html__('Schedule emails to send at a specific hour (site timezone). Use -1 for no restriction.', 'flexi-abandon-cart-recovery'); ?></p>
                </td>
            </tr>
            <tr id="coupon_status_container">
                <th><?php echo esc_html__('Add Coupon', 'flexi-abandon-cart-recovery'); ?></th>
                <td>
                    <label for="coupon_status">
                        <?php
$coupon_status = isset($form_data[ 'coupon_status' ]) ? $form_data[ 'coupon_status' ] : '';
$checked       = ('on' === $coupon_status) ? 'checked' : '';
?>
                        <input name="coupon_status" type="checkbox" id="coupon_status"
                            <?php echo esc_attr($checked); ?>>
                    </label>
                </td>
            <tr id="coupon_details" style="display:none;">
                <th scope="row">
                    <label
                        for="coupon_type"><?php echo esc_html__('Select Coupon Type', 'flexi-abandon-cart-recovery'); ?></label>
                </th>
                <td>
                    <div>
                        <?php $coupon_type = isset($form_data[ 'coupon_type' ]) ? $form_data[ 'coupon_type' ] : '';?>
                        <input type="radio" id="woo_coupon" name="coupon_type" value="woocommerce_coupon"
                            <?php echo esc_attr('woocommerce_coupon' === $coupon_type ? 'checked' : ''); ?>>
                        <label for="woo_coupon">Woocommerce Coupon</label>
                        &nbsp; &nbsp;
                        <input type="radio" id="flexi_dynamic_coupon" name="coupon_type" value="flexi_dynamic_coupon"
                            <?php echo esc_attr('flexi_dynamic_coupon' === $coupon_type ? 'checked' : ''); ?>>
                        <label for="flexi_dynamic_coupon">Flexi Dynamic Coupon</label>
                    </div>
                    <br>
                    <br>
                    <div class="woocommerce_coupon_lists" style="display:none ;">
                        <?php
$args = array(
    'posts_per_page' => -1,
    'post_type'      => 'shop_coupon',
    'post_status'    => 'publish',
);

$coupons = get_posts($args);
?>
                        <select name="woocommerce_coupon_name" class="coupon-dropdown">
                            <option value=""> --SELECT--</option>
                            <?php if ($coupons) {?>
                            <?php foreach ($coupons as $coupon) {
    $is_selected = selected($form_data[ 'coupon_name' ] ?? '', $coupon->post_title, false);
    ?>
                            <option value="<?php echo esc_attr($coupon->post_title); ?>" <?php echo $is_selected; ?>>
                                <?php echo esc_html($coupon->post_title); ?>
                            </option>
                            <?php
}}?>
                        </select>
                    </div>

                    <div class="flexi_coupon_lists" style="display:none;">
                        <?php
$flexi_coupons = $flexi_carts_dbqueries->select_db_query('flexi_cart_coupons', 'name_code');

if (!empty($flexi_coupons)) {
    ?>
                        <select name="flexi_coupon_name" class="coupon-dropdown">
                            <option value=""> --SELECT--</option>
                            <?php
foreach ($flexi_coupons as $coupon) {
        $is_selected = selected($form_data[ 'coupon_name' ] ?? '', $coupon[ 'name_code' ], false);
        echo '<option value="' . esc_attr($coupon[ 'name_code' ]) . '" ' . $is_selected . '>' . esc_html($coupon[ 'name_code' ]) . '</option>';
    }
    ?>
                        </select>
                        <?php
} else {
    echo "<p>" . esc_html__('No Coupons Exists', 'flexi-abandon-cart-recovery') . "</p>";
}
?>
                    </div>

                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label
                        for="email_temp_body"><?php echo esc_html__('Email Body', 'flexi-abandon-cart-recovery'); ?></label>
                </th>
                <td>
                    <?php
wp_editor($initial_data, $editor_id, $settings);
?>
                </td>
            </tr>
        </table>

        <?php $pagetab = isset($_GET[ 'panel' ]) ? sanitize_text_field(wp_unslash($_GET[ 'panel' ])) : '';?>
        <button name="save_email_template" id="save_new_template" class="button button-primary" type="submit"
            value="<?php echo esc_attr__('Save Template', 'flexi-abandon-cart-recovery'); ?>"
            data-page="<?php echo esc_attr($pagetab); ?>">
            <?php esc_html_e('Save Template', 'flexi-abandon-cart-recovery');?>
        </button>
    </form>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
    // A/B test row toggle.
    $('#ab_test_enabled').on('change', function() {
        if ($(this).is(':checked')) {
            $('#ab_test_row').show();
        } else {
            $('#ab_test_row').hide();
        }
    });
});
</script>