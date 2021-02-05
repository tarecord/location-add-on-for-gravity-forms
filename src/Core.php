<?php
/**
 * The core plugin class.
 *
 * @package TARecord/LocationAddOnForGravityForms
 */

namespace TARecord\LocationAddonForGravityForms;

use TARecord\LocationAddonForGravityForms\Database;
use TARecord\LocationAddonForGravityForms\Settings;
use TARecord\LocationAddonForGravityForms\Relationship;

/**
 * The core plugin class.
 */
class Core {

	/**
	 * The main plugin file.
	 *
	 * @var __FILE__
	 */
	public $plugin_file;

	/**
	 * The plugin version.
	 *
	 * @var string
	 */
	public $version;

	/**
	 * The constructor.
	 *
	 * @param __FILE__ $plugin_file The main plugin file.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
		$this->version     = ( get_plugin_data( $plugin_file ) )['Version'];
	}

	/**
	 * Initialize all plugin components.
	 */
	public function init() {
		$this->register_hooks();
	}

	/**
	 * Register plugin hooks.
	 */
	public function register_hooks() {
		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_action( 'init', [ $this, 'load_settings' ] );
		add_action( 'save_post', [ ( new Relationship() ), 'search_for_forms_on_save' ], 10, 2 );
	}

	/**
	 * Activate the plugin.
	 */
	public function activate() {
		( new Database() )->create_table( 'lagf_form_page' );
		register_uninstall_hook( __FILE__, self::uninstall() );
	}

	/**
	 * Uninstall the plugin.
	 */
	public static function uninstall() {
		( new Database() )->delete_table( 'lagf_form_page' );
	}

	/**
	 * Load the text domain for translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'location-add-on-for-gravity-forms', false, dirname( plugin_basename( $this->plugin_file ) ) . '/languages' );
	}

	/**
	 * Load the settings.
	 */
	public function load_settings() {
		( new Settings() )->init_hooks();
	}
}
