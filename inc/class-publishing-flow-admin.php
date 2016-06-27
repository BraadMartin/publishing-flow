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

		// Output our custom JS templates on post edit screens.
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_print_footer_scripts' ) );

		// Include our custom requirements box in the Publish metabox.
		add_action( 'post_submitbox_misc_actions', array( $this, 'include_requirements_box' ), 99 );

		// Handle redirect after clicking Publish Flow button.
		add_filter( 'redirect_post_location', array( $this, 'customizer_redirect' ), 10, 2 );

		// Enqueue Customizer scripts and styles.
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_controls_enqueue_scripts' ) );

		// Modify Customizer sections and controls.
		add_action( 'customize_register', array( $this, 'customize_register' ), 30 );

		// Modify Customizer panels.
		add_filter( 'customize_loaded_components', array( $this, 'customize_loaded_components' ), 30, 2 );

		// Output our custom JS templates on our Customizer screen.
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
			array( 'jquery', 'wp-util', 'underscore' ),
			PUBLISHING_FLOW_VERSION,
			true
		);

		wp_enqueue_style(
			'publishing-flow-admin',
			PUBLISHING_FLOW_URL . 'css/publishing-flow-admin.css',
			array(),
			PUBLISHING_FLOW_VERSION
		);

		$url            = $this->build_customizer_url( $post->ID );

		$publish_action = ( $this->if_scheduled_post( $post->ID ) ) ? 'schedule' : 'publish';
		$data           = $this->build_data_array( $post->ID );
		$extra_data     = array(
			'buttonUrl'     => $url,
			'publishAction' => $publish_action,
		);

		$data = array_merge( $data, $extra_data );

		wp_localize_script( 'publishing-flow-admin', 'publishingFlowData', $data );
	}

	/**
	 * Output our custom JS templates on post edit screens.
	 */
	function admin_print_footer_scripts() {

		$screen = get_current_screen();

		// Only on post edit screens.
		if ( $screen->base !== 'post' ) {
			return;
		}

		// Control templates.
		include_once PUBLISHING_FLOW_PATH . 'templates/required-primary.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/optional-primary.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/required-meta.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/optional-meta.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/required-group.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/optional-group.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/required-taxonomy.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/optional-taxonomy.php';
	}

	/**
	 * Include our custom requirements box in the Publish metabox.
	 *
	 * @param  WP_Post  $post  The current post object.
	 */
	public function include_requirements_box( $post ) {

		$data = $this->build_data_array( $post->ID );

		if ( $data['requirementsMet'] ) {
			$output = sprintf(
				'<span class="%s"></span>%s<span class="%s"></span>',
				'dashicons dashicons-yes',
				__( 'All required fields have a value', 'publishing-flow' ),
				'dashicons dashicons-arrow-down'
			);
		} else {
			$output = sprintf(
				'<span class="%s"></span>%s<span class="%s"></span>',
				'dashicons dashicons-no-alt',
				__( 'Required fields are missing values', 'publishing-flow' ),
				'dashicons dashicons-arrow-down'
			);
		}

		$output = sprintf(
			'<div class="%s"><div class="%s">%s</div></div>',
			'publishing-flow-requirements-wrap',
			'publishing-flow-requirements-status',
			$output
		);

		echo $output;
	}

	/**
	 * Redirect to the Customizer when the Publish Flow button is clicked.
	 *
	 * @param   string  $location  The redirect URL.
	 * @param   int     $post_id   The post ID.
	 *
	 * @return  string             The updated redirect URL.
	 */
	public function customizer_redirect( $location, $post_id ) {

		if ( isset( $_POST['pf-action'] ) && 'enter-publishing-flow' === $_POST['pf-action'] ) {
			$location = $this->build_customizer_url( $post_id );
		}

		return $location;
	}

	/**
	 * Enqueue Customizer scripts and styles.
	 */
	public function customize_controls_enqueue_scripts() {

		// Bail if we're not serving Publishing Flow or don't have a valid post ID.
		if ( empty( $_GET['publishing-flow'] ) || empty( $_GET['post-id'] ) ) {
			return;
		}

		// Check for an already registered version of featherlight,
		// and register ours if none is found.
		if ( ! wp_script_is( 'featherlight', 'registered' ) ) {
			wp_register_script(
				'featherlight',
				PUBLISHING_FLOW_URL . 'vendor/featherlight/featherlight.min.js',
				array( 'jquery' ),
				'1.4.0',
				true
			);
		}
		if ( ! wp_style_is( 'featherlight', 'registered' ) ) {
			wp_register_style(
				'featherlight',
				PUBLISHING_FLOW_URL . 'vendor/featherlight/featherlight.min.css',
				array(),
				'1.4.0'
			);
		}

		wp_enqueue_script(
			'publishing-flow-customizer',
			PUBLISHING_FLOW_URL . 'js/publishing-flow-customizer.js',
			array( 'jquery', 'wp-util', 'underscore', 'featherlight' ),
			PUBLISHING_FLOW_VERSION,
			true
		);

		wp_enqueue_style(
			'publishing-flow-customizer',
			PUBLISHING_FLOW_URL . 'css/publishing-flow-customizer.css',
			array( 'featherlight' ),
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
	 * @param   int  $post_id  The post ID to use.
	 *
	 * @return  array          The data object.
	 */
	public function build_data_array( $post_id ) {

		// Set up the post object.
		// Note: In some cases we could pass the $post object into this function, and this
		// function used to support that, but in those situations we don't always have the
		// post fields filtered for display, so re-querying for the post here is necessary.
		$post = get_post( $post_id, 'object', 'display' );

		// Grab all post meta.
		$meta = get_metadata( 'post', $post->ID );

		// Convert post meta into a simple array.
		foreach ( $meta as $key => $value ) {
			if ( isset( $meta[ $key ] ) ) {
				$meta[ $key ] = $value[0];
			} else {
				unset( $meta[ $key ] );
			}
		}

		// Grab all taxonomies for the post type.
		$taxonomies = get_object_taxonomies( $post->post_type, 'names' );

		// Grab all post terms and convert into a simple array.
		$terms = array();

		foreach ( $taxonomies as $tax ) {
			$tax_terms = get_the_terms( $post, $tax );

			if ( empty( $tax_terms ) ) {
				continue;
			}

			if ( ! isset( $terms[ $tax ] ) ) {
				$terms[ $tax ] = array();
			}

			foreach ( $tax_terms as $term_obj ) {
				$terms[ $tax ][ $term_obj->term_id ] = $term_obj->name;
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

		// Get all required taxonomies.
		$required_tax = $this->get_required_taxonomies( $post->post_type );
		$optional_tax = $this->get_optional_taxonomies( $post->post_type );

		$req_primary = array();
		$opt_primary = array();
		$req_meta    = array();
		$opt_meta    = array();
		$req_group   = array();
		$opt_group   = array();
		$req_tax     = array();
		$opt_tax     = array();

		// Build custom primary and meta objects that contain everything we need
		// to render the control for each field.
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
		foreach ( $required_tax as $tax => $arr ) {

			$tax_value = ( isset( $terms[ $tax ] ) ) ? $terms[ $tax ] : array();

			if ( ! empty( $tax_value ) ) {
				$tax_value = implode( ', ', $tax_value );
			}

			// Handle missing values.
			$label      = $arr['label'] ?: $tax;
			$has_value  = $arr['has_value'] ?: '';
			$no_value   = $arr['no_value'] ?: '';
			$show_value = ( $arr['show_value'] );

			$req_tax[ $tax ] = array(
				'label'     => $label,
				'value'     => $tax_value,
				'hasValue'  => $has_value,
				'noValue'   => $no_value,
				'showValue' => $show_value,
			);
		}
		foreach ( $optional_tax as $tax => $arr ) {

			$tax_value = ( isset( $terms[ $tax ] ) ) ? $terms[ $tax ] : array();

			if ( ! empty( $tax_value ) ) {
				$tax_value = implode( ', ', $tax_value );
			}

			// Handle missing values.
			$label      = $arr['label'] ?: $tax;
			$has_value  = $arr['has_value'] ?: '';
			$no_value   = $arr['no_value'] ?: '';
			$show_value = ( $arr['show_value'] );

			$opt_tax[ $tax ] = array(
				'label'     => $label,
				'value'     => $tax_value,
				'hasValue'  => $has_value,
				'noValue'   => $no_value,
				'showValue' => $show_value,
			);
		}

		// Confirm that all of the required fields have a value.
		$requirements_met = $this->check_requirements_met( $req_primary, $req_meta, $req_group, $req_tax );

		// Get the edit link.
		$edit_link = get_edit_post_link( $post->ID );

		// Check the domain to allow for overriding requirements in a development environment.
		$dev_domain = $this->get_dev_domain();
		if ( $dev_domain === $_SERVER['HTTP_HOST'] ) {
			$requirements_met = true;
		}

		// Generate a nonce.
		$publish_nonce = wp_create_nonce( 'pf-publish' );

		// Determine whether the post should be scheduled.
		$scheduled = $this->if_scheduled_post( $post );

		// Format the publish date for display.
		$post_date = get_the_date( 'F j, Y \a\t g:ia', $post->ID );

		$data = array(
			'post'                     => $post,
			'meta'                     => $meta,
			'requiredPrimary'          => $req_primary,
			'optionalPrimary'          => $opt_primary,
			'requiredMeta'             => $req_meta,
			'optionalMeta'             => $opt_meta,
			'requiredGroup'            => $req_group,
			'optionalGroup'            => $opt_group,
			'requiredTax'              => $req_tax,
			'optionalTax'              => $opt_tax,
			'editLink'                 => $edit_link,
			'requirementsMet'          => intval( $requirements_met ),
			'defaultDevice'            => 'mobile',
			'publishNonce'             => $publish_nonce,
			'scheduled'                => intval( $scheduled ),
			'postDate'                 => $post_date,
			'publishLabel'             => __( 'Publish Flow', 'publishing-flow' ),
			'scheduleLabel'            => __( 'Schedule Flow', 'publishing-flow' ),
			'doPublishLabel'           => __( 'Publish', 'publishing-flow' ),
			'doScheduleLabel'          => __( 'Schedule', 'publishing-flow' ),
			'requiredLabel'            => __( 'Required', 'publishing-flow' ),
			'optionalLabel'            => __( 'Optional', 'publishing-flow' ),
			'publishDateLabel'         => __( 'Publish Date', 'publishing-flow' ),
			'publishedOnLabel'         => __( 'This post will be published', 'publishing-flow' ),
			'scheduledOnLabel'         => __( 'This post will be scheduled to publish on', 'publishing-flow' ),
			'publishNowLabel'          => __( 'immediately', 'publishing-flow' ),
			'infoSectionLabel'         => __( 'Welcome to Publishing Flow', 'publishing-flow' ),
			'infoSectionContent'       => __( "Before you can publish you'll need to click through each of the device preview icons on the bottom of this panel", 'publishing-flow' ),
			'reqNotification'          => __( "Woah there, looks like this post is still missing a required field!", 'publishing-flow' ),
			'reqNotificationLink'      => __( 'Visit the edit screen to fix this.', 'publishing-flow' ),
			'deviceNotification'       => __( "Woah there, looks like you haven't yet previewed this post on all screen sizes.", 'publishing-flow' ),
			'deviceNotificationAction' => __( 'Click through each device', 'publishing-flow' ),
		);

		/**
		 * Allow the data array to be filtered.
		 *
		 * @param  array    $data  The data array.
		 * @param  WP_Post  $post  The current post object.
		 */
		return apply_filters( 'publishing_flow_data_array', $data, $post );
	}

	/**
	 * Check whether all the required fields have a value.
	 *
	 * @param   array  $req_primary  The array of required primary fields.
	 * @param   array  $req_meta     The array of required meta fields.
	 * @param   array  $req_group    The array of required meta field groups.
	 * @param   array  $req_tax      The array of required taxonomies.
	 *
	 * @return  int                  Whether or not all required fields have a value.
	 */
	public function check_requirements_met( $req_primary, $req_meta, $req_group, $req_tax ) {

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
		if ( $requirements_met ) {
			foreach ( $req_tax as $i => $arr ) {
				if ( empty( $arr['value'] ) ) {
					$requirements_met = false;
					break;
				}
			}
		}

		return $requirements_met;
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

		// Get all registered sections.
		$sections = $wp_customize->sections();

		// Remove all registered sections.
		foreach( $sections as $section ) {
			$wp_customize->remove_section( $section->id );
		}
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

		$post_id = (int)$_GET['post-id'];

		// Control templates.
		include_once PUBLISHING_FLOW_PATH . 'templates/required-primary.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/optional-primary.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/required-meta.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/optional-meta.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/required-group.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/optional-group.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/required-taxonomy.php';
		include_once PUBLISHING_FLOW_PATH . 'templates/optional-taxonomy.php';

		// Confirmation templates.
		echo $this->publish_success_template( $post_id );
		echo $this->schedule_success_template( $post_id );
		echo $this->publish_fail_template( $post_id );
	}

	/**
	 * Build and return our Publish Success template;
	 *
	 * @param   int  $post_id  The current post ID.
	 *
	 * @return  string  The template.
	 */
	public function publish_success_template( $post_id ) {

		ob_start();

		?>
		<div class="pf-publish-success pf-lightbox">
			<h1 class="pf-heading">
				<?php _e( 'Success!', 'publishing-flow' ); ?>
			</h1>
			<h2 class="pf-heading">
				<?php _e( 'Your post has been published.', 'publishing-flow' ); ?>
			</h2>
			<p><?php _e( 'What do you want to do now?', 'publishing-flow' ); ?></p>
			<a class="pf-button button pf-view-post" href="<?php // This gets filled in by JS. ?>"><?php _e( 'View Post', 'publishing-flow' ); ?></a>
			<a class="pf-button button pf-edit-post" href="<?php // This gets filled in by JS. ?>"><?php _e( 'Keep Editing', 'publishing-flow' ); ?></a>
		</div>
		<?php

		return apply_filters( 'publishing_flow_publish_success_template', ob_get_clean() );
	}

	/**
	 * Build and return our Schedule Success template;
	 *
	 * @param   int  $post_id  The current post ID.
	 *
	 * @return  string  The template.
	 */
	public function schedule_success_template( $post_id ) {

		ob_start();

		?>
		<div class="pf-schedule-success pf-lightbox">
			<h1 class="pf-heading">
				<?php _e( 'Success!', 'publishing-flow' ); ?>
			</h1>
			<h2 class="pf-heading">
				<?php _e( 'Your post has been scheduled.', 'publishing-flow' ); ?>
			</h2>
			<p><?php _e( 'What do you want to do now?', 'publishing-flow' ); ?></p>
			<a class="pf-button button pf-view-post" href="<?php // This gets filled in by JS. ?>"><?php _e( 'View Post', 'publishing-flow' ); ?></a>
			<a class="pf-button button pf-edit-post" href="<?php // This gets filled in by JS. ?>"><?php _e( 'Keep Editing', 'publishing-flow' ); ?></a>
		</div>
		<?php

		return apply_filters( 'publishing_flow_schedule_success_template', ob_get_clean() );
	}

	/**
	 * Build and return our Publish Fail template;
	 *
	 * @param   int  $post_id  The current post ID.
	 *
	 * @return  string  The template.
	 */
	public function publish_fail_template( $post_id ) {

		ob_start();

		?>
		<div class="pf-publish-fail pf-lightbox">
			<h1 class="pf-heading">
				<?php _e( 'Whoops, something went wrong...', 'publishing-flow' ); ?>
			</h1>
			<h2 class="pf-heading">
				<?php _e( 'Your post could not be published or scheduled at this time.', 'publishing-flow' ); ?>
			</h2>
			<p><?php _e( 'Please go back to the edit screen and try again.', 'publishing-flow' ); ?></p>
			<a class="pf-button button pf-edit-post" href="<?php // This gets filled in by JS. ?>"><?php _e( 'Return to Edit Screen', 'publishing-flow' ); ?></a>
		</div>
		<?php

		return apply_filters( 'publishing_flow_publish_fail_template', ob_get_clean() );
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

		$response->status   = 'success';
		$response->postLink = get_permalink( $post->ID );

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
			),
			'post_content' => array(
				'label'      => __( 'Post Content', 'publishing-flow' ),
				'show_value' => false,
				'has_value'  => __( 'The post has content', 'publishing-flow' ),
				'no_value'   => __( 'The post is missing content', 'publishing-flow' ),
			),
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

	/**
	 * Return an array of all required taxonomies.
	 *
	 * @param   string  $post_type  The post type.
	 *
	 * @return  array               The array of required taxonomies.
	 */
	public function get_required_taxonomies( $post_type ) {

		$taxonomies = array();

		return apply_filters( 'publishing_flow_required_taxonomies', $taxonomies, $post_type );
	}

	/**
	 * Return an array of all optional taxonomies.
	 *
	 * @param   string  $post_type  The post type.
	 *
	 * @return  array               The array of optional taxonomies.
	 */
	public function get_optional_taxonomies( $post_type ) {

		$taxonomies = array();

		return apply_filters( 'publishing_flow_optional_taxonomies', $taxonomies, $post_type );
	}

	/**
	 * Return a domain for development environments.
	 *
	 * @return  string  The dev domain.
	 */
	public function get_dev_domain() {

		/**
		 * Allow a development domain to be specified that will bypass the requirements
		 * check (allowing for easier publishing of test posts and pages).
		 *
		 * @param  string  The dev URL.
		 */
		return apply_filters( 'publishing_flow_dev_domain', '' );
	}
}
