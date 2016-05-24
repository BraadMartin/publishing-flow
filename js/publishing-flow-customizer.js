/**
 * Publishing Flow Customizer JS.
 */

var PublishingFlowCustomizer = ( function( $, wp, data ) {

	var $controls;

	/**
	 * Initialize.
	 */
	var init = function() {
		$controls = $( '#customize-theme-controls' );

		// Inject our custom controls.
		injectControls();
	};

	/**
	 * Inject our custom controls.
	 */
	var injectControls = function() {
		$controls.append( $( '<div />' ).text( 'working' ) );
	}

	return {
		init: init,
	};

})( jQuery, wp, publishingFlowData );

/**
 * Start the party.
 */
jQuery( document ).ready( function( $ ) {
	PublishingFlowCustomizer.init();
});