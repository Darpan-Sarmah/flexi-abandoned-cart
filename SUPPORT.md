# Support — Flexi Abandon Cart Recovery

## Table of Contents

- [How to Report Bugs](#how-to-report-bugs)
- [Support Channels](#support-channels)
- [Before Reporting an Issue](#before-reporting-an-issue)
- [Performance Monitoring](#performance-monitoring)
- [Best Practices](#best-practices)

---

## How to Report Bugs

When reporting a bug, please include as much of the following information as possible so we can reproduce and fix the issue quickly:

### Required Information

1. **Plugin version** — visible in **Plugins → Installed Plugins**.
2. **WordPress version** — visible in **Dashboard → Updates** or via `wp core version`.
3. **WooCommerce version** — visible in **Plugins → Installed Plugins**.
4. **PHP version** — visible in **Tools → Site Health → Info → Server**.
5. **MySQL / MariaDB version** — visible in **Tools → Site Health → Info → Database**.
6. **Steps to reproduce** — A numbered list of the exact steps needed to trigger the bug.
7. **Expected behaviour** — What you expected to happen.
8. **Actual behaviour** — What actually happened.
9. **Error messages** — Any visible error messages, notices, or log entries from `wp-content/debug.log`.
10. **Screenshots or screen recordings** — If applicable.

### Optional but Helpful

- List of other active plugins (to identify conflicts).
- Active theme name and version.
- Relevant database queries or table contents (redact personal data before sharing).

---

## Support Channels

| Channel | Use For |
|---|---|
| **GitHub Issues** | Bug reports and feature requests |
| **GitHub Discussions** | Questions, ideas, and general usage help |
| **WordPress.org Support Forum** | Community support (if the plugin is listed on WordPress.org) |
| **Email** | Critical security issues (do not post vulnerabilities publicly) |

### Security Vulnerabilities

If you discover a security vulnerability, please **do not** open a public GitHub issue. Instead, contact the maintainers directly via email with:

- A description of the vulnerability.
- Steps to reproduce.
- Potential impact.
- Any suggested fixes (optional).

We will acknowledge receipt within 48 hours and aim to release a fix within 7 days for critical issues.

---

## Before Reporting an Issue

Please check the following before opening a new issue:

1. **Search existing issues** on GitHub to see if the problem has already been reported.
2. **Review [TROUBLESHOOTING.md](TROUBLESHOOTING.md)** — many common problems are documented there.
3. **Test with a default theme and only WooCommerce active** — this rules out theme or plugin conflicts.
4. **Check `wp-content/debug.log`** with `WP_DEBUG` and `WP_DEBUG_LOG` enabled.
5. **Verify your server meets the minimum requirements** listed in [INSTALLATION.md](INSTALLATION.md).

---

## Performance Monitoring

### Key Metrics to Watch

| Metric | Where to Find It | Alert If |
|---|---|---|
| Recovery rate | Flexi Cart Recovery → Dashboard | Drops significantly week-over-week |
| Email delivery rate | Email Logs (sent vs. failed) | Failure rate exceeds 5% |
| Open rate | Email Logs (opened / sent) | Below 10% may indicate spam filtering |
| Click-through rate | Email Logs (clicked / sent) | Below 5% may indicate poor email content |
| Database table size | phpMyAdmin / `wp db size` | Tables grow unexpectedly large |
| Cron execution | `wp cron event list` | Events are overdue by more than 30 minutes |

### Monitoring Tools

- **Query Monitor** plugin — Identify slow database queries introduced by the plugin.
- **WP Crontrol** plugin — Inspect and manually trigger cron events.
- **WP Mail SMTP** logs — Track email delivery at the SMTP level.
- **Server monitoring** (New Relic, Datadog, etc.) — Monitor server-side performance impact.

---

## Best Practices

### Email Content

- Personalise emails using the `{{user_name}}` shortcode.
- Keep subject lines concise and relevant (avoid spam trigger words).
- Include a clear call-to-action with `{{cart_checkout_url}}`.
- Test emails in multiple email clients before going live (Gmail, Outlook, Apple Mail).

### Coupon Strategy

- Use coupons selectively — offering a discount on the first recovery email may train customers to abandon carts intentionally.
- Consider sending the first email without a coupon, and adding a coupon only in the second or third follow-up.
- Set coupon expiry dates short enough to create urgency (24–72 hours is common).
- Use the **Individual Use** setting to prevent coupon stacking.

### Email Frequency

- Avoid sending more than 3 recovery emails per abandoned cart.
- Space emails out: e.g., 1 hour → 24 hours → 3 days.
- Honour unsubscribe requests promptly in compliance with CAN-SPAM / GDPR.

### Data Hygiene

- Periodically review and archive or delete expired cart records to keep database tables small.
- Review email logs for recurring delivery failures and investigate the root cause.
- Rotate coupon prefixes occasionally to avoid predictable code patterns.

### Security

- Keep WordPress, WooCommerce, and all plugins up to date.
- Use a strong database password and restrict database user privileges to the minimum required.
- Use HTTPS on your entire site to protect customer email addresses in transit.
- Review the GDPR consent message periodically to ensure compliance with current regulations.
