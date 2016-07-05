<?php
/**
 * Plugin Name: Publishing Flow
 * Version:     1.0.0
 * Description: Adds a Customizer-based publishing flow for ensuring posts are complete before publishing
 * Author:      Braad Martin
 * Author URI:  http://braadmartin.com
 * Plugin URI:  http://braadmartin.com
 * Text Domain: publishing-flow
 * Domain Path: /languages
 */

define( 'PUBLISHING_FLOW_VERSION', '1.0.0' );
define( 'PUBLISHING_FLOW_PATH', plugin_dir_path( __FILE__ ) );
define( 'PUBLISHING_FLOW_URL', plugin_dir_url( __FILE__ ) );

// Include admin functionality.
require_once PUBLISHING_FLOW_PATH . 'inc/class-publishing-flow-admin.php';

/**
 * Start the party.
 */
if ( is_admin() ) {
	$publishing_flow = new Publishing_Flow_Admin();
	$publishing_flow->init();
}