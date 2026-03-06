# Troubleshooting Guide — Flexi Abandon Cart Recovery

## Table of Contents

- [Emails Not Sending](#emails-not-sending)
- [Dashboard Shows No Data](#dashboard-shows-no-data)
- [Cart Tracking Not Working](#cart-tracking-not-working)
- [Scheduler Issues](#scheduler-issues)
- [Database Errors](#database-errors)
- [Common Error Messages and Solutions](#common-error-messages-and-solutions)
- [Enabling Debug Logging](#enabling-debug-logging)

---

## Emails Not Sending

### Checklist

- [ ] At least one Email Trigger has **Status = Active**.
- [ ] The trigger's assigned Email Template has **Status = Active**.
- [ ] The **Send After** delay for the trigger has elapsed since the cart was abandoned.
- [ ] The abandoned cart has not already been recovered or marked as purchased.
- [ ] WordPress cron is running (see [Scheduler Issues](#scheduler-issues)).
- [ ] The `wp_mail()` function is working (use the **Send Test Email** feature).

### Test Email Delivery

1. Open any email template (**Flexi Cart Recovery → Email Templates**).
2. Click **Send Test Email**.
3. Enter a valid email address and click **Send**.
4. Check whether the email arrives.
   - If it does **not** arrive → the issue is with WordPress mail configuration, not the plugin.
   - If it does arrive → the issue is with the trigger/scheduler setup.

### Check Email Logs

Go to **Flexi Cart Recovery → Email Logs**. If entries appear with **Status = failed**:

- Review the **Message** column for the error text.
- Common failure reasons include invalid recipient addresses, SMTP authentication errors, and PHP `mail()` being disabled by the hosting provider.

### SMTP Configuration

The most reliable fix for email delivery issues is to use an SMTP plugin:

1. Install [WP Mail SMTP](https://wordpress.org/plugins/wp-mail-smtp/).
2. Configure it with a transactional email provider (Mailgun, SendGrid, Amazon SES, etc.).
3. Use the SMTP plugin's built-in email test to verify delivery.

### Hosting Provider Blocking `mail()`

Some shared hosting providers disable PHP's `mail()` function to prevent spam. In this case, an SMTP plugin is **required**.

---

## Dashboard Shows No Data

### Checklist

- [ ] **Enable Tracking** is turned **On** in **Global Settings**.
- [ ] WooCommerce is installed and active.
- [ ] At least one product has been added to the cart and an email address entered at checkout before abandoning.
- [ ] The **Cart Abandonment Time** has elapsed (default: 10 minutes).
- [ ] WordPress cron has run since the cart was abandoned.

### Quick Test

1. Temporarily set **Cart Abandonment Time** to **1 minute** in Global Settings.
2. Add a product to the WooCommerce cart on the storefront.
3. Begin checkout and enter a real email address.
4. Leave the page without completing the order.
5. Wait 1 minute, then manually trigger cron:
   ```bash
   wp cron event run --due-now
   ```
   Or visit `https://your-site.com/?doing_wp_cron`.
6. Refresh the **Dashboard** — the cart should now appear.

---

## Cart Tracking Not Working

### Checklist

- [ ] The checkout page is not being cached (add the checkout URL to your caching plugin's exclusion list).
- [ ] JavaScript is enabled in the browser.
- [ ] No browser console errors are present on the checkout page.
- [ ] WooCommerce is configured to show the email field before the checkout form is submitted (standard WooCommerce behaviour).

### Verify Cart Capture JavaScript

Open the browser developer tools (F12), go to the **Network** tab, and watch for AJAX requests when you update the cart or type in the email field. The plugin hooks into WooCommerce cart events (`woocommerce_add_to_cart`, `woocommerce_cart_item_removed`, etc.).

### Conflict With Other Plugins

If cart tracking stops after installing another plugin:

1. Deactivate all plugins except WooCommerce and Flexi Abandon Cart Recovery.
2. Test cart tracking.
3. Reactivate plugins one by one until the conflict is identified.

---

## Scheduler Issues

### WordPress Cron Not Running

WordPress cron relies on site traffic to trigger. On low-traffic sites, cron jobs may be significantly delayed.

**Diagnosis:**

```bash
wp cron event list
```

Look for `flexi_check_for_abandon_carts_event` — it should be listed with a **Next Run** timestamp.

**Fix (development/testing):**

```bash
wp cron event run --due-now
```

**Fix (production):**

Set up a real server cron job and disable WordPress's pseudo-cron:

```php
// wp-config.php
define('DISABLE_WP_CRON', true);
```

```cron
*/5 * * * * wget -q -O - https://your-site.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1
```

### Scheduler Events Missing

If `flexi_check_for_abandon_carts_event` is not listed in `wp cron event list`:

1. Deactivate and reactivate the plugin — this re-registers the cron events.
2. Verify that no other plugin is clearing all cron events.

---

## Database Errors

### Tables Do Not Exist

**Error message in logs:**
```
WordPress database error: Table 'wordpress.wp_flexi_abandon_cart_users' doesn't exist
```

**Fix:**

1. Go to **Plugins → Installed Plugins**.
2. Deactivate **Flexi Abandon Cart Recovery**.
3. Reactivate the plugin — this re-runs the table creation routine.

If tables still are not created, check that the database user has `CREATE TABLE` privileges.

### Foreign Key Constraint Fails

**Error message in logs:**
```
Cannot add or update a child row: a foreign key constraint fails
```

**Context:** This occurs if a cart item is inserted before the parent cart record exists (i.e., cart record creation failed).

**Fix:**

1. Check `debug.log` for errors preceding the foreign key failure.
2. Ensure that the `{prefix}flexi_users_cart_details` table was created successfully.
3. Deactivate and reactivate the plugin if the table is missing.

### `dbDelta` Errors on Activation

If you see SQL errors during activation, your MySQL/MariaDB version may not support a particular column type or default value.

**Minimum versions:** MySQL 5.6 / MariaDB 10.0.

Check your database version:
```sql
SELECT VERSION();
```

---

## Common Error Messages and Solutions

| Error Message | Cause | Solution |
|---|---|---|
| `Error saving default options.` | `update_option()` returned false | Check database write permissions; try deactivating and reactivating |
| `Error inserting default email template.` | Insert into `flexi_email_templates` failed | Verify the table exists and the DB user has INSERT permission |
| WooCommerce required notice on activation | WooCommerce is not active | Activate WooCommerce first |
| White screen on activation | Fatal PHP error | Enable `WP_DEBUG` in `wp-config.php` and check `debug.log` |
| Coupon codes not appearing in emails | Coupon not linked to template | Edit the template and enable the coupon section |
| Open/click tracking not recording | Admin-post endpoint blocked | Check that `admin-post.php` is accessible; review server firewall rules |

---

## Enabling Debug Logging

Add the following to `wp-config.php` to enable WordPress debug logging:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false); // Keep false on production
```

Logs are written to `/wp-content/debug.log`.

Additionally, the plugin admin class is initialised with full PHP error reporting during development:

```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

> **Warning:** Remove or disable these settings in production environments.
