<?php
/**
 * Status of the WordPress AI Client (core, 7.0+), which CognitoLab plugins
 * use so that one AI connection serves the whole site.
 *
 * @package Sitekeel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return string 'unavailable' (WordPress too old or AI switched off),
 *                'no_connector' or 'ready'.
 */
function skeel_ai_status() {
	if ( ! function_exists( 'wp_ai_client_prompt' ) || ! function_exists( 'wp_supports_ai' ) || ! call_user_func( 'wp_supports_ai' ) ) {
		return 'unavailable';
	}

	// Building a prompt makes no request; it only resolves the configured connectors.
	$builder = call_user_func( 'wp_ai_client_prompt', 'ping' );
	if ( ! is_object( $builder ) || ! is_callable( array( $builder, 'is_supported_for_text_generation' ) ) ) {
		return 'unavailable';
	}
	return true === $builder->is_supported_for_text_generation() ? 'ready' : 'no_connector';
}

/**
 * Renders the status block on the Portfolio tab.
 */
function skeel_render_ai_status() {
	$status = skeel_ai_status();
	$texts  = array(
		'ready'        => __( 'Ready: an AI connector is set up under Settings > Connectors. Fettle, IntelliDesc and Watermark Guru Pro can use it without their own API keys.', 'sitekeel' ),
		'no_connector' => __( 'Available, but no AI connector is set up yet. Add one under Settings > Connectors to let CognitoLab plugins share it.', 'sitekeel' ),
		'unavailable'  => __( 'Not available on this site (WordPress 7.0 or later with AI enabled is needed). Plugins that use AI fall back to their own API key settings.', 'sitekeel' ),
	);
	?>
	<h2><?php esc_html_e( 'WordPress AI', 'sitekeel' ); ?></h2>
	<p class="skeel-ai skeel-ai-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $texts[ $status ] ); ?></p>
	<?php
}
