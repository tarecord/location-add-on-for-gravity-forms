<?php
/**
 * Handles all the logic for creating form-post relationships.
 *
 * @package TARecord/LocationAddonForGravityForms
 */

namespace TARecord\LocationAddonForGravityForms;

use TARecord\LocationAddonForGravityForms\Database;

/**
 * Class for maintaining form-post relationships.
 */
class Relationship {

	/**
	 * The form ID.
	 *
	 * @var int
	 */
	public $form_id;

	/**
	 * The post ID.
	 *
	 * @var int
	 */
	public $post_id;

	/**
	 * Constructor.
	 *
	 * @param int $form_id The form ID.
	 * @param int $post_id The post ID.
	 */
	public function __construct( $form_id = 0, $post_id = 0 ) {
		$this->form_id = $form_id;
		$this->post_id = $post_id;
	}

	/**
	 * Find a relationship by ID.
	 *
	 * @param int    $id   The ID to find relationships for.
	 * @param string $type The column to use for the relationship.
	 *
	 * @return array An array of relationships.
	 */
	public function find_by_id( $id = 0, $type = 'form' ) {

		$relationships = [];

		if ( 'form' === $type ) {
			$relationships = ( new Database() )->get_rows_by_form_id( $id );
		} else {
			$relationships = ( new Database() )->get_rows_by_post_id( $id );
		}

		return $relationships;
	}

	/**
	 * Saves the relationship in the database.
	 *
	 * @param int $form_id The form ID.
	 * @param int $post_id The post ID.
	 *
	 * @return int|false Number of rows inserted into the database or false on error.
	 */
	public function save( $form_id = 0, $post_id = 0 ) {
		if ( 0 === $form_id || 0 === $post_id ) {
			return false;
		}

		$row = [
			'form_id' => $form_id,
			'post_id' => $post_id,
		];

		return ( new Database() )->insert_row( $row, [ '%d', '%d' ] );
	}

	/**
	 * Save page/form relationship in the database on save
	 *
	 * @param int    $post_id The post id returned from the save_post action.
	 * @param object $post    The post object.
	 *
	 * @return  void
	 */
	public function search_for_forms_on_save( $post_id, $post ) {

		// Don't save relationship if it's a revision.
		if ( wp_is_post_revision( $post ) ) {
			return;
		}

		// Grab the content from the post.
		$content  = stripslashes( $post->post_content );
		$pattern  = get_shortcode_regex( [ 'gravityform' ] );
		$form_ids = $this->check_for_forms( $content, $pattern );

		foreach ( $form_ids as $form_id ) {

			// Delete the existing relationships if there are any.
			( new Database() )->delete_row(
				[
					'post_id' => $post_id,
					'form_id' => $form_id,
				]
			);

			$this->save( $form_id, $post_id );
		}
	}

	/**
	 * Check for the form shortcode in the post content.
	 *
	 * @param object $post_content The post object to search in.
	 * @param string $pattern      The regex pattern to match the form shortcode.
	 *
	 * @return mixed The form ids or false.
	 */
	protected function check_for_forms( $post_content, $pattern ) {

		$matches  = [];
		$form_ids = [];

		if ( function_exists( 'has_block' ) && has_block( 'gravityforms/form', $post_content ) ) {
			$blocks = parse_blocks( $post_content );

			foreach ( $blocks as $block ) {
				if ( 'gravityforms/form' === $block['blockName'] ) {
					array_push( $form_ids, intval( $block['attrs']['formId'] ) );
				}
			}
		} else {
			preg_match_all( '/' . $pattern . '/s', $post_content, $matches );

			// Check if at least 1 shortcode was found.
			if ( '' !== $matches[0][0] ) {
				$forms = $this->get_shortcode_ids( $matches[0] );
				if ( is_array( $forms ) ) {
					$form_ids = array_merge( $form_ids, $forms );
				}
			}
		}

		return ( ! empty( $form_ids ) ) ? $form_ids : false;
	}

	/**
	 * Gets the ids from the form shortcodes.
	 *
	 * @param string $shortcodes The shortcodes to get the IDs from.
	 */
	protected function get_shortcode_ids( $shortcodes = [] ) {

		$form_ids = [];

		foreach ( $shortcodes as $shortcode ) {
			// Use the match to extract the form id from the shortcode.
			if ( preg_match( '~id=[\"\']?([^\"\'\s]+)[\"\']?~i', $shortcode, $form_id ) ) {

				// If we have the form id, add it to the array.
				array_push( $form_ids, intval( $form_id[1] ) );
			}
		}

		if ( ! empty( $form_ids ) ) {
			return $form_ids;
		}

		return false;
	}
}
