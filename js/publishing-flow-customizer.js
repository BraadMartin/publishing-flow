/**
 * Publishing Flow Customizer JS.
 */

var PublishingFlowCustomizer = ( function( $, _, wp, data ) {

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

		// Add a class to the controls wrapper to indicate Publishing Flow is active.
		$controls.addClass( 'pf-customizer' );

		// Add a class to the controls wrapper to indicate all required fields have a value.
		if ( data.requirementsMet ) {
			$controls.addClass( 'pf-requirements-met' );
		}

		// Set mobile as initial previewed device.
		wp.customize.previewedDevice.set( 'mobile' );

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
	 * Inject our info section.
	 */
	var injectInfo = function() {

		$info.empty();

		$ourInfo = $( '<div />' )
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

		$reqNotifications = $( '<div />' )
			.addClass( 'pf-notifications' )
			.append(
				$( '<p />' )
					.text( "Woah there, looks like this post is still missing a required field!" )
					.append(
						$( '<span />' ).addClass( 'dashicons dashicons-warning' )
					),
				$( '<a />' )
					.attr( 'href', data.editLink )
					.text( 'Visit the edit screen to fix this.' )
			);

		$controls.prepend( $reqNotifications );

		$deviceNotifications = $( '<div />' )
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

		var optPrimary = wp.template( 'pf-optional-primary' );

		_.each( data.optionalPrimary, function( value, key, list ) {
			$sectionOptional.append(
				optPrimary({
					key:       key,
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

		$button.addClass( 'button-primary pf-customizer-publish' )
			.attr( 'disabled', true )
			.text( 'Publish' );

		$header.append( $button );

		$button.on( 'click', function() {

			// Trigger a message about missing things if the user
			// clicks on the button while it is disabled.
			if ( $( this ).attr( 'disabled' ) ) {
				if ( $controls.hasClass( 'pf-requirements-met' ) ) {
					showDeviceNotification();
				} else {
					showReqNotification();
				}
			}

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
		$deviceButtons = $footer.find( '.devices button' );

		$deviceButtons.on( 'click', function() {

			$( this ).addClass( 'pf-clicked' );

			// If all buttons have been clicked and other requirements
			// have been met, enable the Publish button.
			if ( $deviceButtons.filter( '.pf-clicked' ).length === $deviceButtons.length && $controls.hasClass( 'pf-requirements-met' ) ) {
				$header.find( '.pf-customizer-publish' ).removeAttr( 'disabled' );
			}
		});
	}

	/**
	 * Make an Ajax call to publish the previewed post.
	 */
	var ajaxPublishPost = function() {
		console.log( 'publishing the post' );
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