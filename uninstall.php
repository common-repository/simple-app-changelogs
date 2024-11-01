<?php
// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}
global $wpdb;
//proceed uninsall!
$projectTable_name = $wpdb->prefix. "sappchangelog_projects";
$versionTable_name = $wpdb->prefix ."sappchangelog_versions";
$sqlEliminateVersionTable = "DROP TABLE " .$versionTable_name . ";";
$sqlEliminateProjectTable = "DROP TABLE " . $projectTable_name .";";
$wpdb->query( $sqlEliminateVersionTable );
$wpdb->query( $sqlEliminateProjectTable );
?>