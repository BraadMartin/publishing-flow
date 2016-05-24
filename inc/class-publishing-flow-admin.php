<?php
/**
 * Publishing Flow Admin class.
 *
 * This class serves as the entry point for all admin functionality.
 */

class Publishing_Flow_Admin {

	/**
	 * The constructor.
	 */
	public function __construct() {
		// Silence is golden.
	}

	/**
	 * Set up hooks.
	 */
	public function init() {

		// Enqueue admin scripts and styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Enqueue Customizer scripts and styles.
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_controls_enqueue_scripts' ) );

		// Modify Customizer sections and controls.
		add_action( 'customize_register', array( $this, 'customize_register' ), 30 );

		// Modify Customizer panels.
		add_filter( 'customize_loaded_components', array( $this, 'customize_loaded_components' ), 30, 2 );
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param   string  $hook  The admin page hook.
	 */
	public function admin_enqueue_scripts( $hook ) {

		// Only on post edit screens.
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		global $post;

		wp_enqueue_script(
			'publishing-flow-admin',
			PUBLISHING_FLOW_URL . 'js/publishing-flow-admin.js',
			array( 'jquery' ),
			PUBLISHING_FLOW_VERSION,
			true
		);

		$url = $this->build_customizer_url( $post->ID );

		$data = array(
			'url' => $url,
		);

		wp_localize_script( 'publishing-flow-admin', 'publishingFlowData', $data );
	}

	/**
	 * Enqueue Customizer scripts and styles.
	 */
	public function customize_controls_enqueue_scripts() {

		// Bail if we don't have a valid post ID.
		if ( empty( $_GET['post-id'] ) ) {
			return;
		}

		wp_enqueue_script(
			'publishing-flow-customizer',
			PUBLISHING_FLOW_URL . 'js/publishing-flow-customizer.js',
			array( 'jquery' ),
			PUBLISHING_FLOW_VERSION,
			true
		);

		// Grab the full post object.
		$post_id = (int)$_GET['post-id'];
		$post    = get_post( $post_id );

		// Grab all post meta.
		$meta = get_metadata( 'post', $post_id );

		// Get all required primary keys.
		$required_primary = $this->get_required_primary_keys();

		// Get all required meta keys.
		$required_meta = $this->get_required_meta_keys();

		$data = array(
			'post'             => $post,
			'meta'             => $meta,
			'required_primary' => $required_primary,
			'required_meta'    => $required_meta,
		);

		wp_localize_script( 'publishing-flow-customizer', 'publishingFlowData', $data );
	}

	/**
	 * Build our Customizer URL.
	 *
	 * @param   int  $post_id  The post ID.
	 *
	 * @return  string         The URL.
	 */
	public function build_customizer_url( $post_id ) {

		$url = admin_url( 'customize.php' );

		// Open the Customizer to the right URL.
		$url = add_query_arg(
			'url',
			urlencode( get_permalink( $post_id ) ),
			$url
		);

		// Set the current post's edit URL as the return URL.
		$url = add_query_arg(
			'return',
			urlencode( get_edit_post_link( $post_id ) ),
			$url
		);

		// Pass a flag that we'll use to scope our controls.
		$url = add_query_arg(
			'publishing-flow',
			'true',
			$url
		);

		// Pass the previewed post's ID.
		$url = add_query_arg(
			'post-id',
			$post_id,
			$url
		);

		return $url;
	}

	/**
	 * Modify Customizer sections and controls.
	 *
	 * @param  WP_Customize_Manager  $wp_customize  The Customizer Manager object.
	 */
	public function customize_register( $wp_customize ) {

		// Bail if we're not serving Publishing Flow.
		if ( empty( $_GET['publishing-flow'] ) ) {
			return;
		}

		/**
		 * Sections.
		 */

		// Get all registered sections.
		$sections = $wp_customize->sections();

		// Remove all registered sections.
		foreach( $sections as $section ) {
			$wp_customize->remove_section( $section->id );
		}

		/**
		 * Controls.
		 */
	}

	/**
	 * Modify Customizer panels.
	 *
	 * @param   array                 $components    The array of registered components.
	 * @param   WP_Customize_Manager  $wp_customize  The Customizer Manager object.
	 *
	 * @return  array                                The modified array of components.
	 */
	public function customize_loaded_components( $components, $wp_customize ) {

		// Only if we're serving Publishing Flow.
		if ( ! empty( $_GET['publishing-flow'] ) ) {
			$components = array_diff( $components, array( 'widgets', 'nav_menus' ) );
		}

		return $components;
	}

	/**
	 * Return an array of all required primary keys.
	 *
	 * @return  array  The array of required primary keys.
	 */
	public function get_required_primary_keys() {

		$primary_keys = array(
			'post_title',
		);

		return apply_filters( 'publishing_flow_required_primary_keys', $primary_keys );
	}

	/**
	 * Return an array of all required meta keys.
	 *
	 * @return  array  The array of required meta keys.
	 */
	public function get_required_meta_keys() {

		$meta_keys = array();

		return apply_filters( 'publishing_flow_required_meta_keys', $meta_keys );
	}
}