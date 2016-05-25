/**
 * Publishing Flow Admin JS.
 */

var PublishingFlow = ( function( $, data ) {

	'use strict';

	/**
	 * Initalize.
	 */
	var init = function() {

		// Bail if our data isn't there.
		if ( ! data ) {
			return;
		}

		// Hijack the publish and schedule buttons.
		redirectButtons();
	}

	/**
	 * Hijack the publish and schedule buttons.
	 */
	var redirectButtons = function() {

		var $publish = $( '#publishing-action #publish' );

		// Do nothing if there isn't a publish button.
		if ( ! $publish.length ) {
			return;
		}

		// Hide actual publish button.
		$publish.remove();

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
