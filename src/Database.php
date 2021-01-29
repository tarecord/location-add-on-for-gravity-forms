<?php
/**
 * The class for interacting with database tables.
 *
 * @package TARecord/LocationAddOnForGravityForms
 */

namespace TARecord\LocationAddonForGravityForms;

/**
 * Handles interacting with the WPDB class.
 */
class Database {

	/**
	 * The WPDB class instance.
	 *
	 * @var object
	 */
	private $wpdb;

	/**
	 * The main location table name.
	 */
	public const TABLE_NAME = 'lagf_form_page';

	/**
	 * Constructor.
	 */
	public function __construct() {

		global $wpdb;
		$this->wpdb = $wpdb;
	}

	/**
	 * Create a database table.
	 *
	 * @param string $name The unprefixed name of the table to create.
	 *
	 * @return void
	 */
	public function create_table( $name = null ) {

		// Bail if no name was provided.
		if ( is_null( $name ) ) {
			return;
		}

		$table_name      = $this->wpdb->prefix . $name;
		$charset_collate = $this->wpdb->get_charset_collate();

		if ( $this->table_exists( $table_name ) ) {
			return;
		}

		// Create the table.
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			form_id mediumint(8) NOT NULL,
			post_id bigint(20) NOT NULL,
			PRIMARY KEY (id)
			) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Delete a database table.
	 *
	 * @param string $name The unprefixed name of the table to delete.
	 */
	public function delete_table( $name = null ) {

		$table_name = $this->wpdb->prefix . $name;

		// Check if table exists before trying to delete it.
		if ( $this->table_exists( $table_name ) ) {

			$this->wpdb->query(
				$this->wpdb->prepare( 'DROP TABLE %s;', $table_name ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			);
		}

	}

	/**
	 * Queries the database by form ID and returns an array of matching rows.
	 *
	 * @link https://developer.wordpress.org/reference/classes/wpdb/#select-generic-results
	 *
	 * @param int $form_id The form ID.
	 *
	 * @return array An associative array containing all found rows.
	 */
	public function get_rows_by_form_id( $form_id = 0 ) {
		if ( 0 === $form_id ) {
			return [];
		}

		$table_name = $this->wpdb->prefix . self::TABLE_NAME;

		return $this->wpdb->get_results(
			$this->wpdb->prepare( 'SELECT * FROM %s WHERE form_id = %s', $table_name, $form_id ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			'ARRAY_A'
		);
	}

	/**
	 * Queries the database by post ID and returns an array of matching rows.
	 *
	 * @link https://developer.wordpress.org/reference/classes/wpdb/#select-generic-results
	 *
	 * @param int $post_id The form ID.
	 *
	 * @return array An associative array containing all found rows.
	 */
	public function get_rows_by_post_id( $post_id = 0 ) {
		if ( 0 === $post_id ) {
			return [];
		}

		$table_name = $this->wpdb->prefix . self::TABLE_NAME;

		return $this->wpdb->get_results(
			$this->wpdb->prepare( 'SELECT * FROM %s WHERE post_id = %s', $table_name, $post_id ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			'ARRAY_A'
		);
	}


	/**
	 * Handles inserting new rows into the database.
	 *
	 * Basically just a wrapper around $wpdb->insert()
	 *
	 * @link https://developer.wordpress.org/reference/classes/wpdb/insert/
	 *
	 * @param string       $table  The table to insert into.
	 * @param array        $data   The data to insert in the row.
	 * @param array|string $format The format to use when inserting the row.
	 *
	 * @return int|false The number of rows inserted, or false on error.
	 */
	public function insert( $table = '', $data = [], $format = null ) {
		if ( empty( $table ) ) {
			$table = self::TABLE_NAME;
		}

		return $this->wpdb->insert( $table, $data, $format );
	}

	/**
	 * Run a query.
	 *
	 * @param string $table_name The table to check for in the database.
	 *
	 * @return bool
	 */
	private function table_exists( $table_name = null ) {
		if ( is_null( $table_name ) ) {
			$table_name = self::TABLE_NAME;
		}

		$query_result = $this->wpdb->get_var(
			$this->wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		);

		return $query_result === $table_name;
	}
}
