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

		// Output our custom JS templates.
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'customize_controls_print_footer_scripts' ) );

		// Ajax handler for the publish post action.
		add_action( 'wp_ajax_pf_publish_post', array( $this, 'ajax_publish_post' ) );
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

		wp_enqueue_style(
			'publishing-flow-admin',
			PUBLISHING_FLOW_URL . 'css/publishing-flow-admin.css',
			array(),
			PUBLISHING_FLOW_VERSION
		);

		$url       = $this->build_customizer_url( $post->ID );
		$scheduled = $this->if_scheduled_post( $post->ID );

		if ( $scheduled ) {
			$label = apply_filters( 'publishing_flow_schedule_button_text', __( 'Schedule Flow', 'publishing-flow' ) );
		} else {
			$label = apply_filters( 'publishing_flow_publish_button_text', __( 'Publish Flow', 'publishing-flow' ) );
		}

		$data = array(
			'buttonUrl'   => $url,
			'buttonLabel' => $label,
		);

		wp_localize_script( 'publishing-flow-admin', 'publishingFlowData', $data );
	}

	/**
	 * Enqueue Customizer scripts and styles.
	 */
	public function customize_controls_enqueue_scripts() {

		// Bail if we're not serving Publishing Flow or don't have a valid post ID.
		if ( empty( $_GET['publishing-flow'] ) || empty( $_GET['post-id'] ) ) {
			return;
		}

		wp_enqueue_script(
			'publishing-flow-customizer',
			PUBLISHING_FLOW_URL . 'js/publishing-flow-customizer.js',
			array( 'jquery', 'wp-util', 'underscore' ),
			PUBLISHING_FLOW_VERSION,
			true
		);

		wp_enqueue_style(
			'publishing-flow-customizer',
			PUBLISHING_FLOW_URL . 'css/publishing-flow-customizer.css',
			array(),
			PUBLISHING_FLOW_VERSION
		);

		$post_id = (int)$_GET['post-id'];

		$data = $this->build_data_array( $post_id );

		wp_localize_script( 'publishing-flow-customizer', 'publishingFlowData', $data );
	}

	/**
	 * Determine if a post has a date in the future.
	 *
	 * @param   int|WP_Post  $post  The post object or ID.
	 *
	 * @return  bool                Whether the post has a date in the future.
	 */
	public function if_scheduled_post( $post ) {

		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		// This logic is taken directly from /wp-includes/post.php
		$time = strtotime( $post->post_date_gmt . ' GMT' );

		if ( $time > time() ) {
			return true;
		} else {
			return false;
		}
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

		// Open the Customizer to the post's preview URL.
		$url = add_query_arg(
			'url',
			urlencode( get_preview_post_link( $post_id ) ),
			$url
		);

		// Set the post's edit URL as the return URL.
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

		/**
		 * Allow the Customizer URL to be filtered.
		 *
		 * @param  int  $post_id  The current post ID.
		 */
		return apply_filters( 'publishing_flow_customizer_url', $url, $post_id );
	}

	/**
	 * Build the data array our JS will use.
	 *
	 * @param   integer  $post_id  The post ID to use.
	 * @return  array              The data object.
	 */
	public function build_data_array( $post_id = 0 ) {

		// Grab the full post object.
		$post = get_post( $post_id );

		// Grab all post meta.
		$meta = get_metadata( 'post', $post_id );

		// Convert post meta into a simple array.
		foreach ( $meta as $key => $value ) {
			if ( isset( $meta[ $key ] ) ) {
				$meta[ $key ] = $value[0];
			} else {
				unset( $meta[ $key ] );
			}
		}

		// Get all required and optional primary keys.
		$required_primary = $this->get_required_primary_keys( $post->post_type );
		$optional_primary = $this->get_optional_primary_keys( $post->post_type );

		// Get all required and optional meta keys.
		$required_meta = $this->get_required_meta_keys( $post->post_type );
		$optional_meta = $this->get_optional_meta_keys( $post->post_type );

		// Get all required and optional meta key groups.
		$required_group = $this->get_required_meta_key_groups( $post->post_type );
		$optional_group = $this->get_optional_meta_key_groups( $post->post_type );

		$req_primary = array();
		$opt_primary = array();
		$req_meta    = array();
		$opt_meta    = array();
		$req_group   = array();
		$opt_group   = array();

		// Build custom primary and meta objects that contain everything we need
		// to render each key's control.
		foreach ( $required_primary as $key => $arr ) {

			// Handle missing values.
			$label      = $arr['label'] ?: $key;
			$has_value  = $arr['has_value'] ?: '';
			$no_value   = $arr['no_value'] ?: '';
			$show_value = ( $arr['show_value'] );

			$req_primary[ $key ] = array(
				'label'     => $label,
				'value'     => $post->$key,
				'hasValue'  => $has_value,
				'noValue'   => $no_value,
				'showValue' => $show_value,
			);
		}
		foreach ( $optional_primary as $key => $arr ) {

			// Handle missing values.
			$label      = $arr['label'] ?: $key;
			$has_value  = $arr['has_value'] ?: '';
			$no_value   = $arr['no_value'] ?: '';
			$show_value = ( $arr['show_value'] );

			$opt_primary[ $key ] = array(
				'label'     => $label,
				'value'     => $post->$key,
				'hasValue'  => $has_value,
				'noValue'   => $no_value,
				'showValue' => $show_value,
			);
		}
		foreach ( $required_meta as $key => $arr ) {
			$meta_value = ( isset( $meta[ $key ] ) ) ? $meta[ $key ] : null;

			// Handle missing values.
			$label      = $arr['label'] ?: $key;
			$has_value  = $arr['has_value'] ?: '';
			$no_value   = $arr['no_value'] ?: '';
			$show_value = ( $arr['show_value'] );

			$req_meta[ $key ] = array(
				'label'     => $label,
				'value'     => $meta_value,
				'hasValue'  => $has_value,
				'noValue'   => $no_value,
				'showValue' => $show_value,
			);
		}
		foreach ( $optional_meta as $key => $arr ) {
			$meta_value = ( isset( $meta[ $key ] ) ) ? $meta[ $key ] : null;

			// Handle missing labels.
			$label      = $arr['label'] ?: $key;
			$has_value  = $arr['has_value'] ?: '';
			$no_value   = $arr['no_value'] ?: '';
			$show_value = ( $arr['show_value'] );

			$opt_meta[ $key ] = array(
				'label'     => $label,
				'value'     => $meta_value,
				'hasValue'  => $has_value,
				'noValue'   => $no_value,
				'showValue' => $show_value,
			);
		}
		foreach ( $required_group as $key => $group ) {

			$label      = $group['label'];
			$show_value = ( $group['show_value'] );
			$has_value  = $group['has_value'] ?: '';
			$no_value   = $group['no_value'] ?: '';
			$meta_keys  = array_keys( $group['meta_keys'] );
			$value      = array();

			foreach ( $group['meta_keys'] as $k => $l ) {
				if ( isset( $meta[ $k ] ) ) {
					$value[] = $l;
				}
			}
			$value = implode( ', ', $value );

			$req_group[ $key ] = array(
				'label'     => $label,
				'keys'      => $meta_keys,
				'value'     => $value,
				'hasValue'  => $has_value,
				'noValue'   => $no_value,
				'showValue' => $show_value,
			);
		}
		foreach ( $optional_group as $key => $group ) {

			$label      = $group['label'];
			$show_value = ( $group['show_value'] );
			$has_value  = $group['has_value'] ?: '';
			$no_value   = $group['no_value'] ?: '';
			$meta_keys  = array_keys( $group['meta_keys'] );
			$value      = array();

			foreach ( $group['meta_keys'] as $k => $l ) {
				if ( isset( $meta[ $k ] ) ) {
					$value[] = $l;
				}
			}
			$value = implode( ', ', $value );

			$opt_group[ $key ] = array(
				'label'     => $label,
				'keys'      => $meta_keys,
				'value'     => $value,
				'hasValue'  => $has_value,
				'noValue'   => $no_value,
				'showValue' => $show_value,
			);
		}

		$edit_link = get_edit_post_link( $post->ID );

		// Confirm that all of the required fields have a value, and if so set a flag.
		$requirements_met = true;

		foreach ( $req_primary as $key => $arr ) {
			if ( empty( $arr['value'] ) ) {
				$requirements_met = false;
				break;
			}
		}
		if ( $requirements_met ) {
			foreach ( $req_meta as $key => $arr ) {
				if ( empty( $arr['value'] ) ) {
					$requirements_met = false;
					break;
				}
			}
		}
		if ( $requirements_met ) {
			foreach ( $req_group as $i => $arr ) {
				if ( empty( $arr['value'] ) ) {
					$requirements_met = false;
					break;
				}
			}
		}

		/**
		 * Allow the default device the Customizer preview shows to be filtered.
		 *
		 * @param  string  The default Customizer preview device.
		 */
		$device = apply_filters( 'publishing_flow_customizer_default_device', 'mobile' );

		// Generate a nonce.
		$publish_nonce = wp_create_nonce( 'pf-publish' );

		$scheduled = $this->if_scheduled_post( $post );

		$data = array(
			'post'            => $post,
			'meta'            => $meta,
			'requiredPrimary' => $req_primary,
			'optionalPrimary' => $opt_primary,
			'requiredMeta'    => $req_meta,
			'optionalMeta'    => $opt_meta,
			'requiredGroup'   => $req_group,
			'optionalGroup'   => $opt_group,
			'editLink'        => $edit_link,
			'requirementsMet' => $requirements_met,
			'defaultDevice'   => $device,
			'publishNonce'    => $publish_nonce,
			'scheduled'       => $scheduled,
		);

		/**
		 * Allow the Customizer data array to be filtered.
		 *
		 * @param  array  $data  The Customizer data array.
		 */
		return apply_filters( 'publishing_flow_customizer_data_array', $data );
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
	 * Output our custom JS templates.
	 */
	public function customize_controls_print_footer_scripts() {

		include_once PUBLISHING_FLOW_PATH . 'templates/required-primary.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/optional-primary.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/required-meta.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/optional-meta.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/required-group.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/optional-group.php';
	}

	/**
	 * Publish or Schedule a post from Publishing Flow.
	 */
	public function ajax_publish_post() {

		// Bail if our nonce is not valid.
		check_ajax_referer( 'pf-publish', 'pf_publish_nonce', true );

		$user = wp_get_current_user();

		// Bail if the current user isn't allowed to publish posts.
		if ( ! $user || ! user_can( $user, 'publish_posts' ) ) {
			_e( 'Sorry, the current user is not allowed to publish posts', 'publishing-flow' );
			wp_die();
		}

		$post = get_post( $_POST['post_id'] );

		// Bail if we don't have a post to publish.
		if ( is_wp_error( $post ) ) {
			_e( 'Sorry, no post to publish was found.', 'publishing-flow' );
			wp_die();
		}

		// Bail if the post is already published or scheduled.
		if ( 'publish' === $post->post_status || 'future' === $post->post_status ) {
			_e( 'Looks like this post has already been published or scheduled', 'publishing-flow' );
			wp_die();
		}

		$response = new stdClass();

		/**
		 * We'll either publish the post or schedule it, so first check the date
		 * and compare to the current time, and if it's in the future then simply
		 * set the status to 'future', otherwise publish it.
		 */
		$scheduled = $this->if_scheduled_post( $post );

		if ( $scheduled ) {

			$old_status        = $post->post_status;
			$post->post_status = 'future';

			wp_update_post( $post );

			wp_transition_post_status( 'future', $old_status, $post );

			$response->outcome = 'scheduled';

		} else {

			wp_publish_post( $post );

			$response->outcome = 'published';
		}

		$response->status = 'success';

		wp_send_json( $response );

		wp_die();
	}

	/**
	 * Return an array of all required primary keys.
	 *
	 * @param   string  $post_type  The post type.
	 *
	 * @return  array               The array of required primary keys.
	 */
	public function get_required_primary_keys( $post_type ) {

		$primary_keys = array(
			'post_title' => array(
				'label'      => __( 'Post Title', 'publishing-flow' ),
				'show_value' => true,
				'has_value'  => __( 'The post has a title', 'publishing-flow' ),
				'no_value'   => __( 'The post is missing a title', 'publishing-flow' ),
			)
		);

		return apply_filters( 'publishing_flow_required_primary_keys', $primary_keys, $post_type );
	}

	/**
	 * Return an array of all optional primary keys.
	 *
	 * @param   string  $post_type  The post type.
	 *
	 * @return  array               The array of optional primary keys.
	 */
	public function get_optional_primary_keys( $post_type ) {

		$primary_keys = array();

		return apply_filters( 'publishing_flow_optional_primary_keys', $primary_keys, $post_type );
	}

	/**
	 * Return an array of all required meta keys.
	 *
	 * @param   string  $post_type  The post type.
	 *
	 * @return  array               The array of required meta keys.
	 */
	public function get_required_meta_keys( $post_type ) {

		$meta_keys = array();

		return apply_filters( 'publishing_flow_required_meta_keys', $meta_keys, $post_type );
	}

	/**
	 * Return an array of all optional meta keys.
	 *
	 * @param   string  $post_type  The post type.
	 *
	 * @return  array               The array of optional meta keys.
	 */
	public function get_optional_meta_keys( $post_type ) {

		$meta_keys = array();

		return apply_filters( 'publishing_flow_optional_meta_keys', $meta_keys, $post_type );
	}

	/**
	 * Return an array of all required meta keys groups.
	 *
	 * A "group" represents multiple meta keys where at least one of them needs to have
	 * a value for the group to be considered as having a value.
	 *
	 * @param   string  $post_type  The post type.
	 *
	 * @return  array               The array of required meta key groups.
	 */
	public function get_required_meta_key_groups( $post_type ) {

		$meta_key_groups = array();

		return apply_filters( 'publishing_flow_required_meta_key_groups', $meta_key_groups, $post_type );
	}

	/**
	 * Return an array of all optional meta key groups.
	 *
	 * A "group" represents multiple meta keys where at least one of them needs to have
	 * a value for the group to be considered as having a value.
	 *
	 * @param   string  $post_type  The post type.
	 *
	 * @return  array               The array of optional meta key groups.
	 */
	public function get_optional_meta_key_groups( $post_type ) {

		$meta_key_groups = array();

		return apply_filters( 'publishing_flow_optional_meta_key_groups', $meta_key_groups, $post_type );
	}
}
