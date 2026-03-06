# Configuration Guide — Flexi Abandon Cart Recovery

## Table of Contents

- [Global Settings](#global-settings)
- [Email Template Setup](#email-template-setup)
- [Coupon Configuration](#coupon-configuration)
- [Email Triggers and Rules](#email-triggers-and-rules)
- [Rule Sets](#rule-sets)
- [Scheduler Configuration](#scheduler-configuration)
- [Advanced Settings](#advanced-settings)
- [Performance Tuning](#performance-tuning)

---

## Global Settings

Navigate to **Flexi Cart Recovery → Global Settings** to configure the plugin's core behaviour.

### Tracking

| Setting | Default | Description |
|---|---|---|
| Enable Tracking | On | Master switch. When off, no new abandoned carts are recorded. |

### Abandonment Detection

| Setting | Default | Description |
|---|---|---|
| Cart Abandonment Time | 10 | Inactivity duration before a cart is marked abandoned. |
| Cart Abandonment Time Unit | Minutes | Time unit for the abandonment threshold (`minutes`, `hours`). |

> **Tip:** A shorter abandonment time catches more carts but may include users who are still actively browsing. A common best practice is 15–30 minutes for most stores.

### Email Resend

| Setting | Default | Description |
|---|---|---|
| Resend Email After | 10 | Minimum interval between two recovery emails for the same cart. |
| Resend Email After Unit | Minutes | Time unit for the resend interval. |

### Cart Expiry

| Setting | Default | Description |
|---|---|---|
| Cart Expire After | 30 | Days after which an abandoned cart is considered expired. |
| Cart Expire After Unit | Days | Time unit for cart expiry. |

Expired carts are excluded from future email sends and are marked with `is_expired = 1` in the database.

### Email Sender

| Setting | Default | Description |
|---|---|---|
| Email From | info@flexi.com | The `From` email address for all recovery emails. |
| Email Name | (site name) | The `From` display name for all recovery emails. |

> **Important:** Use a verified domain email address. Sending from a free webmail address (e.g., Gmail) may trigger spam filters or be blocked by DMARC policies.

### GDPR & Privacy

| Setting | Default | Description |
|---|---|---|
| GDPR Message | (default text) | Consent notice displayed on the WooCommerce checkout page alongside the email field. |

### Language

| Setting | Default | Description |
|---|---|---|
| Email and Plugin Language | en_US | Locale used for plugin UI and email templates. |

---

## Email Template Setup

Navigate to **Flexi Cart Recovery → Email Templates**.

### Creating a New Template

1. Click **Add New Template**.
2. Fill in the **Template Name** (internal reference only).
3. Enter the **Email Subject** — supports shortcodes.
4. Compose the **Email Body** using the TinyMCE rich-text editor.
5. Insert shortcodes from the reference panel (see below).
6. Configure the **Coupon** section if you want to include a discount.
7. Set **Status** to **Active**.
8. Click **Save Template**.

### Available Shortcodes

| Shortcode | Output |
|---|---|
| `{{cart_details}}` | HTML table listing cart items (product name, quantity, price) |
| `{{cart_checkout_url}}` | Clickable link back to the WooCommerce checkout page, pre-populated with the cart |
| `{{coupon_code}}` | The dynamically generated coupon code (requires coupon enabled on template) |
| `{{coupon_discount}}` | The coupon discount value (e.g., `10%` or `$5.00`) |
| `{{email_name}}` | Store name as set in Global Settings |
| `{{user_name}}` | Customer's display name |

### Coupon Settings in Template

| Field | Description |
|---|---|
| Coupon Status | Enable or disable coupon generation for this template |
| Coupon Type | Select the linked dynamic coupon template |
| Coupon Name (prefix) | Prefix prepended to the auto-generated coupon code |

### Sending a Test Email

On any template's edit screen, click **Send Test Email**, enter a recipient address, and click **Send**. The test email will render all shortcodes using placeholder values.

---

## Coupon Configuration

Navigate to **Flexi Cart Recovery → Dynamic Coupons**.

### Creating a Coupon Template

1. Click **Add New Coupon**.
2. Set the **Coupon Name** (used as a prefix for generated codes).
3. Choose **Discount Type**: `Fixed` or `Percentage`.
4. Enter the **Discount Amount**.
5. Toggle **Individual Use** if the coupon should not combine with other promotions.
6. Set a **Coupon Limit** (total uses across all generated codes; 0 = unlimited).
7. Enter **Expiry (days)** — the number of days after generation before the coupon expires.
8. Optionally configure **Restricted Emails** to limit redemption to specific addresses.
9. Set **Status** to **Active** and save.

### How Coupons Are Generated

When a recovery email is dispatched:

1. The plugin checks whether the selected email template has coupons enabled.
2. A new WooCommerce coupon is created using the template's discount settings and a unique code based on the configured prefix.
3. The coupon code and discount details are embedded in the email via `{{coupon_code}}` and `{{coupon_discount}}`.
4. The coupon record is stored in `{prefix}flexi_cart_coupons` for tracking and expiry management.

### Coupon Expiry Management

A scheduled task (`flexi_check_coupon_expiry`) runs automatically to mark expired coupons (`is_expired = 1`) so they are no longer presented in new emails.

---

## Email Triggers and Rules

### Email Triggers

Navigate to **Flexi Cart Recovery → Email Triggers**.

An **Email Trigger** defines *when* a recovery email is sent and *which template* is used.

| Field | Description |
|---|---|
| Trigger Name | Internal label for this trigger |
| Email Template | The template to send |
| Rule Set | Optional audience rule (see Rule Sets below) |
| Send After (number) | Delay from abandonment before this trigger fires |
| Send After (unit) | Time unit: `minutes`, `hours`, `days` |
| Status | Active or Inactive |

**Multi-step Campaigns:** Create multiple triggers with escalating delays. For example:

| Trigger | Template | Send After |
|---|---|---|
| First Reminder | Basic reminder | 1 hour |
| Second Reminder | Reminder + coupon | 24 hours |
| Final Reminder | Urgent message | 3 days |

### Rule Sets

Navigate to **Flexi Cart Recovery → Rule Sets** (accessible from the Email Triggers page).

A **Rule Set** filters which abandoned carts are eligible for a given trigger. This lets you target specific segments with tailored messaging.

**Rule Types:**

| Rule Type | Description |
|---|---|
| Product | Target carts containing a specific product |
| Category | Target carts containing products in a specific category |
| Cart Value | Target carts above or below a total value |
| User Role | Target carts belonging to users with a specific WordPress role |

Multiple conditions can be combined within a single rule set.

---

## Scheduler Configuration

The plugin uses WordPress cron to:

- Detect newly abandoned carts (`flexi_check_for_abandon_carts_event`).
- Mark expired carts (`flexi_check_cart_expiry`).
- Mark expired coupons (`flexi_check_coupon_expiry`).

### Default Schedule

The scheduler is registered in WordPress cron when the plugin is active. The cron event fires based on WordPress's built-in schedule intervals.

### Using a Real Server Cron (Recommended for Production)

WordPress cron is triggered by web traffic, which can cause delays on low-traffic sites. For reliable, time-accurate email delivery, replace WordPress cron with a real server cron job:

1. Disable WordPress cron in `wp-config.php`:
   ```php
   define('DISABLE_WP_CRON', true);
   ```
2. Add a server cron job (Linux `crontab -e`):
   ```
   */5 * * * * wget -q -O - https://your-site.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1
   ```
   Or with WP-CLI:
   ```
   */5 * * * * cd /path/to/wordpress && wp cron event run --due-now
   ```

---

## Advanced Settings

### SMTP for Email Delivery

The plugin uses WordPress's built-in `wp_mail()` function. To improve deliverability:

1. Install an SMTP plugin such as [WP Mail SMTP](https://wordpress.org/plugins/wp-mail-smtp/).
2. Configure it with your transactional email provider (SendGrid, Mailgun, Amazon SES, etc.).
3. Verify email delivery using the SMTP plugin's test tool.

### Email Open Tracking

Open tracking is done via a 1×1 transparent pixel image embedded in outgoing emails. The image URL points to an admin-post endpoint (`admin_post_track_email_open`) which increments the `opened` counter in `{prefix}flexi_email_logs`.

No additional configuration is required.

### Purchase (Conversion) Tracking

When a customer completes a WooCommerce order, the `woocommerce_thankyou` hook fires. The plugin checks whether the order's email matches an abandoned cart and marks it as `is_purchased = 1`.

---

## Performance Tuning

- **Limit email trigger count** — Each trigger runs a database query per abandoned cart per scheduler cycle. Keep the number of active triggers reasonable (3–5 is typical).
- **Use server cron** — See [Scheduler Configuration](#scheduler-configuration).
- **Database maintenance** — Periodically purge very old expired cart records to keep table sizes manageable. The plugin does not currently auto-delete records; use a maintenance plugin or a manual SQL query:
  ```sql
  DELETE FROM wp_flexi_users_cart_details
  WHERE is_expired = 1 AND abandon_time < DATE_SUB(NOW(), INTERVAL 90 DAY);
  ```
- **Use a transactional email provider** — Offloads email queuing and delivery from your web server.
- **Object caching** — A persistent object cache (Redis, Memcached) can reduce repeated database lookups for plugin settings.
