<?php
/**
 * Base Integration class for Flexi Abandoned Cart Recovery.
 *
 * All third-party integrations should extend this abstract class and
 * implement the required methods. Integrations are auto-discovered and
 * loaded by the plugin bootstrap if their enabled flag is set.
 *
 * @link  https://abandoned-cart-recovery
 * @since 1.0.0
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/includes/integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flexi_ACR_Integration_Base
 *
 * @since 1.0.0
 */
abstract class Flexi_ACR_Integration_Base {

	/**
	 * Unique integration ID (e.g. 'mailchimp').
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Human-readable integration name.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Integration description.
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * Integration settings key in WordPress options.
	 *
	 * @var string
	 */
	protected $option_key = '';

	/**
	 * Get integration ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get integration name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get integration description.
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Whether this integration is currently enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		$settings = $this->get_settings();
		return ! empty( $settings['enabled'] );
	}

	/**
	 * Get saved settings for this integration.
	 *
	 * @return array
	 */
	public function get_settings() {
		return (array) get_option( $this->option_key, array() );
	}

	/**
	 * Save settings for this integration.
	 *
	 * @param array $settings Settings to save.
	 */
	public function save_settings( array $settings ) {
		update_option( $this->option_key, $settings );
	}

	/**
	 * Initialize integration hooks. Called after settings are loaded.
	 *
	 * Implementors should add their WordPress action/filter hooks here.
	 *
	 * @since 1.0.0
	 */
	abstract public function init();

	/**
	 * Render the integration settings form fields (HTML).
	 *
	 * @since 1.0.0
	 */
	abstract public function render_settings();
}
