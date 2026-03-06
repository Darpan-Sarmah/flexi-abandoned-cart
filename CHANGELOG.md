# Changelog — Flexi Abandon Cart Recovery

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] — 2024-01-01

### Initial Release

This is the first public release of **Flexi Abandon Cart Recovery**.

### Added

#### Core

- Plugin bootstrap file with WordPress and WooCommerce dependency checking.
- Activation routine that creates all required database tables using `dbDelta`.
- Deactivation routine that clears scheduled cron events.
- Uninstall routine.
- Constants: `FLEXI_ABANDON_CART_RECOVERY_VERSION`, `FLEXI_ABANDON_CART_RECOVERY_BASENAME`, `FLEXI_ABANDON_CART_RECOVERY_PREFIX`, `FLEXI_ABANDON_CART_RECOVERY_FILE`, `FLEXI_ABANDON_CART_RECOVERY_DIR`, `FLEXI_ABANDON_CART_RECOVERY_URL`.

#### Database Tables

- `{prefix}flexi_users_cart_details` — Abandoned cart records with status, timestamps, and flags.
- `{prefix}flexi_abandon_cart_users` — Customer information (email, name, role).
- `{prefix}flexi_users_cart_items` — Individual line items for each cart.
- `{prefix}flexi_email_logs` — Full delivery and engagement log for recovery emails.
- `{prefix}flexi_email_templates` — Email template definitions including subject, body, and coupon settings.
- `{prefix}flexi_cart_coupons` — Dynamic coupon records with discount settings and expiry.
- `{prefix}flexi_email_rules` — Rule set definitions for audience targeting.
- `{prefix}flexi_email_triggers` — Email trigger configurations linking templates, rules, and send delays.

#### Cart Tracking

- Capture abandoned cart data on WooCommerce cart events: `woocommerce_add_to_cart`, `woocommerce_after_cart_item_quantity_update`, `woocommerce_cart_item_restored`, `woocommerce_single_add_to_cart`, `woocommerce_cart_item_removed`.
- Automatic purchase detection via `woocommerce_thankyou` hook.
- Email link click tracking via `init` and `woocommerce_before_calculate_totals` hooks.
- Email open tracking via transparent pixel and `admin_post_track_email_open` endpoint.

#### Email System

- Default email template: **Abandon Cart Recovery Email Template**.
- Default coupon email template: **Flexi Coupon Email Template**.
- TinyMCE integration for rich-text template editing.
- Shortcodes: `{{cart_details}}`, `{{cart_checkout_url}}`, `{{coupon_code}}`, `{{coupon_discount}}`, `{{email_name}}`, `{{user_name}}`.
- Send Test Email functionality from the template editor.
- Report-over-email feature.

#### Dynamic Coupons

- Automatic WooCommerce coupon generation per recovery email.
- Support for fixed-amount and percentage discounts.
- Individual-use and usage-limit settings.
- Configurable expiry period.
- Restricted-email targeting.
- Scheduled expiry management via `flexi_check_coupon_expiry` cron event.

#### Admin Dashboard

- Summary statistics: total abandoned carts, emails sent, revenue recovered.
- Email Logs view with open, click, and purchase tracking.
- Abandoned cart user details view.
- Global Settings page.
- Email Templates management (list, create, edit, delete).
- Dynamic Coupons management (list, create, edit, delete).
- Email Rule Sets management.
- Email Triggers management.
- Schedulers management.

#### Rule Engine

- Rule types: Product, Category, Cart Value, User Role.
- AJAX endpoints for fetching products (`woocommerce_get_all_products`), categories (`woocommerce_get_all_categories`), and user roles (`woocommerce_get_all_roles`).

#### Scheduler

- WordPress cron integration with custom schedule via `cron_schedules` filter.
- `flexi_check_for_abandon_carts_event` — Scans for abandoned carts and dispatches recovery emails.
- `flexi_check_cart_expiry` — Marks expired carts.
- `flexi_check_coupon_expiry` — Marks expired coupons.

#### Public (Storefront)

- `woocommerce_is_purchasable` filter integration for coupon-restricted products.
- `woocommerce_get_price_html` filter integration for displaying coupon discounts.
- GDPR consent message on the checkout page.

#### Internationalisation

- Text domain: `flexi-abandon-cart-recovery`.
- Language files directory: `/languages`.
- `.pot` file ready for translation.

#### Default Configuration

The following defaults are set on first activation:

| Setting | Default Value |
|---|---|
| Enable Tracking | On |
| Cart Abandonment Time | 10 minutes |
| Resend Email After | 10 minutes |
| Cart Expire After | 30 days |
| Email From | info@flexi.com |
| Email Name | Site name |
| GDPR Message | Standard consent text |
| Language | en_US |

### Known Issues

- The admin class enables full PHP error reporting (`E_ALL`) in its constructor. This should be removed or made conditional on `WP_DEBUG` in a future release.
- Email open and click tracking requires that the site's `admin-post.php` endpoint is publicly accessible. Environments with strict firewall rules may block tracking requests.
- On very high-traffic stores, the WordPress pseudo-cron mechanism may introduce delays in email dispatch. A real server cron job is recommended (see [CONFIGURATION.md](CONFIGURATION.md)).

---

## Migration Guide

### From No Previous Version (Fresh Install)

No migration required. Activate the plugin and follow the [Setup Guide](README.md#setup-guide).

### Future Upgrade Notes

See [UPGRADE.md](UPGRADE.md) for instructions on upgrading between versions.
