# Product Features

## Flexi Abandoned Cart Recovery for WooCommerce – Feature List

---

### Core Features

| Feature | Description |
|---------|-------------|
| **Cart Abandonment Tracking** | Automatically detects abandoned checkouts when a customer enters their email but does not complete the order. |
| **Automated Email Sequences** | Send multiple follow-up emails at configurable intervals after abandonment is detected. |
| **Dynamic Coupon Generation** | Creates unique, single-use WooCommerce discount coupons automatically for inclusion in recovery emails. |
| **Email Template Editor** | Customise subject lines and body content for each email in the sequence using a visual editor. |
| **Dynamic Email Variables** | Insert customer name, cart items, cart total, coupon code, and recovery link into email templates. |
| **Recovery Link** | Each email contains a unique link that restores the customer's exact cart contents. |

---

### Analytics & Reporting

| Feature | Description |
|---------|-------------|
| **Analytics Dashboard** | Overview of abandoned carts, recovered carts, recovery rate percentage, and total revenue recovered. |
| **Email Performance Tracking** | Per-email metrics including send count, open rate, click-through rate, and conversion rate. |
| **Abandoned Carts List** | Paginated list of all captured abandoned carts with customer details, cart value, and current status. |
| **Recovery Status Tracking** | Carts are automatically marked as Recovered when the customer completes the order. |

---

### Settings & Configuration

| Feature | Description |
|---------|-------------|
| **Abandonment Threshold** | Configure how long to wait before a cart is classified as abandoned (e.g. 60 minutes). |
| **Email Schedule** | Set the exact delay for each follow-up email in the recovery sequence. |
| **Coupon Settings** | Configure discount type (percentage or fixed), discount amount, and coupon expiry period. |
| **Data Retention** | Configure how long abandoned cart records are kept before automatic purging. |
| **Logs View** | View a log of all emails sent by the plugin for debugging and auditing purposes. |

---

### Technical Features

| Feature | Description |
|---------|-------------|
| **WooCommerce Integration** | Built on WooCommerce hooks and APIs; no core file modifications. |
| **WP-Cron Scheduling** | Uses WordPress cron for reliable background email scheduling. |
| **Translation Ready** | Fully internationalised with a `.pot` translation template included. |
| **GDPR Friendly** | Only processes data for customers who have entered their email at checkout. |
| **Graceful Degradation** | Shows a clear admin notice and disables gracefully if WooCommerce is not active. |
| **Clean Uninstall** | Removes all plugin data from the database upon uninstallation. |

---

### Compatibility

| Requirement | Value |
|-------------|-------|
| WordPress | 5.9+ (tested to 6.4) |
| WooCommerce | 6.0+ (tested to 8.0) |
| PHP | 7.4+ (tested to 8.2) |
| Multisite | Not supported (single-site only) |
