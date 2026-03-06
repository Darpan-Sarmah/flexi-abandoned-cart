# WordPress Action Hooks — Flexi Abandon Cart Recovery

This document lists all WordPress action hooks that Flexi Abandon Cart Recovery registers and fires.

## Table of Contents

- [Custom Actions (Do Actions)](#custom-actions-do-actions)
- [WordPress / WooCommerce Actions Used](#wordpress--woocommerce-actions-used)
- [Cron Events](#cron-events)

---

## Custom Actions (Do Actions)

The plugin does not currently expose custom `do_action()` hooks for third-party use. Future versions will add extension hooks as needed.

---

## WordPress / WooCommerce Actions Used

The plugin hooks into the following core WordPress and WooCommerce actions:

### Admin Area

| Action | Priority | Handler | Description |
|---|---|---|---|
| `admin_enqueue_scripts` | 10 | `Flexi_Abandon_Cart_Recovery_Admin::enqueue_styles` | Enqueues admin CSS |
| `admin_enqueue_scripts` | 10 | `Flexi_Abandon_Cart_Recovery_Admin::enqueue_scripts` | Enqueues admin JavaScript |
| `admin_menu` | 22 | `Flexi_Abandon_Cart_Recovery_Admin::flexi_acr_admin_menu` | Registers admin menu pages |
| `admin_notices` | 10 | `flexi_abandon_cart_reco_admin_notice_activation` | Displays the post-activation welcome notice |
| `admin_init` | 10 | `deactivate_flexi_abandon_cart_reco_woo_missing` | Deactivates the plugin if WooCommerce is missing |
| `admin_post_nopriv_track_email_open` | 10 | `Flexi_Abandon_Cart_Recovery_Admin::track_email_open` | Records email open (unauthenticated) |
| `admin_post_track_email_open` | 10 | `Flexi_Abandon_Cart_Recovery_Admin::track_email_open` | Records email open (authenticated) |

### AJAX

| Action | Priority | Handler | Description |
|---|---|---|---|
| `wp_ajax_flexi_save_trigger_data` | 10 | `flexi_save_trigger_data` | Saves email trigger configuration |
| `wp_ajax_woocommerce_get_all_products` | 10 | `flexi_woocommerce_get_all_products` | Returns all products as JSON |
| `wp_ajax_nopriv_woocommerce_get_all_products` | 10 | `flexi_woocommerce_get_all_products` | Returns all products as JSON (guest) |
| `wp_ajax_woocommerce_get_all_categories` | 10 | `flexi_woocommerce_get_all_categories` | Returns all categories as JSON |
| `wp_ajax_nopriv_woocommerce_get_all_categories` | 10 | `flexi_woocommerce_get_all_categories` | Returns all categories as JSON (guest) |
| `wp_ajax_woocommerce_get_all_roles` | 10 | `flexi_woocommerce_get_all_user_roles` | Returns all user roles as JSON |
| `wp_ajax_nopriv_woocommerce_get_all_roles` | 10 | `flexi_woocommerce_get_all_user_roles` | Returns all user roles as JSON (guest) |
| `wp_ajax_flexi_send_test_mail` | 10 | `flexi_send_test_mail` | Sends a test recovery email |
| `wp_ajax_nopriv_flexi_send_test_mail` | 10 | `flexi_send_test_mail` | Sends a test recovery email (guest) |
| `wp_ajax_flexi_send_report_over_mail` | 10 | `flexi_send_report_over_mail` | Emails an analytics report |
| `wp_ajax_nopriv_flexi_send_report_over_mail` | 10 | `flexi_send_report_over_mail` | Emails an analytics report (guest) |

### WooCommerce Cart Events

| Action | Priority | Handler | Description |
|---|---|---|---|
| `woocommerce_add_to_cart` | 10 | `flexi_capture_abandoned_cart_data` | Captures cart data when a product is added |
| `woocommerce_after_cart_item_quantity_update` | 10 | `flexi_capture_abandoned_cart_data` | Captures cart data when quantity changes |
| `woocommerce_cart_item_restored` | 10 | `flexi_capture_abandoned_cart_data` | Captures cart data when an item is restored |
| `woocommerce_single_add_to_cart` | 10 | `flexi_capture_abandoned_cart_data` | Captures cart data on single product add |
| `woocommerce_cart_item_removed` | 10 | `flexi_capture_abandoned_cart_data` | Captures cart data when an item is removed |
| `woocommerce_thankyou` | 1 | `Flexi_Abandon_Cart_Recovery_Admin::track_purchase` | Marks cart as purchased on order completion |

### General WordPress

| Action | Priority | Handler | Description |
|---|---|---|---|
| `plugins_loaded` | 10 | `Flexi_Abandon_Cart_Recovery_i18n::load_plugin_textdomain` | Loads plugin translations |
| `init` | 10 | `Flexi_Abandon_Cart_Recovery_Admin::track_link_click` | Records recovery link click |
| `woocommerce_before_calculate_totals` | 10 | `Flexi_Abandon_Cart_Recovery_Admin::track_link_click` | Records recovery link click during cart calculation |

### Frontend

| Action | Priority | Handler | Description |
|---|---|---|---|
| `wp_enqueue_scripts` | 10 | `Flexi_Abandon_Cart_Recovery_Public::enqueue_styles` | Enqueues frontend CSS |
| `wp_enqueue_scripts` | 10 | `Flexi_Abandon_Cart_Recovery_Public::enqueue_scripts` | Enqueues frontend JavaScript |

---

## Cron Events

The plugin registers the following custom WordPress cron events:

| Event | Schedule | Handler | Description |
|---|---|---|---|
| `flexi_check_for_abandon_carts_event` | Custom interval | `Flexi_Abandon_Cart_Recovery_Admin::flexi_check_for_abandoned_carts` | Scans for abandoned carts and dispatches recovery emails |
| `flexi_check_cart_expiry` | Custom interval | `Flexi_Abandon_Cart_Recovery_Admin::mark_flexi_cart_expiry` | Marks carts that have passed the expiry threshold |
| `flexi_check_coupon_expiry` | Custom interval | `Flexi_Abandon_Cart_Recovery_Admin::mark_flexi_coupons_expiry` | Marks coupons that have passed their expiry date |

Cron events are registered in `Flexi_Abandon_Cart_Recovery_Admin::flexi_coupon_cart_expiry_scheduler()` (hooked to `init`).

Custom cron schedules are added via the `cron_schedules` filter in `Flexi_Abandon_Cart_Recovery_Admin::flexi_add_custom_scheduler()`.

### Manually Triggering Cron Events

```bash
# Run all due cron events
wp cron event run --due-now

# Run a specific event
wp cron event run flexi_check_for_abandon_carts_event
wp cron event run flexi_check_cart_expiry
wp cron event run flexi_check_coupon_expiry
```
