# Flexi Abandon Cart Recovery

> Recover lost WooCommerce revenue by capturing abandoning shoppers' emails and sending automated follow-up campaigns with optional dynamic discount coupons.

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)
[![WordPress](https://img.shields.io/badge/WordPress-5.6%2B-blue)](https://wordpress.org)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-5.0%2B-purple)](https://woocommerce.com)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://php.net)
[![Version](https://img.shields.io/badge/version-1.0.0-green)](CHANGELOG.md)

---

## Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Setup Guide](#setup-guide)
- [Configuration](#configuration)
- [Email Templates](#email-templates)
- [Dynamic Coupons](#dynamic-coupons)
- [Analytics & Reports](#analytics--reports)
- [Troubleshooting](#troubleshooting)
- [FAQ](#faq)
- [Performance Considerations](#performance-considerations)
- [Multisite Compatibility](#multisite-compatibility)
- [Contributing](#contributing)
- [License](#license)

---

## Overview

**Flexi Abandon Cart Recovery** is a WooCommerce plugin that helps online store owners recover revenue lost to cart abandonment — one of the biggest sources of lost e-commerce revenue.

The plugin:

1. **Captures** customer email addresses as soon as they begin the checkout process.
2. **Detects** cart abandonment when no checkout activity is recorded for a configurable period.
3. **Sends** timed, personalised recovery emails — optionally with unique discount coupons.
4. **Tracks** email opens, link clicks, and resulting purchases so you can measure ROI.

---

## Key Features

| Feature | Description |
|---|---|
| Automatic Cart Tracking | Detects abandoned carts based on a configurable inactivity timeout |
| Multi-step Email Campaigns | Configure multiple timed follow-up emails per abandoned cart |
| Dynamic Coupon Generation | Automatically create unique WooCommerce coupons per recovery email |
| Rich Email Templates | TinyMCE editor with dynamic placeholder shortcodes |
| Rule Engine | Target emails by product, category, cart value, or user role |
| Email Triggers | Define when and to whom each email is sent |
| Analytics Dashboard | Track sent, opened, clicked, and converted emails |
| Email Logs | Detailed per-email log with full delivery and engagement history |
| Scheduler | WordPress cron integration — no manual processing required |
| GDPR Support | Configurable consent message displayed during checkout |
| Internationalization | Fully translatable; `.pot` file included |
| Multisite Ready | Compatible with WordPress Multisite |

---

## Requirements

| Dependency | Minimum Version |
|---|---|
| WordPress | 5.6 |
| WooCommerce | 5.0 |
| PHP | 7.4 |
| MySQL | 5.6 |
| MariaDB | 10.0 (alternative to MySQL) |

---

## Installation

### Via WordPress Admin (Recommended)

1. Log in to your WordPress admin dashboard.
2. Navigate to **Plugins → Add New**.
3. Search for **Flexi Abandon Cart Recovery**.
4. Click **Install Now**, then **Activate**.

### Manual Upload

1. Download the plugin `.zip` file.
2. In the WordPress admin, go to **Plugins → Add New → Upload Plugin**.
3. Select the downloaded `.zip` file and click **Install Now**.
4. Click **Activate Plugin**.

### FTP / SFTP

1. Extract the `.zip` file to obtain the `flexi-abandon-cart-recovery` folder.
2. Upload the folder to `/wp-content/plugins/` on your server via FTP/SFTP.
3. Activate the plugin through **Plugins** in the WordPress admin.

For a detailed walkthrough including database verification, see [INSTALLATION.md](INSTALLATION.md).

---

## Setup Guide

After activating the plugin, a welcome notice will appear. Follow these steps to get started:

### Step 1 — Configure Global Settings

Go to **Flexi Cart Recovery → Global Settings** and configure:

- **Enable Tracking** – Turn cart tracking on or off site-wide.
- **Cart Abandonment Time** – How long to wait before marking a cart as abandoned (default: 10 minutes).
- **Resend Email After** – Minimum gap between recovery emails for the same cart.
- **Cart Expiry** – How long before an abandoned cart is considered expired (default: 30 days).
- **Email From / Email Name** – The sender name and address for all recovery emails.
- **GDPR Message** – Consent notice displayed on the checkout page.

### Step 2 — Create an Email Template

Go to **Flexi Cart Recovery → Email Templates → Add New** and:

1. Enter a **Template Name** and **Email Subject**.
2. Use the TinyMCE editor to compose the email body.
3. Insert dynamic placeholders (see [Email Templates](#email-templates)).
4. Optionally enable a coupon and configure its settings.
5. Set the template **Status** to **Active** and save.

### Step 3 — Set Up Email Triggers

Go to **Flexi Cart Recovery → Email Triggers → Add New** and:

1. Enter a **Trigger Name**.
2. Select the **Email Template** to send.
3. Set **Send After** (delay from abandonment, e.g. 1 hour).
4. Optionally assign a **Rule Set** to limit which carts are targeted.
5. Set the trigger **Status** to **Active** and save.

### Step 4 — Monitor Results

- **Dashboard** – High-level statistics: total abandoned carts, emails sent, revenue recovered.
- **Email Logs** – Per-email delivery log with open, click, and purchase tracking.
- **Cart Details** – Browse individual abandoned carts and their status.

---

## Configuration

See [CONFIGURATION.md](CONFIGURATION.md) for the full configuration reference covering:

- Global settings
- Email template options
- Coupon configuration
- Trigger and rule settings
- Scheduler tuning
- Advanced options

---

## Email Templates

### Supported Shortcodes

Use these placeholders anywhere in the email subject or body:

| Shortcode | Output |
|---|---|
| `{{cart_details}}` | HTML table of the abandoned cart items (name, quantity, price) |
| `{{cart_checkout_url}}` | Direct link back to the checkout page pre-filled with the cart |
| `{{coupon_code}}` | The generated coupon code |
| `{{coupon_discount}}` | The coupon discount amount or percentage |
| `{{email_name}}` | Store name as configured in Global Settings |
| `{{user_name}}` | Customer's display name |

### Sending a Test Email

1. Open any template in the editor.
2. Click the **Send Test Email** button.
3. Enter a recipient address and click **Send**.

---

## Dynamic Coupons

The plugin can automatically generate unique WooCommerce coupons and include them in recovery emails.

### Creating a Coupon Template

Go to **Flexi Cart Recovery → Dynamic Coupons → Add New**:

| Field | Description |
|---|---|
| Coupon Name / Prefix | Prefix used when generating coupon codes |
| Discount Type | Fixed amount or percentage |
| Discount Amount | Value of the discount |
| Individual Use | Whether the coupon can be combined with other coupons |
| Usage Limit | Maximum number of times the coupon can be used |
| Expiry (days) | Days until the coupon expires after generation |

### Linking Coupons to Templates

When editing an email template, enable the **Coupon** option and select the coupon template to use. The `{{coupon_code}}` and `{{coupon_discount}}` placeholders will be replaced automatically when the email is sent.

---

## Analytics & Reports

### Dashboard

The dashboard provides a high-level summary:

- Total abandoned carts
- Carts recovered (completed purchase after receiving a recovery email)
- Emails sent, opened, and clicked
- Estimated revenue recovered

### Email Logs

The Email Logs page shows a full record of every recovery email dispatched:

| Column | Description |
|---|---|
| Recipient | Customer email address |
| Template | Email template used |
| Coupon | Coupon code applied (if any) |
| Sent At | Date and time the email was sent |
| Status | `sent` or `failed` |
| Opened | Number of times the email was opened |
| Clicked | Number of link clicks recorded |
| Purchased | Whether the customer completed a purchase |

### Enabling Email Open & Click Tracking

Open tracking is done via a transparent tracking pixel embedded in all outgoing emails.
Click tracking wraps the checkout URL with a redirect that records the click before forwarding.
Both mechanisms are enabled automatically; no additional configuration is required.

---

## Troubleshooting

For a detailed troubleshooting guide, see [TROUBLESHOOTING.md](TROUBLESHOOTING.md).

### Quick Reference

| Symptom | First Steps |
|---|---|
| Emails not sending | Check scheduler is running; verify WP cron is active; check Email Logs for errors |
| Dashboard shows no data | Confirm **Enable Tracking** is on; add items to cart and abandon to test |
| Cart tracking not working | Ensure WooCommerce is active; check browser console for JavaScript errors |
| Coupons not being created | Verify coupon is linked to the template; check WooCommerce coupon permissions |

---

## FAQ

**Q: Does the plugin require WooCommerce?**
A: Yes — Flexi Abandon Cart Recovery works exclusively with WooCommerce.

**Q: When is a cart considered abandoned?**
A: A cart is marked abandoned when no checkout activity is detected for the duration set in **Global Settings → Cart Abandonment Time** (default: 10 minutes).

**Q: Can I send multiple follow-up emails?**
A: Yes. Create multiple Email Triggers, each with a different template and **Send After** delay.

**Q: Will the plugin send emails to guest users?**
A: Yes, provided the guest entered their email address on the checkout page before abandoning.

**Q: Is the plugin compatible with caching plugins?**
A: Yes. Checkout pages are typically excluded from caching by plugins like WP Rocket or W3 Total Cache, so the cart tracking JavaScript runs as expected.

**Q: How do I prevent re-sending to already recovered carts?**
A: The plugin automatically marks carts as recovered or purchased when an order is completed. Recovered carts are excluded from future email sends.

**Q: What happens to expired coupons?**
A: The plugin runs a scheduled task (`flexi_check_coupon_expiry`) that marks expired coupons automatically.

---

## Performance Considerations

- **Database tables** — The plugin creates 7 custom database tables on activation. Queries are indexed on primary keys and foreign keys.
- **Cron frequency** — Recovery emails are sent via WordPress cron. For high-traffic stores, consider replacing `wp-cron` with a real server cron job. See [CONFIGURATION.md](CONFIGURATION.md) for details.
- **Cart data cleanup** — Expired carts (older than the configured expiry period) are automatically flagged. Implement periodic database maintenance for long-running stores.
- **Email delivery** — Using a dedicated SMTP plugin (e.g., WP Mail SMTP) with a transactional email provider is strongly recommended for reliable delivery at scale.

---

## Multisite Compatibility

The plugin is compatible with WordPress Multisite. Each sub-site maintains its own set of plugin database tables (prefixed with the sub-site's table prefix). Activate the plugin per-site, not network-wide, unless you have verified network-wide activation in your environment.

---

## Contributing

Contributions, bug reports, and feature requests are welcome.

1. Fork the repository.
2. Create a feature branch: `git checkout -b feature/my-new-feature`.
3. Commit your changes: `git commit -am 'Add new feature'`.
4. Push to the branch: `git push origin feature/my-new-feature`.
5. Open a Pull Request.

Please follow the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/) for PHP code.

For bug reports, see [SUPPORT.md](SUPPORT.md).

---

## License

Flexi Abandon Cart Recovery is licensed under the **GNU General Public License v2.0 or later**.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

See [LICENSE.txt](LICENSE.txt) for the full license text.
