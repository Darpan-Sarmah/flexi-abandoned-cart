# Upgrade Guide

This guide describes how to upgrade Flexi Abandoned Cart Recovery for WooCommerce between versions.

---

## General Upgrade Instructions

1. **Back up your site** – Always create a full backup (files + database) before upgrading any plugin.
2. **Upgrade via WordPress admin** – Go to **Plugins → Installed Plugins**, find "Flexi Abandoned Cart Recovery for WooCommerce", and click **Update Now** when an update is available.
3. **Alternatively, upload manually** – Download the latest ZIP from the [GitHub Releases page](https://github.com/Darpan-Sarmah/flexi-abandoned-cart/releases) or WordPress.org, then go to **Plugins → Add New → Upload Plugin** and upload the ZIP.
4. **Check the changelog** – Review [`CHANGELOG.md`](../CHANGELOG.md) for any breaking changes or manual migration steps required for your version.
5. **Test after upgrading** – Visit the plugin settings and the analytics dashboard to confirm everything is working correctly.

---

## Version-Specific Notes

### Upgrading to 1.0.0

This is the initial release. No upgrade steps are required.

---

## Database Migrations

The plugin handles database schema migrations automatically on activation and upgrade via the activator class. If you encounter any database errors after an upgrade:

1. Deactivate and reactivate the plugin to trigger the migration routines.
2. Check `wp-content/debug.log` (with `WP_DEBUG_LOG` enabled) for error messages.
3. If the issue persists, open a support ticket with your WordPress, WooCommerce, PHP versions, and the error message.

---

## Rolling Back

If an upgrade causes issues, you can roll back to the previous version:

1. Deactivate the plugin.
2. Delete the plugin files from `/wp-content/plugins/flexi-abandon-cart-recovery/`.
3. Upload the previous version's ZIP (available from the [GitHub Releases page](https://github.com/Darpan-Sarmah/flexi-abandoned-cart/releases)).
4. Activate the plugin.

---

## Getting Help

See [`docs/SUPPORT.md`](SUPPORT.md) for support options.
