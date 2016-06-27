/**
 * Publishing Flow Admin JS.
 */

var PublishingFlow = ( function( $, data ) {

	'use strict';

	/**
	 * DOM references.
	 */
	var $publish;

	/**
	 * Initalize.
	 */
	var init = function() {

		// Bail if our data isn't there.
		if ( ! data ) {
			return;
		}

		// Set up key DOM references.
		$publish = $( '#publishing-action #publish' );

		// Hijack the publish and schedule buttons.
		var hijacked = redirectButtons();

		if ( hijacked ) {

			// Set up a mutation observer to detect when the publish button changes.
			setupButtonObserver();

			// Click handler for the button.
			setupButtonClick();
		}

		// Inject requirements section.
		injectRequirementsSection();

		// Setup expand/contract on requirements section.
		setupRequirementsSection();
	}

	/**
	 * Hijack the publish and schedule buttons.
	 *
	 * @todo  The check on "Update" being the button text here will fail on
	 *        non-english sites.
	 */
	var redirectButtons = function() {

		// Do nothing if there isn't a publish button.
		if ( ! $publish.length ) {
			return false;
		}

		// Do nothing if the publish button says "Update".
		if ( 'Update' === $publish.val() ) {
			return false;
		}

		// Hide actual publish button.
		$publish.addClass( 'pf-hidden' );

		// Grab our button label from our data object.
		if ( data.publishAction === 'schedule' ) {
			var label = data.scheduleLabel;
		} else {
			var label = data.publishLabel;
		}

		// Inject our button.
		$( '#publishing-action' ).append(
			$( '<input />' )
				.addClass( 'button button-primary publishing-flow-trigger' )
				.attr( 'value', label )
				.attr( 'type', 'submit' )
				.attr( 'name', 'save' )
		);

		return true;
	}

	/**
	 * Setup a mutation observer to detect when the publish button changes.
	 */
	var setupButtonObserver = function() {

		var target = document.querySelector( '#publish' );

		var observer = new MutationObserver( function( mutations ) {
			mutations.forEach( function( mutation ) {
				if ( 'attributes' === mutation.type && 'value' === mutation.attributeName ) {
					updateButtonText( mutation.target.value );
				}
			});
		});

		var config = {
			attributes: true
		};

		observer.observe( target, config );
	}

	/**
	 * Update our button text.
	 *
	 * @todo  Figure out how to support non-english sites.
	 */
	var updateButtonText = function( text ) {

		// Handle English button text gracefully.
		if ( 'Publish' === text ) {
			var newText = data.publishLabel;
		} else if ( 'Schedule' === text ) {
			var newText = data.scheduleLabel;
		}

		$( '.publishing-flow-trigger' ).val( newText );
	}

	/**
	 * Click handler for the Publish Flow button.
	 */
	var setupButtonClick = function() {

		// When the button is clicked, inject an extra hidden <input>
		// that will allow us to do our redirect, then submit the form.
		$( '.publishing-flow-trigger' ).on( 'click', function( e ) {
			e.preventDefault();

			// Disable browser notices about unsaved form content.
			$( window ).off( 'beforeunload.edit-post' );

			$( '#publishing-action' ).append(
				$( '<input />' )
					.attr( 'type', 'hidden' )
					.attr( 'name', 'pf-action' )
					.attr( 'value', 'enter-publishing-flow' )
			);

			$( 'form#post' ).submit();
		});
	}

	/**
	 * Inject the requirements section.
	 */
	var injectRequirementsSection = function() {

		var $sectionWrap = $( '.publishing-flow-requirements-wrap' );

		// Define our sections.
		var $sectionRequired = $( '<div />' )
			.addClass( 'pf-section pf-required-section' );
		var $sectionOptional = $( '<div />' )
			.addClass( 'pf-section pf-optional-section' );

		// Define section labels.
		var $sectionRequiredLabel = $( '<h2 />' )
			.addClass( 'pf-section-label' )
			.text( data.requiredLabel );
		var $sectionOptionalLabel = $( '<h2 />' )
			.addClass( 'pf-section-label' )
			.text( data.optionalLabel );

		// Inject labels.
		$sectionRequired.append( $sectionRequiredLabel );
		$sectionOptional.append( $sectionOptionalLabel );

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

		var reqTax = wp.template( 'pf-required-tax' );

		_.each( data.requiredTax, function( value, key, list ) {
			$sectionRequired.append(
				reqTax({
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

		var optTax = wp.template( 'pf-optional-tax' );

		_.each( data.optionalTax, function( value, key, list ) {
			$sectionOptional.append(
				optTax({
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
			$sectionWrap.append( $sectionRequired );
		}
		if ( $sectionOptional.children().length > 1 ) {
			$sectionWrap.append( $sectionOptional );
		}
	}

	/**
	 * Setup expand/contract on the requirements section.
	 */
	var setupRequirementsSection = function() {

		$( '.publishing-flow-requirements-status' ).on( 'click', function() {
			$( '.publishing-flow-requirements-wrap' ).toggleClass( 'active' );
		});
	}

	return {
		init: init,
	};

})( jQuery, publishingFlowData );

/**
 * Start the party.
 */
jQuery( document ).ready( function() {
	PublishingFlow.init();
});
