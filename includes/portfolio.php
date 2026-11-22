<?php
/**
 * Portfolio: which CognitoLab plugins are on this site and how the hub
 * sees them. Detection uses only the installed plugin list and each plugin's
 * own version constant, so nothing here makes a plugin depend on the hub.
 *
 * @package Sitekeel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array[] Keyed by slug.
 */
function skeel_portfolio_registry() {
	$registry = array(
		'heralda'                      => array(
			'name'       => 'Heralda',
			'file'       => 'heralda/heralda.php',
			'constant'   => 'HRLD_VERSION',
			'pro_file'   => 'heralda-pro/heralda-pro.php',
			'visibility' => 'observed',
			'summary'    => __( 'Bars turned on/off, schedule, pages and button changes.', 'sitekeel' ),
		),
		'proofblocks'                  => array(
			'name'       => 'ProofBlocks',
			'file'       => 'proofblocks/proofblocks.php',
			'constant'   => 'PRFBL_VERSION',
			'pro_file'   => 'proofblocks-pro/proofblocks-pro.php',
			'visibility' => 'observed',
			'summary'    => __( 'Banners, counters and pricing tables added to or removed from live pages.', 'sitekeel' ),
		),
		'filigrane'                    => array(
			'name'       => 'Filigrane',
			'file'       => 'filigrane/filigrane.php',
			'constant'   => 'WMGURU_VERSION',
			'pro_file'   => 'filigrane-pro/filigrane-pro.php',
			'visibility' => 'observed',
			'summary'    => __( 'Watermark settings changes.', 'sitekeel' ),
		),
		'tillkeeper'                  => array(
			'name'       => 'Tillkeeper',
			'file'       => 'tillkeeper/tillkeeper.php',
			'constant'   => 'TLKP_VERSION',
			'pro_file'   => 'tillkeeper-pro/tillkeeper-pro.php',
			'visibility' => 'observed',
			'summary'    => __( 'Changes made by AI agents through store abilities.', 'sitekeel' ),
		),
		'storewhy'                     => array(
			'name'       => 'StoreWhy',
			'file'       => 'storewhy/storewhy.php',
			'constant'   => 'STWY_VERSION',
			'pro_file'   => 'storewhy-pro/storewhy-pro.php',
			'visibility' => 'observed',
			'summary'    => __( 'Shares the same change timeline; its sales dashboard shows these changes next to the numbers.', 'sitekeel' ),
		),
		'fettle'                       => array(
			'name'       => 'Fettle',
			'file'       => 'fettle/fettle.php',
			'constant'   => 'FETTLE_VERSION',
			'pro_file'   => 'fettle-pro/fettle-pro.php',
			'visibility' => 'needs_events',
			'summary'    => __( 'Accessibility scans and regressions, once Fettle reports them.', 'sitekeel' ),
		),
		'intellidesc-for-woocommerce' => array(
			'name'       => 'IntelliDesc',
			'file'       => 'intellidesc-for-woocommerce/intellidesc-for-woocommerce.php',
			'constant'   => '',
			'pro_file'   => 'intellidesc-for-woocommerce-pro/intellidesc-for-woocommerce-pro.php',
			'visibility' => 'needs_events',
			'summary'    => __( 'AI-generated product descriptions, once IntelliDesc reports them.', 'sitekeel' ),
		),
	);

	/**
	 * Filters the portfolio registry.
	 *
	 * @param array[] $registry Keyed by slug.
	 */
	return (array) apply_filters( 'skeel_portfolio_registry', $registry );
}

/**
 * @return array[] Registry entries enriched with install, activation and edition state.
 */
function skeel_portfolio_status() {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$installed = get_plugins();
	$out       = array();

	foreach ( skeel_portfolio_registry() as $slug => $item ) {
		$free_installed = isset( $installed[ $item['file'] ] );
		$pro_installed  = '' !== $item['pro_file'] && isset( $installed[ $item['pro_file'] ] );

		$version = '';
		if ( '' !== $item['constant'] && defined( $item['constant'] ) ) {
			$version = (string) constant( $item['constant'] );
		} elseif ( $free_installed ) {
			$version = (string) $installed[ $item['file'] ]['Version'];
		} elseif ( $pro_installed ) {
			// IntelliDesc Pro is a standalone plugin, not an add-on to the free one.
			$version = (string) $installed[ $item['pro_file'] ]['Version'];
		}

		$item['free_installed'] = $free_installed;
		$item['free_active']    = is_plugin_active( $item['file'] );
		$item['pro_installed']  = $pro_installed;
		$item['pro_active']     = $pro_installed && is_plugin_active( $item['pro_file'] );
		$item['installed']      = $free_installed || $pro_installed;
		$item['active']         = $item['free_active'] || $item['pro_active'];
		$item['version']        = $version;
		$item['edition']        = $item['pro_active'] ? ( $item['free_active'] ? 'free_pro' : 'pro' ) : 'free';
		$out[ $slug ]           = $item;
	}
	return $out;
}
