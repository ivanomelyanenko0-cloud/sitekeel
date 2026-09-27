<?php
/**
 * Which changes are important: the ones that can take a promotion, a
 * product or the whole shop offline, or that an AI agent made.
 *
 * @package Sitekeel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<string, string> 'source/event' => label.
 */
function skeel_important_events() {
	$events = array(
		'wordpress/plugin_deactivated'     => __( 'A plugin was deactivated', 'sitekeel' ),
		'wordpress/theme_switched'         => __( 'The theme was switched', 'sitekeel' ),
		'wordpress/core_updated'           => __( 'WordPress was updated', 'sitekeel' ),
		'wordpress/page_unpublished'       => __( 'A page was taken offline', 'sitekeel' ),
		'woocommerce/product_unpublished'  => __( 'A product was taken offline', 'sitekeel' ),
		'woocommerce/stock_status_changed' => __( 'A product\'s stock status changed', 'sitekeel' ),
		'woocommerce/price_changed'        => __( 'A price changed', 'sitekeel' ),
		'heralda/bar_unpublished'          => __( 'A Heralda bar was turned off', 'sitekeel' ),
		'proofblocks/block_removed'        => __( 'A ProofBlocks block was removed from a live page', 'sitekeel' ),
		'tillkeeper/agent_write'           => __( 'An AI agent changed something', 'sitekeel' ),
	);

	/**
	 * Filters which changes count as important.
	 *
	 * @param array $events 'source/event' => label.
	 */
	return (array) apply_filters( 'skeel_important_events', $events );
}

/**
 * @param array $row Journal row.
 * @return bool
 */
function skeel_is_important( array $row ) {
	return isset( skeel_important_events()[ $row['source'] . '/' . $row['event'] ] );
}
