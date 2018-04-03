<?php
/*
 * Plugin Name: CCP Mentor Plugin
 * Version: 1.0
 * Plugin URI: http://dev.pscott.com.au/wp-ccp-mentor-plugin
 * Description: Custom plugin being developed for SunshineChamber Alliance Mentoring Program
 * Author: Peter Scott
 * Author URI: https://www.pscott.com.au/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: ccp-mentor-plugin
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Peter Scott
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-ccp-mentor-plugin.php' );
require_once( 'includes/class-ccp-mentor-plugin-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-ccp-mentor-plugin-admin-api.php' );
require_once( 'includes/lib/class-ccp-mentor-plugin-post-type.php' );
require_once( 'includes/lib/class-ccp-mentor-plugin-taxonomy.php' );

require_once( 'includes/lib/class-ccp-mentor-plugin-Customers_List.php' );




/**
 * Returns the main instance of CCP_Mentor_Plugin to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object CCP_Mentor_Plugin
 */
function CCP_Mentor_Plugin () {
	$instance = CCP_Mentor_Plugin::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = CCP_Mentor_Plugin_Settings::instance( $instance );
		
	}

	return $instance;
}

CCP_Mentor_Plugin();

