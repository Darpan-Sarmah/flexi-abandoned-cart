# WordPress.org Marketplace Upload Guide

This guide explains how to publish and update Flexi Abandoned Cart Recovery on the WordPress.org plugin directory.

---

## Prerequisites

- A WordPress.org account with the plugin registered (slug: `flexi-abandon-cart-recovery`).
- Subversion (SVN) installed on your machine (`sudo apt install subversion` or `brew install subversion`).
- Write access to the plugin's SVN repository.

---

## First-Time Submission

1. Submit the plugin for review at https://wordpress.org/plugins/developers/add/.
2. Wait for WordPress.org review approval (typically 1–5 business days).
3. Once approved, you will receive the SVN repository URL.

---

## SVN Repository Structure

The WordPress.org SVN repository uses the following structure:

```
flexi-abandon-cart-recovery/
├── trunk/          ← Current development version
├── tags/
│   └── 1.0.0/      ← Stable release snapshot
└── assets/         ← Banner, icon, and screenshot images
```

---

## Checking Out the SVN Repository

```bash
svn checkout https://plugins.svn.wordpress.org/flexi-abandon-cart-recovery/ wp-org-svn
cd wp-org-svn
```

---

## Uploading a New Release

### Step 1 – Build the Release

```bash
make zip
# Creates dist/flexi-abandon-cart-recovery-x.y.z.zip
```

### Step 2 – Update `trunk/`

```bash
# Remove existing trunk contents (except .svn)
find trunk/ -mindepth 1 -not -path '*/.svn*' -delete

# Copy plugin files to trunk (excluding dev files)
rsync -av \
  --exclude='.git' \
  --exclude='.github' \
  --exclude='node_modules' \
  --exclude='dist' \
  --exclude='webpack.config.js' \
  --exclude='package.json' \
  --exclude='package-lock.json' \
  --exclude='Makefile' \
  --exclude='*.map' \
  --exclude='marketing' \
  --exclude='tests' \
  --exclude='tables.txt' \
  /path/to/local/flexi-abandon-cart-recovery/ trunk/
```

### Step 3 – Create a Tag for the Release

```bash
# Copy trunk to a new version tag
svn copy trunk/ tags/1.0.0/
```

### Step 4 – Upload Assets (First Time or When Updated)

Place the following files in the `assets/` directory of the SVN repo:

| File | Dimensions | Purpose |
|------|-----------|---------|
| `banner-1544x500.png` | 1544 × 500 px | Plugin directory banner (high-DPI) |
| `banner-772x250.png` | 772 × 250 px | Plugin directory banner (standard) |
| `icon-256x256.png` | 256 × 256 px | Plugin icon (high-DPI) |
| `icon-128x128.png` | 128 × 128 px | Plugin icon (standard) |
| `screenshot-1.png` | ≤ 1200 px wide | Dashboard screenshot |
| `screenshot-2.png` | ≤ 1200 px wide | Email editor screenshot |
| `screenshot-3.png` | ≤ 1200 px wide | Global settings screenshot |
| `screenshot-4.png` | ≤ 1200 px wide | Abandoned carts list screenshot |
| `screenshot-5.png` | ≤ 1200 px wide | Coupon management screenshot |

```bash
# Add new files to SVN
svn add assets/*.png
```

### Step 5 – Commit to SVN

```bash
svn status
svn commit -m "Release 1.0.0"
```

---

## Updating an Existing Release

1. Make your code changes locally.
2. Update the version number (`make bump NEW_VERSION=x.y.z`).
3. Update `CHANGELOG.md` and `README.txt`.
4. Follow Steps 1–5 above, replacing the version number accordingly.

---

## Verification Checklist

After committing to SVN:

- [ ] https://wordpress.org/plugins/flexi-abandon-cart-recovery/ shows the new version.
- [ ] Plugin description, screenshots, and FAQ are correct.
- [ ] The download ZIP installs and activates correctly on a clean WordPress install.
- [ ] `Stable tag:` in `README.txt` matches the new tag directory name in SVN.

---

## Useful Links

- SVN repository: https://plugins.svn.wordpress.org/flexi-abandon-cart-recovery/
- Plugin page: https://wordpress.org/plugins/flexi-abandon-cart-recovery/
- Readme validator: https://wordpress.org/plugins/developers/readme-validator/
- Plugin assets guide: https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/
- Plugin handbook: https://developer.wordpress.org/plugins/wordpress-org/
