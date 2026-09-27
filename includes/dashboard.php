<?php
/**
 * Dashboard widget: the latest changes, with important ones marked.
 *
 * @package Sitekeel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the widget for users who can see the timeline.
 */
function skeel_register_dashboard_widget() {
	if ( current_user_can( 'manage_options' ) ) {
		wp_add_dashboard_widget( 'skeel_recent_changes', __( 'Recent site changes', 'sitekeel' ), 'skeel_render_dashboard_widget' );
	}
}
add_action( 'wp_dashboard_setup', 'skeel_register_dashboard_widget' );

/**
 * Renders the widget.
 */
function skeel_render_dashboard_widget() {
	$rows = function_exists( 'cljournal_query' ) ? cljournal_query( array( 'limit' => 8 ) ) : array();

	if ( empty( $rows ) ) {
		echo '<p>' . esc_html__( 'No changes recorded yet.', 'sitekeel' ) . '</p>';
		return;
	}

	echo '<ul class="skeel-widget">';
	foreach ( $rows as $row ) {
		printf(
			'<li class="%1$s"><span class="skeel-widget-when">%2$s</span> %3$s%4$s</li>',
			esc_attr( skeel_is_important( $row ) ? 'skeel-important' : '' ),
			esc_html( human_time_diff( strtotime( $row['occurred_at'] . ' UTC' ) ) ),
			skeel_is_important( $row ) ? '<strong>' . esc_html__( 'Important:', 'sitekeel' ) . '</strong> ' : '',
			esc_html( skeel_describe_event( $row ) )
		);
	}
	echo '</ul>';
	printf( '<p><a href="%1$s">%2$s</a></p>', esc_url( admin_url( 'admin.php?page=sitekeel' ) ), esc_html__( 'Open the full timeline', 'sitekeel' ) );
}

/**
 * @param string $hook Admin page hook.
 */
function skeel_dashboard_assets( $hook ) {
	if ( 'index.php' === $hook ) {
		wp_enqueue_style( 'skeel-admin', SKEEL_PLUGIN_URL . 'assets/css/admin.css', array(), SKEEL_VERSION );
	}
}
add_action( 'admin_enqueue_scripts', 'skeel_dashboard_assets' );
