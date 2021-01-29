<?php
/**
 * Handles setting up the location pages & menu items for the plugin.
 *
 * @package    TARecord/LocationAddonForGravityForms
 * @author     Tanner Record <tanner.record@gmail.com>
 * @license    GPL2
 * @since      File available since Release 1.0.0
 */

namespace TARecord\LocationAddonForGravityForms;

/**
 * Establishes the pages & menu items in the admin.
 */
class Settings {

	public const PAGE_ID = 'lagf_locations';

	/**
	 * Sets up hooks & filters.
	 */
	public function init_hooks() {
		add_filter( 'gform_form_actions', [ $this, 'add_form_post_action' ], 10, 2 );
		add_filter( 'gform_toolbar_menu', [ $this, 'add_location_form_edit_menu_option' ], 10, 2 );
		add_filter( 'gform_addon_navigation', [ $this, 'add_location_menu_item' ] );
	}

	/**
	 * Adds Location link to form menu
	 *
	 * @param array $actions  The original array of actions.
	 * @param int   $form_id  The form id.
	 *
	 * @return $actions The array of actions.
	 */
	public function add_form_post_action( $actions, $form_id ) {
		$actions['locations'] = [
			'label'        => __( 'Locations', 'gravityforms' ),
			'title'        => __( 'Posts this form appears on', 'gravityforms' ),
			'url'          => '?page=' . self::PAGE_ID . '&form_id=' . $form_id,
			'capabilities' => 'gravityforms_edit_forms',
			'priority'     => 699,
		];
		return $actions;
	}

	/**
	 * Add Locations link to form edit page
	 *
	 * @param array $menu_items  The menu items to override.
	 * @param int   $form_id     The form id.
	 *
	 * @return array  The menu items to add to the table.
	 */
	public function add_location_form_edit_menu_option( $menu_items, $form_id ) {

		$edit_capabilities = [ 'gravityforms_edit_forms' ];

		$menu_items[ self::PAGE_ID ] = [
			'label'        => __( 'Locations', 'gravityforms' ),
			'short_label'  => esc_html__( 'Locations', 'gravityforms' ),
			'icon'         => '<i class="fa fa-map-marker fa-lg"></i>',
			'title'        => __( 'Posts this form appears on', 'gravityforms' ),
			'url'          => '?page=' . self::PAGE_ID . '&form_id=' . $form_id,
			'menu_class'   => 'gf_form_toolbar_editor',
			'capabilities' => $edit_capabilities,
			'priority'     => 699,
		];

		return $menu_items;
	}

	/**
	 * Adds the Form Locations Menu Item
	 *
	 * @param array $menu_items The menu items to override.
	 *
	 * @return array An array of menu items.
	 */
	public function add_location_menu_item( $menu_items = [] ) {
		$menu_items[] = [
			'name'       => self::PAGE_ID,
			'label'      => 'Locations',
			'callback'   => [ $this, 'add_location_view' ],
			'permission' => 'edit_posts',
		];
		return $menu_items;
	}

	/**
	 * Renders the Locations page.
	 *
	 * @return void
	 */
	public function add_location_view() {
		include_once dirname( __FILE__ ) . '/views/locations.php';
	}

}
