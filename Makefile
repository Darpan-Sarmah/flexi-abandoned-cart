PLUGIN_SLUG  = flexi-abandon-cart-recovery
PLUGIN_DIR   = $(shell basename $(CURDIR))
VERSION      = $(shell cat VERSION)
BUILD_DIR    = /tmp/$(PLUGIN_SLUG)-build
DIST_DIR     = dist
ZIP_FILE     = $(DIST_DIR)/$(PLUGIN_SLUG)-$(VERSION).zip

.PHONY: all build zip clean lint test help

## Default target
all: build

## Install Node.js dependencies
install:
	npm install

## Build minified assets (production)
build: install
	npm run build

## Build assets in development/watch mode
dev: install
	npm run dev

## Lint JavaScript and CSS
lint: install
	npm run lint:js
	npm run lint:css

## Create a distributable ZIP archive (excludes dev files)
zip: build
	@mkdir -p $(DIST_DIR)
	@rm -rf $(BUILD_DIR)
	@mkdir -p $(BUILD_DIR)/$(PLUGIN_SLUG)
	@rsync -av --exclude='.git' \
	           --exclude='.github' \
	           --exclude='node_modules' \
	           --exclude='$(DIST_DIR)' \
	           --exclude='webpack.config.js' \
	           --exclude='package.json' \
	           --exclude='package-lock.json' \
	           --exclude='Makefile' \
	           --exclude='*.map' \
	           --exclude='marketing' \
	           --exclude='tests' \
	           --exclude='tables.txt' \
	           . $(BUILD_DIR)/$(PLUGIN_SLUG)/
	@cd $(BUILD_DIR) && zip -r $(CURDIR)/$(ZIP_FILE) $(PLUGIN_SLUG)/
	@rm -rf $(BUILD_DIR)
	@echo "Distribution ZIP created: $(ZIP_FILE)"

## Bump the version number (usage: make bump VERSION=1.1.0)
bump:
	@if [ -z "$(NEW_VERSION)" ]; then echo "Usage: make bump NEW_VERSION=x.y.z"; exit 1; fi
	@sed -i "s/$(VERSION)/$(NEW_VERSION)/g" VERSION
	@sed -i "s/Version:           $(VERSION)/Version:           $(NEW_VERSION)/" flexi-abandon-cart-recovery.php
	@sed -i "s/\"version\": \"$(VERSION)\"/\"version\": \"$(NEW_VERSION)\"/" plugin.json
	@sed -i "s/\"version\": \"$(VERSION)\"/\"version\": \"$(NEW_VERSION)\"/" package.json
	@sed -i "s/Stable tag: $(VERSION)/Stable tag: $(NEW_VERSION)/" README.txt
	@echo "Version bumped to $(NEW_VERSION)"

## Remove build artefacts
clean:
	@rm -rf $(DIST_DIR) $(BUILD_DIR) node_modules

## Show this help
help:
	@echo ""
	@echo "Flexi Abandoned Cart Recovery – Build Commands"
	@echo "-----------------------------------------------"
	@grep -E '^## ' $(MAKEFILE_LIST) | sed 's/## /  /'
	@echo ""
