/**
 * Publishing Flow Admin JS.
 */

var PublishingFlow = ( function( $ ) {

	'use strict';

	/**
	 * Initalize.
	 */
	var init = function() {

		// Bail if our data isn't there.
		if ( ! publishingFlowData ) {
			return;
		}

		// Hijack the publish and schedule buttons.
		redirectButtons();
	}

	/**
	 * Hijack the publish and schedule buttons.
	 */
	var redirectButtons = function() {

		// Hide actual buttons.
		$( '#publishing-action input[type="submit"]' ).remove();

		var url = publishingFlowData.url

		// Inject our button.
		$( '#publishing-action' ).append(
			$( '<a />' )
				.addClass( 'button button-primary publishing-flow-trigger' )
				.text( 'Preview & Publish' )
				.attr( 'href', url )
		);
	}

	return {
		init: init,
	};

})( jQuery );

/**
 * Start the party.
 */
jQuery( document ).ready( function() {
	PublishingFlow.init();
});
