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

		// Set up a mutation observer to detect when the publish button changes.
		if ( hijacked ) {
			setupButtonObserver();
		}
	}

	/**
	 * Hijack the publish and schedule buttons.
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

		// Grab our button URL and label from our data object.
		var url   = data.buttonUrl;
		var label = data.buttonLabel;

		// Inject our button.
		$( '#publishing-action' ).append(
			$( '<a />' )
				.addClass( 'button button-primary publishing-flow-trigger' )
				.text( label )
				.attr( 'href', url )
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
			attributes: true,
			childList: true,
			characterData: true
		};

		observer.observe( target, config );
	}

	/**
	 * Update our button text.
	 */
	var updateButtonText = function( text ) {

		// Handle English button text gracefully.
		if ( 'Publish' === text ) {
			text = 'Publish Flow';
		} else if ( 'Schedule' === text ) {
			text = 'Schedule Flow';
		}

		$( '.publishing-flow-trigger' ).text( text );
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
