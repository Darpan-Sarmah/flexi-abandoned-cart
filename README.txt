=== Flexi Abandoned Cart Recovery for WooCommerce ===
Contributors: darpansarmah
Tags: abandoned carts, email recovery, woocommerce, ecommerce, cart recovery
Requires at least: 5.9
Requires PHP: 7.4
Tested up to: 6.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Recover lost sales with automated abandoned cart recovery emails and dynamic coupons.

== Description ==

Flexi Abandoned Cart Recovery for WooCommerce helps store owners recover lost revenue by automatically tracking abandoned carts and sending targeted follow-up emails to potential customers.

When a visitor starts the checkout process but leaves without completing their purchase, Flexi Abandoned Cart Recovery captures their information and triggers a series of automated recovery emails. You can also generate dynamic discount coupons to entice customers back to complete their purchase.

= Key Features =

* **Cart Abandonment Tracking** – Automatically detects and records abandoned carts in real time.
* **Automated Recovery Emails** – Send a series of customisable follow-up emails to recover lost sales.
* **Dynamic Coupon Generation** – Create unique discount codes automatically to incentivise purchase completion.
* **Advanced Analytics Dashboard** – View recovery rates, revenue recovered, and email performance metrics.
* **Email Performance Tracking** – Monitor open rates, click-through rates, and conversions per campaign.
* **Global Settings** – Fine-tune abandonment timing, email schedules, and coupon rules from one place.
* **WooCommerce Integration** – Seamlessly integrates with your existing WooCommerce store.
* **GDPR-Friendly** – Respects customer privacy; only tracks users who have entered their email address.
* **Translation-Ready** – Fully internationalised and ready for translation into any language.

= How It Works =

1. A customer adds items to their cart and begins checkout.
2. They enter their email address but leave without completing the order.
3. The plugin records the abandoned cart and starts the recovery sequence.
4. Automated emails are sent at configurable intervals with optional coupon incentives.
5. The customer clicks the recovery link, returns to their cart, and completes the purchase.
6. You track the recovered revenue in the analytics dashboard.

= Requirements =

* WordPress 5.9 or higher
* WooCommerce 6.0 or higher
* PHP 7.4 or higher

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel.
2. Go to **Plugins → Add New**.
3. Search for "Flexi Abandoned Cart Recovery".
4. Click **Install Now**, then **Activate**.
5. Go to **WooCommerce → Flexi Abandoned Carts** to configure the plugin.

= Manual Installation =

1. Download the plugin ZIP file.
2. Log in to your WordPress admin panel.
3. Go to **Plugins → Add New → Upload Plugin**.
4. Choose the downloaded ZIP file and click **Install Now**.
5. Activate the plugin through the **Plugins** menu.
6. Go to **WooCommerce → Flexi Abandoned Carts** to configure the plugin.

= FTP Installation =

1. Download and unzip the plugin.
2. Upload the `flexi-abandon-cart-recovery` folder to `/wp-content/plugins/`.
3. Activate the plugin through the **Plugins** menu in WordPress admin.
4. Configure settings under **WooCommerce → Flexi Abandoned Carts**.

== Frequently Asked Questions ==

= Does this plugin work without WooCommerce? =

No. Flexi Abandoned Cart Recovery requires WooCommerce to be installed and active, as it hooks into the WooCommerce checkout process.

= When is a cart considered abandoned? =

A cart is considered abandoned when a customer has entered their email address during checkout but has not completed the order within the configured time threshold (default: 60 minutes).

= How many recovery emails can I send? =

You can configure multiple follow-up emails at different time intervals. There is no hard limit on the number of emails in a sequence.

= Can I customise the recovery email templates? =

Yes. You can fully customise the subject line, body content, and include dynamic variables such as the customer's name, cart contents, and coupon codes.

= Is this plugin GDPR compliant? =

The plugin only tracks customers who have voluntarily entered their email address during checkout. We recommend updating your store's privacy policy to disclose cart abandonment tracking.

= Does the plugin generate coupon codes automatically? =

Yes. You can enable dynamic coupon generation so that a unique discount coupon is automatically created and included in recovery emails.

= Will the plugin slow down my website? =

No. The plugin uses efficient database queries and background processing to minimise any impact on your store's performance.

= Where can I report a bug or request a feature? =

Please use the [GitHub issue tracker](https://github.com/Darpan-Sarmah/flexi-abandoned-cart/issues) to report bugs or request new features.

== Screenshots ==

1. Dashboard Analytics – Overview of abandoned carts, recovery rate, and revenue recovered.
2. Email Template Editor – Customise recovery email subject lines and body content.
3. Global Settings – Configure abandonment time threshold, email schedule, and coupon rules.
4. Abandoned Carts List – View all captured abandoned carts with customer details and cart value.
5. Coupon Management – Manage dynamically generated discount coupons.

== Changelog ==

= 1.0.0 =
* Initial release.
* Cart abandonment tracking via WooCommerce checkout hooks.
* Automated recovery email sequences with configurable intervals.
* Analytics dashboard showing recovery rates and revenue.
* Dynamic coupon generation for recovery email incentives.
* Email performance tracking (open rates, click-throughs, conversions).
* Global settings panel for all plugin configuration.
* Translation-ready with `.pot` file included.

== Upgrade Notice ==

= 1.0.0 =
Initial release. No upgrade steps required.

== Support ==

* **Documentation:** See the `docs/` folder included with the plugin.
* **Bug Reports & Feature Requests:** [GitHub Issues](https://github.com/Darpan-Sarmah/flexi-abandoned-cart/issues)
* **WordPress.org Support Forum:** https://wordpress.org/support/plugin/flexi-abandon-cart-recovery/