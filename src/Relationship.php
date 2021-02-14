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
	 * @return int|false Number of rows inserted into the database or false on error.
	 */
	public function save() {

		$row = [
			'form_id' => $this->form_id,
			'post_id' => $this->post_id,
		];

		return ( new Database() )->insert_row( $row, [ '%d', '%d' ] );
	}
}
