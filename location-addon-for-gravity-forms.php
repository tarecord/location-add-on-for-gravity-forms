<?php
/**
 * Location Add-on For Gravity Forms
 *
 * @category  Plugin
 * @package   LocationAddonForGravityForms
 * @author    Tanner Record <tanner.record@gmail.com>
 * @copyright 2021 Tanner Record
 * @license   GPL-2.0-or-later <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link      https://www.tannerrecord.com/location-add-on-for-gravity-forms
 *
 * @wordpress-plugin
 * Plugin Name:       Location Add-on For Gravity Forms
 * Plugin URI:        https://www.tannerrecord.com/location-add-on-for-gravity-forms
 * Description:       An Add-on for Gravity Forms that displays the posts & pages on which a form has been used.
 * Version:           1.0.0
 * Requires at least: 5.6
 * Requires PHP:      7.3
 * Author:            Tanner Record
 * Author URI:        https://www.tannerrecord.com
 * Text Domain:       location-add-on-for-gravity-forms
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * Include the autoloader.
 */
require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

/**
 * Since this plugin depends on Gravity Forms, we need to check if
 * Gravity Forms is currently active.
 *
 * If not, display an error to the user explaining why this plugin
 * could not be activated.
 */
add_action( 'plugins_loaded', 'lagf_dependency_check' );

/**
 * Checks to see if Gravity Forms is installed, activated and the correct version.
 *
 * @since 1.0.0
 */
function lagf_dependency_check() {

	// If Parent Plugin is NOT active.
	if ( current_user_can( 'activate_plugins' ) && ! class_exists( 'GFForms' ) ) {

		add_action( 'admin_init', 'lagf_deactivate' );
		add_action( 'admin_notices', 'lagf_admin_notice' );

		/**
		 * Deactivate the plugin.
		 */
		function lagf_deactivate() {
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}

		/**
		 * Throw an Alert to tell the Admin why it didn't activate.
		 */
		function lagf_admin_notice() {
			$lagf_child_plugin  = __( 'Location Add-On For Gravity Forms', 'location-add-on-for-gravity-forms' );
			$lagf_parent_plugin = __( 'Gravity Forms', 'location-add-on-for-gravity-forms' );

			echo sprintf(
				'<div class="error"><p>Please activate <strong>%2$s</strong> before activating <strong>%1$s</strong>. For now, the plugin has been deactivated.</p></div>',
				esc_html( $lagf_child_plugin ),
				esc_html( $lagf_parent_plugin )
			);

			// phpcs:disable
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
			// phpcs:enable
		}
	}
}

$lagf_plugin = new \TARecord\LocationAddonForGravityForms\Core();

// Handle activation.
register_activation_hook( __FILE__, [ $lagf_plugin, 'activate' ] );

$lagf_plugin->init();
