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
		add_action( 'init', [ $this, 'load_scan' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
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

	/**
	 * Load the scan process.
	 */
	public function load_scan() {
		( new Scan() )->init_hooks();
	}

	/**
	 * Enqueue assets.
	 */
	public function enqueue_assets() {
		$screen = get_current_screen();

		if ( 'forms_page_lagf_locations' !== $screen->id ) {
			return;
		}

		wp_enqueue_script( 'lagf_scan', plugin_dir_url( $this->plugin_file ) . '/assets/js/scan.js', [ 'jquery' ], $this->version, true );
		wp_localize_script(
			'lagf_scan',
			'lagf_scan_obj',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'lagf-process-nonce' ),
			]
		);
		wp_enqueue_style( 'lagf-admin', plugin_dir_url( $this->plugin_file ) . '/assets/css/admin.css', [], $this->version, 'all' );
	}
}
