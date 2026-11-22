<?php
/**
 * Settings tab: how long the timeline keeps changes.
 *
 * @package Sitekeel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SKEEL_SETTINGS_OPTION', 'skeel_settings' );

/**
 * @return int[] Retention choices in days; 0 means keep everything.
 */
function skeel_retention_choices() {
	return array( 30, 90, 180, 365, 730, 0 );
}

/**
 * @return int Chosen retention in days; 0 means keep everything.
 */
function skeel_retention_setting() {
	$stored = get_option( SKEEL_SETTINGS_OPTION, array() );
	$days   = is_array( $stored ) && isset( $stored['retention_days'] ) ? (int) $stored['retention_days'] : 90;
	return in_array( $days, skeel_retention_choices(), true ) ? $days : 90;
}

/**
 * Feeds the chosen retention to the shared journal's daily cleanup.
 * "Keep everything" is a span the cleanup never reaches.
 *
 * @return int Days.
 */
function skeel_filter_retention_days() {
	$days = skeel_retention_setting();
	return 0 === $days ? 36500 : $days;
}
add_filter( 'cljournal_retention_days', 'skeel_filter_retention_days' );

/**
 * Saves the settings form.
 */
function skeel_handle_settings_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'sitekeel' ) );
	}
	check_admin_referer( 'skeel_save_settings' );

	$days = isset( $_POST['retention_days'] ) ? absint( $_POST['retention_days'] ) : 90;
	update_option(
		SKEEL_SETTINGS_OPTION,
		array( 'retention_days' => in_array( $days, skeel_retention_choices(), true ) ? $days : 90 ),
		false
	);

	wp_safe_redirect( add_query_arg( array( 'page' => 'sitekeel', 'tab' => 'settings', 'skeel-saved' => '1' ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_skeel_save_settings', 'skeel_handle_settings_save' );

/**
 * Renders the Settings tab.
 */
function skeel_render_settings_tab() {
	$current = skeel_retention_setting();
	$labels  = array(
		30  => __( '30 days', 'sitekeel' ),
		90  => __( '90 days', 'sitekeel' ),
		180 => __( '6 months', 'sitekeel' ),
		365 => __( '1 year', 'sitekeel' ),
		730 => __( '2 years', 'sitekeel' ),
		0   => __( 'Keep everything', 'sitekeel' ),
	);

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only notice after a nonce-checked redirect.
	if ( ! empty( $_GET['skeel-saved'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'sitekeel' ) . '</p></div>';
	}
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="skeel_save_settings" />
		<?php wp_nonce_field( 'skeel_save_settings' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="skeel-retention"><?php esc_html_e( 'Keep changes for', 'sitekeel' ); ?></label></th>
				<td>
					<select id="skeel-retention" name="retention_days">
						<?php foreach ( skeel_retention_choices() as $days ) : ?>
							<option value="<?php echo esc_attr( (string) $days ); ?>" <?php selected( $current, $days ); ?>><?php echo esc_html( $labels[ $days ] ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Changes older than this are removed once a day. Each change is a small database row, so even "Keep everything" stays light on most sites.', 'sitekeel' ); ?></p>
				</td>
			</tr>
		</table>
		<?php submit_button( __( 'Save', 'sitekeel' ) ); ?>
	</form>
	<?php
}
