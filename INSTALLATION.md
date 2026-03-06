# Installation Guide — Flexi Abandon Cart Recovery

## Table of Contents

- [System Requirements](#system-requirements)
- [Step-by-Step Installation](#step-by-step-installation)
- [Database Table Verification](#database-table-verification)
- [Initial Configuration Checklist](#initial-configuration-checklist)
- [Testing the Plugin](#testing-the-plugin)
- [Common Installation Issues](#common-installation-issues)

---

## System Requirements

| Component | Minimum | Recommended |
|---|---|---|
| WordPress | 5.6 | 6.4+ |
| WooCommerce | 5.0 | 8.0+ |
| PHP | 7.4 | 8.1+ |
| MySQL | 5.6 | 8.0 |
| MariaDB | 10.0 | 10.6+ |
| Memory Limit | 64 MB | 256 MB |
| Max Execution Time | 30 s | 60 s |

> **Note:** WooCommerce must be installed and activated before activating Flexi Abandon Cart Recovery.

---

## Step-by-Step Installation

### Method 1: WordPress Admin Upload

1. Log in to your WordPress admin dashboard (`/wp-admin`).
2. Go to **Plugins → Add New**.
3. Click **Upload Plugin** at the top of the page.
4. Click **Choose File**, select the `flexi-abandon-cart-recovery.zip` file, and click **Install Now**.
5. Once the upload completes, click **Activate Plugin**.
6. You should see a welcome notice confirming successful activation.

### Method 2: FTP / SFTP

1. Extract `flexi-abandon-cart-recovery.zip` on your local machine.
2. Connect to your web server using an FTP/SFTP client (e.g., FileZilla).
3. Navigate to `/wp-content/plugins/` on the server.
4. Upload the entire `flexi-abandon-cart-recovery` folder.
5. In the WordPress admin, go to **Plugins → Installed Plugins**.
6. Find **Flexi Abandon Cart Recovery** and click **Activate**.

### Method 3: WP-CLI

```bash
wp plugin install flexi-abandon-cart-recovery --activate
```

---

## Database Table Verification

During activation, the plugin creates the following custom database tables:

| Table Name | Purpose |
|---|---|
| `{prefix}flexi_users_cart_details` | Abandoned cart records |
| `{prefix}flexi_abandon_cart_users` | Customer information |
| `{prefix}flexi_users_cart_items` | Individual cart line items |
| `{prefix}flexi_email_logs` | Email delivery and engagement log |
| `{prefix}flexi_email_templates` | Email template definitions |
| `{prefix}flexi_cart_coupons` | Dynamic coupon records |
| `{prefix}flexi_email_rules` | Rule set definitions |
| `{prefix}flexi_email_triggers` | Email trigger configurations |

`{prefix}` is your WordPress table prefix (default: `wp_`).

### Verify via phpMyAdmin

1. Open phpMyAdmin and select your WordPress database.
2. Confirm that all eight tables listed above are present.
3. If any table is missing, deactivate the plugin and reactivate it to re-run the table creation routine.

### Verify via WP-CLI

```bash
wp db tables --scope=all | grep flexi
```

Expected output (with default `wp_` prefix):

```
wp_flexi_abandon_cart_users
wp_flexi_cart_coupons
wp_flexi_email_logs
wp_flexi_email_rules
wp_flexi_email_templates
wp_flexi_email_triggers
wp_flexi_users_cart_details
wp_flexi_users_cart_items
```

---

## Initial Configuration Checklist

After activation, complete the following steps before going live:

- [ ] Navigate to **Flexi Cart Recovery → Global Settings** and review all default values.
- [ ] Set **Cart Abandonment Time** to a value appropriate for your store (default: 10 minutes).
- [ ] Set a valid **Email From** address and **Email Name** for outgoing recovery emails.
- [ ] Review and customise the **GDPR Message** displayed on the checkout page.
- [ ] Open the default email template (**Email Templates → Abandon Cart Recovery Email Template**) and edit it to match your store's branding.
- [ ] Create at least one **Email Trigger** and set its status to **Active**.
- [ ] Send a **Test Email** from the template editor to verify that emails are delivered correctly.
- [ ] Add a product to the cart on the storefront, begin checkout (enter an email address), then leave without completing — verify that the cart appears in the **Dashboard** as abandoned after the configured timeout.

---

## Testing the Plugin

### Functional Test

1. Enable **WP_DEBUG** and **WP_DEBUG_LOG** in `wp-config.php` if needed.
2. On the storefront, add a product to the cart.
3. Begin checkout and enter a valid email address.
4. Leave the checkout page without completing the purchase.
5. Wait for the abandonment timeout to elapse (or temporarily reduce it to 1 minute for testing).
6. Manually trigger WordPress cron:
   ```bash
   wp cron event run --due-now
   ```
   Or visit `/?doing_wp_cron` in your browser.
7. Check the **Dashboard** — the cart should appear as abandoned.
8. Check **Email Logs** — a recovery email should be listed.
9. Verify the email arrived in the inbox of the address entered in step 3.

### Test Email Delivery

From any email template's edit screen, use the **Send Test Email** feature to confirm that WordPress's `wp_mail()` is correctly configured and that emails reach their destination.

If emails are not arriving, consider installing and configuring an SMTP plugin such as [WP Mail SMTP](https://wordpress.org/plugins/wp-mail-smtp/).

---

## Common Installation Issues

### Plugin Fails to Activate

**Symptom:** Clicking **Activate** returns a fatal error or white screen.

**Possible Causes & Fixes:**

- WooCommerce is not active → Activate WooCommerce first, then retry.
- PHP version is below 7.4 → Upgrade PHP on your hosting environment.
- A file was corrupted during upload → Delete the plugin folder and re-upload.

### Database Tables Not Created

**Symptom:** The plugin activates but the **Dashboard** is blank and saving settings fails.

**Fix:**

1. Ensure the database user has `CREATE TABLE` privileges.
2. Deactivate and reactivate the plugin.
3. Check `debug.log` for `dbDelta` errors and contact your hosting provider if permission issues persist.

### Welcome Notice Does Not Appear

**Symptom:** Plugin activates but no welcome notice is shown.

**Explanation:** The notice is stored in a transient that expires after 5 seconds. It is displayed on the next admin page load after activation. If you were redirected too slowly, the transient may have expired. This is cosmetic and does not affect functionality.

### "Requires WooCommerce" Notice Appears

**Symptom:** After activation, you see a notice saying WooCommerce is required and the plugin is deactivated.

**Fix:** Install and activate WooCommerce, then activate Flexi Abandon Cart Recovery again.
