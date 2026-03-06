# Troubleshooting Guide

This guide covers the most common issues with Flexi Abandoned Cart Recovery for WooCommerce.

---

## Plugin Will Not Activate

**Symptom:** An error is shown when trying to activate the plugin.

**Possible causes and fixes:**
1. **WooCommerce is not active.** Install and activate WooCommerce, then activate this plugin.
2. **PHP version too low.** This plugin requires PHP 7.4 or higher. Check your PHP version in **WordPress admin → Tools → Site Health**.
3. **WordPress version too low.** This plugin requires WordPress 5.9 or higher.

---

## Carts Are Not Being Tracked

**Symptom:** No abandoned carts appear in the plugin dashboard after customers leave the checkout.

**Possible causes and fixes:**
1. **Customer did not enter their email.** The plugin only captures carts where an email address has been entered during checkout.
2. **Abandonment time threshold not reached.** Wait for the configured abandonment threshold (default: 60 minutes) to elapse.
3. **Caching plugin conflict.** A caching plugin may be preventing the checkout form from sending data. Try adding the checkout page to your caching plugin's exclusion list.
4. **JavaScript error on the checkout page.** Open your browser's developer console (F12) on the checkout page and check for JavaScript errors that may prevent the email capture from working.

---

## Recovery Emails Are Not Being Sent

**Symptom:** Abandoned carts are tracked but no emails are sent.

**Possible causes and fixes:**
1. **WordPress Cron not running.** The plugin uses WP-Cron to schedule emails. If your server does not support WP-Cron, set up a real cron job. Check **WordPress admin → Tools → Site Health → Scheduled Events** to verify cron is working.
2. **Email sending misconfigured.** Check that your site can send email at all using a plugin like Check Email. If emails are not sending, configure an SMTP plugin such as WP Mail SMTP.
3. **Email sent to spam.** Check the customer's spam/junk folder. Configure SPF, DKIM, and DMARC records for your sending domain to improve deliverability.
4. **Email delay not elapsed.** Recovery emails are sent after the configured delay interval. Ensure enough time has passed.

---

## Duplicate Emails Being Sent

**Symptom:** Customers are receiving the same recovery email more than once.

**Possible causes and fixes:**
1. **Multiple cron events triggered.** This can happen when WP-Cron events duplicate due to high traffic. Consider using a real server-side cron job and adding `define('DISABLE_WP_CRON', true);` to `wp-config.php`.
2. **Plugin activated multiple times.** Deactivate and reactivate the plugin to reset scheduled events.

---

## Dynamic Coupons Not Working

**Symptom:** Coupon codes in recovery emails show as invalid or do not apply.

**Possible causes and fixes:**
1. **Coupon has expired.** Check the coupon expiry setting and ensure it is set to a reasonable period.
2. **Coupon already used.** If the coupon is set to single-use, it may have already been redeemed.
3. **Product or cart restrictions.** Verify that the coupon restrictions (minimum order amount, allowed products, etc.) match the customer's cart.

---

## High Database Usage

**Symptom:** The plugin appears to be using a large amount of database storage.

**Possible causes and fixes:**
1. **Data retention period too long.** Reduce the cart data retention period in the plugin settings to automatically purge older records.
2. **Log data accumulating.** Periodically clear old log entries from the Logs page in the plugin admin.

---

## Conflict With Another Plugin

**Symptom:** The plugin stops working or causes errors after installing another plugin.

**Steps to diagnose:**
1. Deactivate all other plugins except WooCommerce and this plugin.
2. If the issue resolves, reactivate plugins one at a time to identify the conflicting plugin.
3. Report the conflict in the [GitHub issue tracker](https://github.com/Darpan-Sarmah/flexi-abandoned-cart/issues) with details of the conflicting plugin.

---

## Enabling Debug Logging

To capture detailed error output:

1. Add the following to your `wp-config.php` file (above `/* That's all, stop editing! */`):

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

2. Reproduce the issue.
3. Check the log file at `wp-content/debug.log`.
4. Include the relevant log output when reporting a bug.

---

## Still Need Help?

See [`docs/SUPPORT.md`](SUPPORT.md) for information on how to get further assistance.
