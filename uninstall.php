<?php
/**
 * Uninstall handler.
 *
 * The change journal table is shared with other CognitoLab plugins, so it is
 * dropped only when no other plugin that bundles the journal is left.
 *
 * @package Sitekeel
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

function skeel_uninstall_site() {
	global $wpdb;

	$consumers = get_option( 'cljournal_consumers', array() );
	$consumers = array_values( array_diff( is_array( $consumers ) ? $consumers : array(), array( 'sitekeel' ) ) );

	if ( $consumers ) {
		update_option( 'cljournal_consumers', $consumers, false );
		return;
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- dropping this plugin's own table on uninstall.
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}cljournal_events" );
	delete_option( 'cljournal_consumers' );
	delete_option( 'cljournal_db_version' );
	wp_clear_scheduled_hook( 'cljournal_prune' );
}

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids' ) ) as $skeel_site_id ) {
		switch_to_blog( $skeel_site_id );
		skeel_uninstall_site();
		restore_current_blog();
	}
} else {
	skeel_uninstall_site();
}
