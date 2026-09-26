<?php
/**
 * Human-readable text for journal events.
 *
 * Lives in the plugin rather than in the shared journal library because
 * translations need this plugin's own text domain.
 *
 * @package Sitekeel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array $row Journal row from cljournal_query().
 * @return string Plain text, not escaped.
 */
function skeel_describe_event( array $row ) {
	$data  = isset( $row['data'] ) && is_array( $row['data'] ) ? $row['data'] : array();
	$get   = static function ( $key ) use ( $data ) {
		return isset( $data[ $key ] ) && is_scalar( $data[ $key ] ) ? (string) $data[ $key ] : '';
	};
	$title = $get( 'title' );
	if ( '' === $title && ! empty( $row['object_id'] ) ) {
		$title = get_the_title( (int) $row['object_id'] );
	}
	if ( '' === $title && ! empty( $row['object_id'] ) ) {
		$title = '#' . (int) $row['object_id'];
	}

	switch ( $row['source'] . '/' . $row['event'] ) {
		case 'wordpress/plugin_activated':
			/* translators: %s: plugin file. */
			return sprintf( __( 'Plugin activated: %s', 'sitekeel' ), $get( 'plugin' ) );
		case 'wordpress/plugin_deactivated':
			/* translators: %s: plugin file. */
			return sprintf( __( 'Plugin deactivated: %s', 'sitekeel' ), $get( 'plugin' ) );
		case 'wordpress/plugin_updated':
			/* translators: %s: plugin file. */
			return sprintf( __( 'Plugin updated: %s', 'sitekeel' ), $get( 'item' ) );
		case 'wordpress/theme_updated':
			/* translators: %s: theme slug. */
			return sprintf( __( 'Theme updated: %s', 'sitekeel' ), $get( 'item' ) );
		case 'wordpress/theme_switched':
			/* translators: 1: old theme, 2: new theme. */
			return sprintf( __( 'Theme switched from "%1$s" to "%2$s"', 'sitekeel' ), $get( 'before' ), $get( 'after' ) );
		case 'wordpress/core_updated':
			/* translators: %s: WordPress version. */
			return sprintf( __( 'WordPress updated to %s', 'sitekeel' ), $get( 'after' ) );
		case 'wordpress/page_published':
			/* translators: %s: page title. */
			return sprintf( __( 'Page published: "%s"', 'sitekeel' ), $title );
		case 'wordpress/page_unpublished':
			/* translators: %s: page title. */
			return sprintf( __( 'Page taken offline: "%s"', 'sitekeel' ), $title );

		case 'woocommerce/product_published':
			/* translators: %s: product name. */
			return sprintf( __( 'Product published: "%s"', 'sitekeel' ), $title );
		case 'woocommerce/product_unpublished':
			/* translators: %s: product name. */
			return sprintf( __( 'Product taken offline: "%s"', 'sitekeel' ), $title );
		case 'woocommerce/price_changed':
			/* translators: 1: product name, 2: old price, 3: new price. */
			return sprintf( __( 'Price of "%1$s" changed: %2$s → %3$s', 'sitekeel' ), $title, skeel_or_dash( $get( 'before' ) ), skeel_or_dash( $get( 'after' ) ) );
		case 'woocommerce/sale_price_changed':
			/* translators: 1: product name, 2: old sale price, 3: new sale price. */
			return sprintf( __( 'Sale price of "%1$s" changed: %2$s → %3$s', 'sitekeel' ), $title, skeel_or_dash( $get( 'before' ) ), skeel_or_dash( $get( 'after' ) ) );
		case 'woocommerce/stock_status_changed':
			/* translators: 1: product name, 2: old status, 3: new status. */
			return sprintf( __( 'Stock status of "%1$s": %2$s → %3$s', 'sitekeel' ), $title, skeel_or_dash( $get( 'before' ) ), skeel_or_dash( $get( 'after' ) ) );

		case 'heralda/bar_published':
			/* translators: %s: bar title. */
			return sprintf( __( 'Heralda bar turned on: "%s"', 'sitekeel' ), $title );
		case 'heralda/bar_unpublished':
			/* translators: %s: bar title. */
			return sprintf( __( 'Heralda bar turned off: "%s"', 'sitekeel' ), $title );
		case 'heralda/bar_schedule_changed':
			/* translators: %s: bar title. */
			return sprintf( __( 'Heralda bar schedule changed: "%s"', 'sitekeel' ), $title );
		case 'heralda/bar_targeting_changed':
			/* translators: %s: bar title. */
			return sprintf( __( 'Heralda bar pages changed: "%s"', 'sitekeel' ), $title );
		case 'heralda/bar_cta_changed':
			/* translators: %s: bar title. */
			return sprintf( __( 'Heralda bar button changed: "%s"', 'sitekeel' ), $title );

		case 'proofblocks/block_added':
			/* translators: 1: block name, 2: page title. */
			return sprintf( __( 'ProofBlocks %1$s added on "%2$s"', 'sitekeel' ), skeel_block_label( $get( 'block' ) ), $title );
		case 'proofblocks/block_removed':
			/* translators: 1: block name, 2: page title. */
			return sprintf( __( 'ProofBlocks %1$s removed from "%2$s"', 'sitekeel' ), skeel_block_label( $get( 'block' ) ), $title );

		case 'watermark-guru/settings_changed':
			$fields = isset( $data['fields'] ) && is_array( $data['fields'] ) ? implode( ', ', array_map( 'strval', $data['fields'] ) ) : '';
			/* translators: %s: list of setting names. */
			return sprintf( __( 'Watermark settings changed (%s)', 'sitekeel' ), $fields );

		case 'agentwarden/agent_write':
			/* translators: 1: ability name, 2: object. */
			return sprintf( __( 'AI agent change via %1$s on %2$s', 'sitekeel' ), $get( 'ability' ), $title );
	}

	/* translators: 1: source, 2: event name. */
	return sprintf( __( '%1$s: %2$s', 'sitekeel' ), $row['source'], str_replace( '_', ' ', $row['event'] ) );
}

/**
 * @param string $value Value.
 * @return string Value or an em dash for empty.
 */
function skeel_or_dash( $value ) {
	return '' === $value ? '—' : $value;
}

/**
 * @param string $block Block name like 'proofblocks/banner'.
 * @return string Short label.
 */
function skeel_block_label( $block ) {
	$labels = array(
		'proofblocks/banner'        => __( 'Banner', 'sitekeel' ),
		'proofblocks/counter'       => __( 'Counter', 'sitekeel' ),
		'proofblocks/pricing-table' => __( 'Pricing Table', 'sitekeel' ),
	);
	return isset( $labels[ $block ] ) ? $labels[ $block ] : $block;
}
