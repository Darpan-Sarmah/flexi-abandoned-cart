=== Flexi Abandon Cart Recovery ===
Contributors: startandgrow
Tags: abandoned cart, cart recovery, woocommerce, email marketing, cart abandonment
Requires at least: 5.6
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 7.4
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Recover lost revenue by capturing abandoning shoppers' emails and sending automated follow-up emails with optional discount coupons.

== Description ==

**Flexi Abandon Cart Recovery** helps WooCommerce store owners recover revenue from customers who add items to their cart but leave without completing the purchase.

The plugin captures email addresses at the checkout page, detects cart abandonment, and automatically sends personalised follow-up emails — optionally including dynamic discount coupons — to bring customers back.

= Key Features =

* **Automatic Cart Tracking** – Detects when a WooCommerce cart is abandoned based on a configurable inactivity timeout.
* **Email Recovery Campaigns** – Send one or more timed follow-up emails to recovering shoppers.
* **Dynamic Coupon Generation** – Automatically create unique discount coupons and embed them in recovery emails.
* **Email Templates** – Built-in rich-text editor (TinyMCE) with dynamic shortcode placeholders such as `{{cart_details}}`, `{{cart_checkout_url}}`, `{{coupon_code}}`, and more.
* **Rule Engine** – Define audience rules based on product, category, cart value, or user role to control which abandoned carts trigger emails.
* **Email Triggers** – Configure multiple triggers with different templates, rules, and send-time delays.
* **Detailed Logs & Analytics** – Track emails sent, opened, clicked, and conversions (purchases) per campaign.
* **Global Settings** – Configure cart abandonment timeout, email sender details, GDPR consent message, and more.
* **Scheduler** – Built-in WordPress cron integration that periodically checks for abandoned carts and sends recovery emails automatically.
* **Multisite Compatible** – Works in WordPress Multisite environments.
* **Internationalization Ready** – Fully translatable with `.pot` file support.

= How It Works =

1. A visitor adds items to their WooCommerce cart and begins the checkout process.
2. The plugin captures their email address.
3. If the checkout is not completed within the configured abandonment timeout, the cart is marked as abandoned.
4. The scheduler runs at regular intervals and triggers recovery emails according to the configured rules and triggers.
5. Each email can include a personalised dynamic coupon to incentivise the purchase.
6. When the customer clicks the recovery link and completes their order, the cart is automatically marked as recovered.

= Available Email Shortcodes =

* `{{cart_details}}` – Renders an HTML table of the abandoned cart items.
* `{{cart_checkout_url}}` – Inserts a direct link back to the cart/checkout page.
* `{{coupon_code}}` – Inserts the generated coupon code.
* `{{coupon_discount}}` – Displays the coupon discount amount or percentage.
* `{{email_name}}` – Inserts the store name configured in Global Settings.
* `{{user_name}}` – Inserts the customer's display name.

== Installation ==

= Minimum Requirements =

* WordPress 5.6 or higher
* WooCommerce 5.0 or higher
* PHP 7.4 or higher
* MySQL 5.6 / MariaDB 10.0 or higher

= Automatic Installation (Recommended) =

1. Log in to your WordPress admin dashboard.
2. Go to **Plugins → Add New**.
3. Search for **Flexi Abandon Cart Recovery**.
4. Click **Install Now**, then **Activate**.

= Manual Installation =

1. Download the plugin `.zip` file.
2. Log in to your WordPress admin dashboard.
3. Go to **Plugins → Add New → Upload Plugin**.
4. Choose the downloaded `.zip` file and click **Install Now**.
5. After installation, click **Activate Plugin**.

Alternatively, extract the `.zip` file and upload the `flexi-abandon-cart-recovery` folder to `/wp-content/plugins/` via FTP, then activate through the Plugins menu.

= After Activation =

1. Navigate to **Flexi Cart Recovery** in the WordPress admin sidebar.
2. Review and update **Global Settings** (abandonment timeout, sender email, GDPR message).
3. Customise the default **Email Templates** or create new ones.
4. Set up **Email Triggers** to define when and to whom recovery emails are sent.
5. Optionally create **Dynamic Coupons** to include incentives in recovery emails.
6. Monitor results in the **Dashboard** and **Email Logs** sections.

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes. Flexi Abandon Cart Recovery is built specifically for WooCommerce and requires it to be installed and active.

= How is a cart considered "abandoned"? =

A cart is marked as abandoned when no checkout activity is detected for the duration specified in **Global Settings → Cart Abandonment Time** (default: 10 minutes).

= Can I send more than one recovery email? =

Yes. Create multiple **Email Triggers**, each configured with a different email template and a different **Send After** delay (e.g., 1 hour, 24 hours, 3 days).

= How do dynamic coupons work? =

When a trigger fires, the plugin checks if the assigned email template has coupons enabled. If so, it creates a new WooCommerce coupon with the configured discount type, amount, and expiry date and embeds the code in the email.

= Are emails sent immediately after abandonment? =

No. The plugin uses WordPress cron (scheduled events) to check for abandoned carts and send emails. The actual send time depends on the **Send After** delay configured for each trigger and the frequency at which WordPress cron runs.

= Is the plugin GDPR-friendly? =

The plugin stores customer email addresses only for users who have already entered them during the checkout process. A configurable GDPR consent message can be displayed. You remain responsible for complying with applicable privacy regulations.

= Does it work with caching plugins? =

The cart tracking relies on JavaScript and AJAX calls initiated during checkout interaction. Most popular caching plugins (WP Rocket, W3 Total Cache, etc.) should be compatible because checkout pages are typically excluded from caching.

= Does it work in WordPress Multisite? =

The plugin is compatible with WordPress Multisite. Each sub-site maintains its own plugin data tables.

= How do I test that emails are being sent? =

Navigate to **Email Templates**, open a template, and use the **Send Test Email** button to send a preview of the template to any email address.

== Screenshots ==

1. Dashboard overview showing abandoned cart stats and revenue recovered.
2. Global Settings page.
3. Email Templates list.
4. Email Template editor with TinyMCE and shortcode reference.
5. Email Triggers configuration.
6. Dynamic Coupons management.
7. Email Logs with open, click, and conversion tracking.
8. Abandoned cart user details.

== Changelog ==

= 1.0.0 =
* Initial release.
* Abandoned cart tracking for WooCommerce.
* Automated email recovery campaigns.
* Dynamic coupon generation.
* Rule-based email triggers.
* Detailed email logs and analytics dashboard.
* TinyMCE email template editor.
* WordPress cron scheduler integration.
* GDPR consent message configuration.

== Upgrade Notice ==

= 1.0.0 =
Initial release. No upgrade steps required.
