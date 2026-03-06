# Deployment Checklist

Use this checklist before every release of Flexi Abandoned Cart Recovery for WooCommerce.

## Pre-Release Checks

### Code Quality
- [ ] All tests passing
- [ ] Code review complete
- [ ] No debug code left (e.g. `var_dump`, `error_reporting(E_ALL)`, `ini_set('display_errors', 1)`)
- [ ] No hardcoded test URLs or credentials
- [ ] PHP CodeSniffer passes with WordPress Coding Standards

### Version Management
- [ ] Version bumped in `VERSION`
- [ ] Version bumped in `flexi-abandon-cart-recovery.php` (plugin header `Version:` and `FLEXI_ABANDON_CART_RECOVERY_VERSION` constant)
- [ ] Version bumped in `plugin.json`
- [ ] Version bumped in `package.json`
- [ ] `Stable tag:` updated in `README.txt`

### Documentation
- [ ] `CHANGELOG.md` updated with all changes for this release
- [ ] `README.txt` updated (description, FAQ, screenshots if changed)
- [ ] In-code documentation (PHPDoc) is current
- [ ] `docs/` files updated if workflows or setup changed

### Assets
- [ ] CSS/JS assets minified (`npm run build` or `make build`)
- [ ] Images optimised
- [ ] Plugin banner and icon assets present in `assets/` directory

### Security
- [ ] Security scan completed (e.g. WPScan, manual review)
- [ ] All user inputs sanitised and validated
- [ ] All outputs escaped
- [ ] Nonces used for all form submissions and AJAX requests
- [ ] Capability checks in place for all admin actions

### Performance
- [ ] Performance tested on staging environment
- [ ] No unnecessary database queries in front-end page loads
- [ ] Assets only enqueued on relevant pages

### Compatibility
- [ ] Tested with latest WordPress release
- [ ] Tested with latest WooCommerce release
- [ ] Tested with PHP 7.4, 8.0, 8.1, 8.2
- [ ] Tested on a clean WordPress install

### WordPress.org Submission
- [ ] Plugin header complete and correct
- [ ] `README.txt` validated with the [WordPress readme validator](https://wordpress.org/plugins/developers/readme-validator/)
- [ ] `README.txt` `Tested up to:` is accurate
- [ ] Screenshots current and present in `assets/` directory
- [ ] Plugin slug matches directory name

## Release Steps

1. Complete all pre-release checks above.
2. Run `make bump NEW_VERSION=x.y.z` to update version strings across all files.
3. Commit the version bump: `git commit -am "Bump version to x.y.z"`.
4. Tag the release: `git tag -a vx.y.z -m "Release x.y.z"`.
5. Push the tag: `git push origin vx.y.z` — this triggers the automated release workflow.
6. Verify the GitHub Actions release workflow completes successfully.
7. Download and test the release ZIP from the GitHub release page.
8. Upload the ZIP to WordPress.org via SVN (see `docs/MARKETPLACE-UPLOAD.md`).

## Post-Release

- [ ] GitHub release published with correct ZIP
- [ ] WordPress.org SVN tag committed
- [ ] Support forum monitored for issues
- [ ] Release announced (if applicable)
