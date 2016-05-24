/**
 * Publishing Flow Customizer JS.
 */

var PublishingFlowCustomizer = ( function( $, wp ) {

	var init = function() {
		console.log( 'firing' );
	};

	return {
		init: init,
	};

})( jQuery, wp );

/**
 * Start the party.
 */
jQuery( document ).ready( function( $ ) {
	PublishingFlowCustomizer.init();
});