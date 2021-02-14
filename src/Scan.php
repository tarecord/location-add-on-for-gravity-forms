<?php
/**
 * Handles the process of finding forms on the site.
 *
 * @package TARecord/LocationAddonForGravityForms
 */

namespace TARecord\LocationAddonForGravityForms;

use WP_Query;

/**
 * Handles finding forms on the site.
 */
class Scan {

	/**
	 * The current step.
	 *
	 * @var integer
	 */
	public $step;

	/**
	 * Initialize Hooks.
	 */
	public function init_hooks() {
		add_action( 'wp_ajax_lagf_scan_for_forms', [ $this, 'ajax_process' ] );
		add_action( 'save_post', [ $this, 'search_for_forms_on_save' ], 10, 2 );
	}

	/**
	 * Kick off the scanning process and return ajax response.
	 */
	public function ajax_process() {
		$action     = filter_input( INPUT_POST, 'action' );
		$nonce      = filter_input( INPUT_POST, 'nonce' );
		$this->step = ( isset( $_POST['step'] ) ) ? absint( filter_input( INPUT_POST, 'step' ) ) : 1;
		$total      = filter_input( INPUT_POST, 'total' ) ?? false;

		if ( empty( $action ) || 'lagf_scan_for_forms' !== $action ) {
			return;
		}

		if ( ! wp_verify_nonce( $nonce, 'lagf-process-nonce' ) ) {
			return;
		}

		$posts = $this->get_data();

		foreach ( $posts as $post ) {
			$form_ids = $this->check_for_forms( $post->ID );

			foreach ( $form_ids as $form_id ) {

				$relationship = new Relationship( $form_id, $post->ID );

				if ( ! $relationship->exists() ) {
					$relationship->save();
				}
			}
		}

		$this->step = $this->step + 1;

		$response = [
			'step' => $this->step,
		];

		echo wp_json_encode( $response );
		exit;
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

		$form_ids = $this->check_for_forms( $post_id );

		// Delete the existing relationships if there are any.
		( new Database() )->delete_row(
			[
				'post_id' => $post_id,
			]
		);

		foreach ( $form_ids as $form_id ) {

			( new Relationship() )->save( $form_id, $post_id );

		}
	}

	/**
	 * Check for the form shortcode in the post content.
	 *
	 * @param object $post_id The post object to search in.
	 *
	 * @return mixed The form ids or false.
	 */
	protected function check_for_forms( $post_id ) {

		$post = get_post( $post_id );

		// Grab the content from the post.
		$post_content = stripslashes( $post->post_content );
		$pattern      = get_shortcode_regex( [ 'gravityform' ] );

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

	/**
	 * Get the posts to process.
	 *
	 * @return array The found posts.
	 */
	public function get_data() {

		$args = [
			'post_type'      => [
				'post',
				'page',
			],
			'posts_per_page' => 30,
			'paged'          => $this->step,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		];

		$data = new WP_Query( $args );

		return $data->posts;
	}
}
