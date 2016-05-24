<?php
/**
 * Plugin Name: Publishing Flow
 * Version:     0.1-alpha
 * Description: Adds a Customizer-based publishing flow for ensuring posts are complete before publishing
 * Author:      NerdWallet
 * Author URI:  https://www.nerdwallet.com
 * Plugin URI:  https://www.nerdwallet.com
 * Text Domain: publishing-flow
 * Domain Path: /languages
 */

define( 'PUBLISHING_FLOW_VERSION', '1.0.0' );
define( 'PUBLISHING_FLOW_PATH', plugin_dir_path( __FILE__ ) );
define( 'PUBLISHING_FLOW_URL', plugin_dir_url( __FILE__ ) );

// Include admin functionality.
require_once PUBLISHING_FLOW_PATH . 'inc/class-publishing-flow-admin.php';

add_action( 'plugins_loaded', 'publishing_flow_init' );
/**
 * Start the party.
 */
function publishing_flow_init() {

	$publishing_flow = new Publishing_Flow_Admin();
	$publishing_flow->init();
}