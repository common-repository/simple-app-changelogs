<?php
/**
 * Plugin Name: Simple apps changelogs
 * Description: Simple Plugin to display a Changelog on an App, Plugin or whatever else page.
 * Version: 1.0
 * Author: peti446
 * Text Domain: simple-appchangelog-plugin
 * Domain Path: /assets/langs
 * License: GPLv2
 */
if ( ! defined( 'SIMPLE_APPCHANGELOG_PLUGIN_FILE' ) ) define('SIMPLE_APPCHANGELOG_PLUGIN_FILE', plugin_basename( __FILE__ ));
if ( ! defined( 'SIMPLE_APPCHANGELOG_PLUGIN_DIR' ) ) 	define( 'SIMPLE_APPCHANGELOG_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) );
if ( ! defined( 'SIMPLE_APPCHANGELOG_PLUGIN_URL' ) )  define( 'SIMPLE_APPCHANGELOG_PLUGIN_URL', WP_PLUGIN_URL . '/'.plugin_basename( dirname( __FILE__ ) ));
//Start calling the php funcitons
if ( is_admin() ){
	require_once SIMPLE_APPCHANGELOG_PLUGIN_DIR.'/admin/admin.php';
}
require_once SIMPLE_APPCHANGELOG_PLUGIN_DIR.'/classes/class.ProjectsManager.php';
require_once SIMPLE_APPCHANGELOG_PLUGIN_DIR.'/classes/class.VersionsManager.php';
require_once SIMPLE_APPCHANGELOG_PLUGIN_DIR.'/main.php';
require_once SIMPLE_APPCHANGELOG_PLUGIN_DIR.'/init.php';
//Load the textdomain
add_action('plugins_loaded', 'simple_appchangelog_textdomain');
function simple_appchangelog_textdomain() {
	load_plugin_textdomain( 'simple-appchangelog-plugin', false, plugin_basename(dirname(__FILE__) ) . '/assets/langs' );
}

?>