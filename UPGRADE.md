# Upgrade Guide — Flexi Abandon Cart Recovery

## Table of Contents

- [General Upgrade Instructions](#general-upgrade-instructions)
- [Version-Specific Notes](#version-specific-notes)
- [Breaking Changes](#breaking-changes)
- [Data Migration Notes](#data-migration-notes)
- [Rollback Procedures](#rollback-procedures)

---

## General Upgrade Instructions

> **Always back up your database and files before upgrading any plugin.**

### Step 1 — Create a Backup

1. Back up your WordPress database using your hosting control panel, phpMyAdmin, or a backup plugin.
2. Back up the current plugin files (optional but recommended):
   ```bash
   cp -r /wp-content/plugins/flexi-abandon-cart-recovery /wp-content/plugins/flexi-abandon-cart-recovery.bak
   ```

### Step 2 — Perform the Upgrade

**Via WordPress Admin:**

1. Go to **Dashboard → Updates**.
2. Check the box next to **Flexi Abandon Cart Recovery**.
3. Click **Update Plugins**.

**Via WP-CLI:**

```bash
wp plugin update flexi-abandon-cart-recovery
```

**Manual upgrade:**

1. Download the new version's `.zip` file.
2. Deactivate the plugin in the WordPress admin.
3. Delete the existing plugin folder from `/wp-content/plugins/`.
4. Upload and extract the new version.
5. Activate the plugin.

### Step 3 — Verify the Upgrade

1. Check the plugin version in **Plugins → Installed Plugins**.
2. Navigate to **Flexi Cart Recovery → Dashboard** and confirm data loads correctly.
3. Review **Global Settings** to ensure no settings were reset.
4. Send a **Test Email** to confirm email delivery still works.
5. Check **Email Logs** for any errors.

---

## Version-Specific Notes

### 1.0.0 (Initial Release)

This is the first release. No upgrade from a prior version applies.

---

## Breaking Changes

### 1.0.0

No breaking changes (initial release).

Future breaking changes will be documented here with:

- **What changed** and why.
- **What you need to do** to adapt existing customisations or integrations.

---

## Data Migration Notes

### 1.0.0

No data migration required for the initial release.

For future releases that introduce schema changes, a migration routine will be added to the plugin's activation hook and documented here with:

- Changed table structures.
- New columns and their default values.
- Removed columns (and whether data can be recovered).
- Any required manual SQL to migrate data from older schemas.

---

## Rollback Procedures

If an upgrade causes issues, follow these steps to roll back.

### Step 1 — Deactivate the New Version

1. In the WordPress admin, go to **Plugins → Installed Plugins**.
2. Deactivate **Flexi Abandon Cart Recovery**.

### Step 2 — Restore the Previous Version

**If you made a file backup:**

1. Delete the new plugin folder from `/wp-content/plugins/`.
2. Restore the backup:
   ```bash
   cp -r /wp-content/plugins/flexi-abandon-cart-recovery.bak /wp-content/plugins/flexi-abandon-cart-recovery
   ```

**If you have a previous `.zip`:**

1. Delete the new plugin folder.
2. Upload and extract the previous version's `.zip`.

### Step 3 — Restore the Database (if needed)

If the new version performed database schema migrations that caused data loss or corruption:

1. Use your database backup to restore the affected tables.
2. Via phpMyAdmin: select the database → **Import** → select the `.sql` backup file.
3. Via WP-CLI:
   ```bash
   wp db import backup.sql
   ```

### Step 4 — Reactivate the Previous Version

Activate the plugin and verify normal operation.

### Step 5 — Report the Issue

If you needed to roll back due to a bug, please report it through [SUPPORT.md](SUPPORT.md) so it can be fixed in the next release.
