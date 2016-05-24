<?php
/**
 * Publishing Flow Admin class.
 *
 * This class serves as the entry point for all admin functionality.
 */

class Publishing_Flow_Admin {

	/**
	 * Our Customizer sections.
	 *
	 * @var Array.
	 */
	private $customizer_sections;

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->customizer_sections = array();
	}

	/**
	 * Set up hooks.
	 */
	public function init() {

		// Enqueue admin scripts and styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Enqueue Customizer scripts and styles.
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_controls_enqueue_scripts' ) );

		// Filter in our Customizer controls.
		//add_action( 'customize_control_active', array( $this, 'customize_control_active' ), 99, 2 );

		// Register Customizer sections and controls.
		add_action( 'customize_register', array( $this, 'customize_register' ), 30 );
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

		wp_enqueue_script(
			'publishing-flow-customizer',
			PUBLISHING_FLOW_URL . 'js/publishing-flow-customizer.js',
			array( 'jquery' ),
			PUBLISHING_FLOW_VERSION,
			true
		);
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

		return $url;
	}

	/**
	 * Filter customizer active sections to only include ours when
	 * the user is actively going through the publishing flow.
	 *
	 * @param   bool                  $active   Whether the Customizer control is active.
	 * @param   WP_Customize_Control  $control  WP_Customize_Control instance.
	 *
	 * @return  bool                            Whether the Customizer control is active.
	 */
	public function customize_control_active( $active, $control ) {

		// Bail if we're not serving Publishing Flow.
		if ( empty( $_GET['publishing-flow'] ) ) {
			return $active;
		}

		$sections = $this->get_customizer_sections();

		if ( in_array( $control->section, $sections ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Return an array of our Customizer sections.
	 *
	 * We only need to run our filter once, so we'll store the results in
	 * a class property the first time this function is called and then
	 * return it from there on subsequent calls.
	 *
	 * @return  array  The array of Customizer sections.
	 */
	public function get_customizer_sections() {

		if ( ! empty( $this->customizer_sections ) ) {
			return $this->customizer_sections;
		}

		$sections = array(
			'publishing_flow_date_section',
			'publishing_flow_publish_section',
		);

		/**
		 * Allow the array of Customizer sections to be customized.
		 *
		 * @param  array  The array of default Publishing Flow sections.
		 */
		$sections = apply_filters( 'publishing_flow_customizer_sections', $sections );

		$this->customizer_sections = $sections;

		return $sections;
	}

	/**
	 * Register our Customizer sections and controls.
	 *
	 * @param  WP_Customize_Manager  $wp_customize  The Customizer Manager object.
	 */
	public function customize_register( $wp_customize ) {

		// Bail if we're not serving Publishing Flow.
		if ( empty( $_GET['publishing-flow'] ) ) {
			return;
		}

		// Remove Core sections and panels.
		$wp_customize->remove_section( 'colors' );
		$wp_customize->remove_section( 'header_image' );
		$wp_customize->remove_section( 'background_image' );
		$wp_customize->remove_section( 'themes' );
		$wp_customize->remove_section( 'title_tagline' );
		$wp_customize->remove_section( 'static_front_page' );
		$wp_customize->remove_panel( 'widgets' );
		$wp_customize->remove_panel( 'nav_menus' );

		/**
		 * Sections.
		 */

		/**
		 * Controls.
		 */
	}
}