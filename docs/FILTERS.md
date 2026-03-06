# WordPress Filters — Flexi Abandon Cart Recovery

This document lists all WordPress filter hooks that Flexi Abandon Cart Recovery registers.

## Table of Contents

- [Filters Applied to WordPress / WooCommerce](#filters-applied-to-wordpress--woocommerce)
- [Admin Filters](#admin-filters)
- [Public / Storefront Filters](#public--storefront-filters)

---

## Filters Applied to WordPress / WooCommerce

### `cron_schedules`

**Callback:** `Flexi_Abandon_Cart_Recovery_Admin::flexi_add_custom_scheduler`
**Priority:** 10
**Arguments:** 1

Adds custom cron schedule intervals used by the plugin's scheduled events.

**Usage Example (adding your own schedule):**

```php
add_filter( 'cron_schedules', function( $schedules ) {
    $schedules['every_two_hours'] = array(
        'interval' => 7200,
        'display'  => __( 'Every Two Hours' ),
    );
    return $schedules;
} );
```

---

### `init` (used as a filter)

**Callback:** `Flexi_Abandon_Cart_Recovery_Admin::flexi_coupon_cart_expiry_scheduler`
**Priority:** 10
**Arguments:** 1

Registers cron events on `init` if they have not already been scheduled. The loader registers this via `add_filter()` for internal reasons; the actual callback does not modify the filter value.

---

### `flexi_check_cart_expiry`

**Callback:** `Flexi_Abandon_Cart_Recovery_Admin::mark_flexi_cart_expiry`
**Priority:** 10
**Arguments:** 1

Fired by the cron job to mark abandoned carts as expired. You can hook into this event to perform custom actions when cart expiry is processed:

```php
add_filter( 'flexi_check_cart_expiry', function( $value ) {
    // Custom logic before/after cart expiry processing.
    return $value;
} );
```

---

### `flexi_check_coupon_expiry`

**Callback:** `Flexi_Abandon_Cart_Recovery_Admin::mark_flexi_coupons_expiry`
**Priority:** 10
**Arguments:** 1

Fired by the cron job to mark dynamic coupons as expired. You can hook into this event to perform custom actions when coupon expiry is processed:

```php
add_filter( 'flexi_check_coupon_expiry', function( $value ) {
    // Custom logic before/after coupon expiry processing.
    return $value;
} );
```

---

## Admin Filters

### `mce_buttons`

**Callback:** `Flexi_Abandon_Cart_Recovery_Admin::flexi_tinymce_admin_btn`
**Priority:** 10
**Arguments:** 1
**Condition:** Only applied when the `page` query parameter equals `flexi-cart-recovery-settings`.

Adds custom shortcode-insertion buttons to the TinyMCE toolbar in the email template editor.

---

### `mce_external_plugins`

**Callback:** `Flexi_Abandon_Cart_Recovery_Admin::flexi_admin_filter_mce_plugin`
**Priority:** 10
**Arguments:** 1
**Condition:** Only applied when the `page` query parameter equals `flexi-cart-recovery-settings`.

Registers a custom TinyMCE plugin JavaScript file that provides the shortcode picker functionality in the template editor.

---

## Public / Storefront Filters

### `woocommerce_is_purchasable`

**Callback:** `Flexi_Abandon_Cart_Recovery_Public::flexi_cart_woocommerce_is_purchasable`
**Priority:** 10
**Arguments:** 2 (`$is_purchasable`, `$product`)

Allows the plugin to restrict product purchasability when a coupon's `restricted_emails` list is configured and the current user's email is not on the list.

```php
/**
 * Example: log when purchasability is modified.
 */
add_filter( 'woocommerce_is_purchasable', function( $is_purchasable, $product ) {
    // This filter runs after the plugin's callback.
    return $is_purchasable;
}, 20, 2 );
```

---

### `woocommerce_get_price_html`

**Callback:** `Flexi_Abandon_Cart_Recovery_Public::flexi_cart_woocommerce_get_price_html`
**Priority:** 10
**Arguments:** 2 (`$price`, `$product`)

Allows the plugin to modify the displayed product price HTML to reflect an active recovery coupon discount for the current user.

```php
/**
 * Example: remove the plugin's price modification.
 */
remove_filter(
    'woocommerce_get_price_html',
    array( $plugin_public_instance, 'flexi_cart_woocommerce_get_price_html' ),
    10
);
```

> **Note:** To obtain `$plugin_public_instance`, use the plugin's loader to retrieve the registered hooks, or re-instantiate the public class with the plugin name and version.
