/**
 * Publishing Flow Customizer JS.
 */

var PublishingFlowCustomizer = ( function( $, _, wp, data ) {

	'use strict';

	/**
	 * DOM references.
	 */
	var $controls;
	var $header;
	var $info;
	var $footer;

	/**
	 * Initialize.
	 */
	var init = function() {

		// Store some key DOM references.
		$controls = $( '#customize-theme-controls' );
		$header   = $( '#customize-header-actions' );
		$info     = $( '#customize-info' );
		$footer   = $( '#customize-footer-actions' );

		// Add initial classes for styling purposes.
		addInitialClasses();

		// Set default initial preview device.
		setDefaultDevice();

		// Inject our info section.
		injectInfo();

		// Inject our notifications section.
		injectNotifications();

		// Inject our custom controls.
		injectControls();

		// Inject our publish button.
		injectButton();

		// Initialize device preview events.
		initDevicePreview();
	};

	/**
	 * Add initial classes for styling purposes.
	 */
	var addInitialClasses = function() {

		// Add a class to the controls wrapper to indicate Publishing Flow is active.
		$controls.addClass( 'pf-customizer' );

		// If all requirements have been met, add a class to the controls wrapper.
		if ( data.requirementsMet ) {
			$controls.addClass( 'pf-requirements-met' );
		}
	}

	/**
	 * Set the default Customizer preview device.
	 */
	var setDefaultDevice = function() {
		wp.customize.previewedDevice.set( data.defaultDevice );

		// Mark the device as having been clicked.
		$footer.find( '.devices button[data-device="' + data.defaultDevice + '"]' ).addClass( 'pf-clicked' );
	}

	/**
	 * Inject our info section.
	 */
	var injectInfo = function() {

		$info.empty();

		var $ourInfo = $( '<div />' )
			.addClass( 'pf-info' )
			.append(
				$( '<h2 />' ).text( 'Welcome to Publishing Flow' )
			).append(
				$( '<p />' ).text( "Before you can publish you'll need to click through each of the device preview icons on the bottom of this panel" )
			);

		$info.append( $ourInfo );
	}

	/**
	 * Inject our notification sections.
	 */
	var injectNotifications = function() {

		var $reqNotifications = $( '<div />' )
			.addClass( 'pf-notifications' )
			.append(
				$( '<p />' )
					.text( "Woah there, looks like this post is still missing a required field!" ),
				$( '<span />' )
					.addClass( 'dashicons dashicons-warning' ),
				$( '<a />' )
					.attr( 'href', data.editLink )
					.text( 'Visit the edit screen to fix this.' )
			);

		$controls.prepend( $reqNotifications );

		var $deviceNotifications = $( '<div />' )
			.addClass( 'pf-device-notifications' )
			.append(
				$( '<p />' )
					.text( "Woah there, looks like you haven't yet previewed this post on all screen sizes." ),
				$( '<p />' )
					.text( "Click through each device" )
					.append(
						$( '<span />' ).addClass( 'dashicons dashicons-arrow-down-alt' )
					)
			);

		$footer.before( $deviceNotifications );
	}

	/**
	 * Inject our custom controls.
	 */
	var injectControls = function() {

		console.log( data );

		// Define our sections.
		var $sectionRequired = $( '<div />' )
			.addClass( 'pf-section pf-required-section' )
			.append(
				$( '<h2 />' )
					.addClass( 'pf-section-label' )
					.text( 'Required' )
			);
		var $sectionOptional = $( '<div />' )
			.addClass( 'pf-section pf-optional-section' )
			.append(
				$( '<h2 />' )
					.addClass( 'pf-section-label' )
					.text( 'Optional' )
			);

		// Render each required and optional item into each section.
		var reqPrimary = wp.template( 'pf-required-primary' );

		_.each( data.requiredPrimary, function( value, key, list ) {
			$sectionRequired.append(
				reqPrimary({
					key:       key,
					label:     value.label,
					value:     value.value,
					hasValue:  value.hasValue,
					noValue:   value.noValue,
					showValue: value.showValue,
				})
			);
		});

		var reqMeta = wp.template( 'pf-required-meta' );

		_.each( data.requiredMeta, function( value, key, list ) {
			$sectionRequired.append(
				reqMeta({
					key:       key,
					label:     value.label,
					value:     value.value,
					hasValue:  value.hasValue,
					noValue:   value.noValue,
					showValue: value.showValue,
				})
			);
		});

		var reqGroup = wp.template( 'pf-required-group' );

		_.each( data.requiredGroup, function( value, key, list ) {
			$sectionRequired.append(
				reqGroup({
					key:       key,
					keys:      value.keys,
					label:     value.label,
					value:     value.value,
					hasValue:  value.hasValue,
					noValue:   value.noValue,
					showValue: value.showValue,
				})
			);
		});

		var optMeta = wp.template( 'pf-optional-meta' );

		_.each( data.optionalMeta, function( value, key, list ) {
			$sectionOptional.append(
				optMeta({
					key:       key,
					label:     value.label,
					value:     value.value,
					hasValue:  value.hasValue,
					noValue:   value.noValue,
					showValue: value.showValue,
				})
			);
		});

		var optGroup = wp.template( 'pf-optional-group' );

		_.each( data.optionalGroup, function( value, key, list ) {
			$sectionOptional.append(
				optPrimary({
					key:       key,
					keys:      value.keys,
					label:     value.label,
					value:     value.value,
					hasValue:  value.hasValue,
					noValue:   value.noValue,
					showValue: value.showValue,
				})
			);
		});

		// If any of our sections have output, output them.
		if ( $sectionRequired.children().length > 1 ) {
			$controls.append( $sectionRequired );
		}
		if ( $sectionOptional.children().length > 1 ) {
			$controls.append( $sectionOptional );
		}
	}

	/**
	 * Inject our publish button.
	 */
	var injectButton = function() {

		// Remove the default save button and spinner.
		$header.find( 'input#save' ).remove();
		$header.find( '.spinner' ).remove();

		var $button = $( '<div />' );
		var buttonText = ( data.scheduled ) ? 'Schedule' : 'Publish';

		$button.addClass( 'button-primary pf-customizer-publish' )
			.attr( 'disabled', true )
			.text( buttonText );

		$header.append( $button );

		// Set up click action on the publish button.
		$button.on( 'click', function() {

			console.log( 'button clicked' );

			// Trigger a message about required things when a user
			// clicks on the button while it is disabled.
			if ( $( this ).attr( 'disabled' ) ) {
				if ( $controls.hasClass( 'pf-requirements-met' ) ) {
					showDeviceNotification();
				} else {
					showReqNotification();
				}

				return;
			}

			// Everything must be good, so publish the post.
			ajaxPublishPost();
		});
	}

	/**
	 * Show the required field notification.
	 */
	var showReqNotification = function() {
		$( '.pf-notifications' ).addClass( 'visible' );
		$controls.addClass( 'pf-notifications-open' );
	}

	/**
	 * Show the device notification.
	 */
	var showDeviceNotification = function() {
		$( '.pf-device-notifications' ).addClass( 'visible' );
	}

	/**
	 * Initialize our device preview events.
	 */
	var initDevicePreview = function() {
		var $deviceButtons = $footer.find( '.devices button' );

		$deviceButtons.on( 'click', function() {

			$( this ).addClass( 'pf-clicked' );

			// If all buttons have been clicked and other requirements
			// have been met, enable the Publish button.
			if ( $deviceButtons.filter( '.pf-clicked' ).length === $deviceButtons.length && $controls.hasClass( 'pf-requirements-met' ) ) {
				$( '.pf-device-notifications' ).removeClass( 'visible' );
				$header.find( '.pf-customizer-publish' ).removeAttr( 'disabled' );
			}
		});
	}

	/**
	 * Make an Ajax call to publish the previewed post.
	 */
	var ajaxPublishPost = function() {

		console.log( 'attempting to publish a post' );

		var pubData = {
			'action'           : 'pf_publish_post',
			'post_id'          : data.post.ID,
			'pf_publish_nonce' : data.publishNonce,
		};

		var publishPost = $.post( ajaxurl, pubData );

		publishPost.done( function( response ) {
			console.log( 'Ajax request to publish post has completed' );
			console.log( response );
		});

		publishPost.fail( function() {
			console.log( 'Whoops, something went wrong with the Ajax request to publish the post' );
		})
	}

	return {
		init: init,
	};

})( jQuery, _, wp, publishingFlowData );

/**
 * Start the party.
 */
jQuery( document ).ready( function( $ ) {
	PublishingFlowCustomizer.init();
});