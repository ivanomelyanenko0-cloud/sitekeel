<?php
/**
 * Portfolio: which CognitoLab plugins are on this site and how the hub
 * sees them. Detection uses only each plugin's own version constant and
 * licensing function, so nothing here makes a plugin depend on the hub.
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
			'pro_fs'     => 'hrld_fs',
			'visibility' => 'observed',
			'summary'    => __( 'Bars turned on/off, schedule, pages and button changes.', 'sitekeel' ),
		),
		'proofblocks'                  => array(
			'name'       => 'ProofBlocks',
			'file'       => 'proofblocks/proofblocks.php',
			'constant'   => 'PRFBL_VERSION',
			'pro_file'   => 'proofblocks-pro/proofblocks-pro.php',
			'pro_fs'     => 'prfbl_fs',
			'visibility' => 'observed',
			'summary'    => __( 'Banners, counters and pricing tables added to or removed from live pages.', 'sitekeel' ),
		),
		'watermark-guru'               => array(
			'name'       => 'Watermark Guru',
			'file'       => 'watermark-guru/watermark-guru.php',
			'constant'   => 'WMGURU_VERSION',
			'pro_file'   => 'watermark-guru-pro/watermark-guru-pro.php',
			'pro_fs'     => 'wmguru_fs',
			'visibility' => 'observed',
			'summary'    => __( 'Watermark settings changes.', 'sitekeel' ),
		),
		'tillkeeper'                  => array(
			'name'       => 'Tillkeeper',
			'file'       => 'tillkeeper/tillkeeper.php',
			'constant'   => 'TLKP_VERSION',
			'pro_file'   => 'tillkeeper-pro/tillkeeper-pro.php',
			'pro_fs'     => 'tlkp_fs',
			'visibility' => 'observed',
			'summary'    => __( 'Changes made by AI agents through store abilities.', 'sitekeel' ),
		),
		'fettle'                       => array(
			'name'       => 'Fettle',
			'file'       => 'fettle/fettle.php',
			'constant'   => 'FETTLE_VERSION',
			'pro_file'   => 'fettle-pro/fettle-pro.php',
			'pro_fs'     => 'fettle_pro_fs',
			'visibility' => 'needs_events',
			'summary'    => __( 'Accessibility scans and regressions, once Fettle reports them.', 'sitekeel' ),
		),
		'intellidesc-for-woocommerce' => array(
			'name'       => 'IntelliDesc',
			'file'       => 'intellidesc-for-woocommerce/intellidesc-for-woocommerce.php',
			'constant'   => '',
			'pro_file'   => 'intellidesc-for-woocommerce-pro/intellidesc-for-woocommerce-pro.php',
			'pro_fs'     => 'ildesc_fs',
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
 * @return array[] Registry entries enriched with install/activation/license state.
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
		$item['license']        = skeel_portfolio_license( $item );
		$out[ $slug ]           = $item;
	}
	return $out;
}

/**
 * @param array $item Registry entry with pro_active set.
 * @return string 'none' | 'active' | 'inactive' | 'unknown'.
 */
function skeel_portfolio_license( array $item ) {
	if ( empty( $item['pro_active'] ) ) {
		return 'none';
	}
	if ( '' === $item['pro_fs'] || ! function_exists( $item['pro_fs'] ) ) {
		return 'unknown';
	}

	$fs = call_user_func( $item['pro_fs'] );
	if ( ! is_object( $fs ) || ! method_exists( $fs, 'can_use_premium_code' ) ) {
		return 'unknown';
	}
	return $fs->can_use_premium_code() ? 'active' : 'inactive';
}
