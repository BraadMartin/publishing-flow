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

		// Grab our button label from our data object.
		var label = data.buttonLabel;

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

	/**
	 * Click handler for the Publish Flow button.
	 */
	var setupButtonClick = function() {

		// When the button is clicked, inject an extra hidden <input>
		// that will allow us to do our redirect.
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
