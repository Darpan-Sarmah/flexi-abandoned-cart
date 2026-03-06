# Frequently Asked Questions

## General

### Does this plugin work without WooCommerce?

No. Flexi Abandoned Cart Recovery requires WooCommerce to be installed and active. The plugin hooks into the WooCommerce checkout flow to capture email addresses and cart data.

### Which versions of WordPress and WooCommerce are supported?

- **WordPress:** 5.9 or higher (tested up to 6.4)
- **WooCommerce:** 6.0 or higher (tested up to 8.0)
- **PHP:** 7.4 or higher (tested up to 8.2)

### Is the plugin free?

Yes, the plugin is released under the GPL-2.0-or-later licence and is free to use.

---

## Cart Tracking

### When is a cart considered abandoned?

A cart is considered abandoned when a customer has entered their email address during checkout but has not completed the order within the configured abandonment threshold (default: 60 minutes).

### Does the plugin track guest checkouts?

Yes. The plugin tracks any checkout where the customer has entered their email address, whether they are a registered user or a guest.

### What happens if a customer returns and completes their order?

If the customer returns and completes their purchase, the cart is automatically marked as recovered and removed from the abandoned carts list.

---

## Recovery Emails

### How many follow-up emails can I configure?

There is no hard limit on the number of emails in a recovery sequence. You can configure as many as needed at different time intervals.

### Can I customise the email templates?

Yes. You can customise the subject line and body of each recovery email. Dynamic variables such as `{customer_name}`, `{cart_total}`, and `{coupon_code}` can be inserted into the template.

### Can I include a coupon in the recovery email?

Yes. Enable the dynamic coupon option in the email settings to automatically generate a unique discount code and embed it in the recovery email.

### What email provider does the plugin use?

The plugin uses the standard WordPress `wp_mail()` function, which works with any SMTP plugin or email service you have configured for your WordPress site (e.g. WP Mail SMTP, Mailgun, SendGrid).

---

## Coupons

### How are dynamic coupons generated?

When a recovery email with a coupon is sent, the plugin automatically creates a unique WooCommerce coupon with the configured discount type, amount, and expiry. The coupon code is embedded in the email link.

### Do coupons expire?

Coupon expiry is configurable. You can set a number of days after which the generated coupon will expire.

---

## Analytics

### What does the analytics dashboard show?

The dashboard shows:
- Total abandoned carts
- Recovered carts and recovery rate
- Revenue recovered
- Email open rates, click-through rates, and conversion rates

### How long is cart data stored?

Cart data retention is configurable in the plugin settings. By default, data older than 90 days is automatically purged.

---

## Privacy & GDPR

### Is the plugin GDPR compliant?

The plugin only tracks customers who have voluntarily entered their email address during the checkout process. You should update your store's Privacy Policy to disclose that cart abandonment tracking is in use. No personal data is sent to third-party servers by the plugin itself.

### Can customers opt out?

Customers can opt out by not entering their email address. You may also add an explicit opt-in checkbox to the checkout page using the plugin's settings or a custom filter.
