<?php
/**
 * Admin screen: Timeline (site change journal) and Portfolio tabs.
 *
 * @package Sitekeel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the top-level menu.
 */
function skeel_register_admin_page() {
	add_menu_page(
		__( 'Sitekeel', 'sitekeel' ),
		__( 'Sitekeel', 'sitekeel' ),
		'manage_options',
		'sitekeel',
		'skeel_render_admin_page',
		'dashicons-networking',
		58
	);
}
add_action( 'admin_menu', 'skeel_register_admin_page' );

/**
 * @param string $hook Current admin page hook.
 */
function skeel_admin_assets( $hook ) {
	if ( 'toplevel_page_sitekeel' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'skeel-admin', SKEEL_PLUGIN_URL . 'assets/css/admin.css', array(), SKEEL_VERSION );
}
add_action( 'admin_enqueue_scripts', 'skeel_admin_assets' );

/**
 * Renders the page.
 */
function skeel_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab switch.
	$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'timeline';
	/**
	 * Filters the tabs on the Sitekeel page. A tab added here is rendered by
	 * the 'skeel_render_tab_{key}' action.
	 *
	 * @param array $tabs key => label.
	 */
	$tabs = (array) apply_filters(
		'skeel_admin_tabs',
		array(
			'timeline'  => __( 'Timeline', 'sitekeel' ),
			'portfolio' => __( 'Portfolio', 'sitekeel' ),
		)
	);
	if ( ! isset( $tabs[ $tab ] ) ) {
		$tab = 'timeline';
	}
	?>
	<div class="wrap skeel-wrap">
		<h1><?php esc_html_e( 'Sitekeel', 'sitekeel' ); ?></h1>
		<nav class="nav-tab-wrapper">
			<?php foreach ( $tabs as $key => $label ) : ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'sitekeel', 'tab' => $key ), admin_url( 'admin.php' ) ) ); ?>" class="nav-tab <?php echo $key === $tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>
		<?php
		if ( 'portfolio' === $tab ) {
			skeel_render_portfolio_tab();
		} elseif ( 'timeline' === $tab ) {
			skeel_render_timeline_tab();
		} else {
			do_action( 'skeel_render_tab_' . $tab );
		}
		?>
	</div>
	<?php
}

/**
 * Timeline tab.
 */
function skeel_render_timeline_tab() {
	if ( ! function_exists( 'cljournal_query' ) ) {
		echo '<p>' . esc_html__( 'The change journal is not loaded yet. Reload the page.', 'sitekeel' ) . '</p>';
		return;
	}

	$filters = skeel_timeline_filters();
	$source  = $filters['source'];
	$days    = $filters['days'];
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only pagination.
	$paged    = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
	$per_page = 50;

	$rows     = cljournal_query( skeel_timeline_query_args( $filters, $per_page + 1, ( $paged - 1 ) * $per_page ) );
	$has_more = count( $rows ) > $per_page;
	$rows     = array_slice( $rows, 0, $per_page );
	?>
	<p class="description">
		<?php
		/* translators: %d: number of days changes are kept. */
		echo esc_html( sprintf( __( 'What changed on this site: WordPress, WooCommerce and CognitoLab plugins. No personal data is stored. Changes are kept for %d days.', 'sitekeel' ), skeel_retention_days() ) );
		?>
	</p>

	<form method="get" class="skeel-filters">
		<input type="hidden" name="page" value="sitekeel" />
		<input type="hidden" name="tab" value="timeline" />
		<label>
			<?php esc_html_e( 'Source', 'sitekeel' ); ?>
			<select name="source">
				<option value=""><?php esc_html_e( 'All', 'sitekeel' ); ?></option>
				<?php foreach ( cljournal_sources() as $s ) : ?>
					<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $source, $s ); ?>><?php echo esc_html( $s ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label>
			<?php esc_html_e( 'Period', 'sitekeel' ); ?>
			<select name="days">
				<?php foreach ( skeel_timeline_periods() as $d ) : ?>
					<?php /* translators: %d: number of days. */ ?>
					<option value="<?php echo esc_attr( (string) $d ); ?>" <?php selected( $days, $d ); ?>><?php echo esc_html( sprintf( _n( 'Last %d day', 'Last %d days', $d, 'sitekeel' ), $d ) ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label><input type="checkbox" name="important" value="1" <?php checked( $filters['important'] ); ?> /> <?php esc_html_e( 'Important changes only', 'sitekeel' ); ?></label>
		<?php submit_button( __( 'Filter', 'sitekeel' ), 'secondary', '', false ); ?>
	</form>
	<?php
	/**
	 * Fires under the timeline filters, e.g. for an export button.
	 *
	 * @param array $filters Current filters: source, days, important.
	 */
	do_action( 'skeel_timeline_tools', $filters );
	?>

	<?php if ( empty( $rows ) ) : ?>
		<p><?php esc_html_e( 'No changes recorded for this period yet.', 'sitekeel' ); ?></p>
		<?php
		return;
	endif;
	?>

	<table class="widefat striped skeel-timeline">
		<thead>
			<tr>
				<th><?php esc_html_e( 'When', 'sitekeel' ); ?></th>
				<th><?php esc_html_e( 'Source', 'sitekeel' ); ?></th>
				<th><?php esc_html_e( 'What changed', 'sitekeel' ); ?></th>
				<th><?php esc_html_e( 'By', 'sitekeel' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $rows as $row ) : ?>
				<?php
				$link  = skeel_object_edit_link( $row );
				$actor = (int) $row['actor_id'] ? get_userdata( (int) $row['actor_id'] ) : false;
				?>
				<tr class="<?php echo skeel_is_important( $row ) ? 'skeel-important' : ''; ?>">
					<td><?php echo esc_html( get_date_from_gmt( $row['occurred_at'], get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></td>
					<td><span class="skeel-source skeel-source-<?php echo esc_attr( $row['source'] ); ?>"><?php echo esc_html( $row['source'] ); ?></span></td>
					<td>
						<?php if ( $link ) : ?>
							<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( skeel_describe_event( $row ) ); ?></a>
						<?php else : ?>
							<?php echo esc_html( skeel_describe_event( $row ) ); ?>
						<?php endif; ?>
					</td>
					<td><?php echo $actor ? esc_html( $actor->display_name ) : esc_html__( 'System', 'sitekeel' ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<p class="skeel-pager">
		<?php if ( $paged > 1 ) : ?>
			<a class="button" href="<?php echo esc_url( add_query_arg( 'paged', $paged - 1 ) ); ?>"><?php esc_html_e( 'Newer', 'sitekeel' ); ?></a>
		<?php endif; ?>
		<?php if ( $has_more ) : ?>
			<a class="button" href="<?php echo esc_url( add_query_arg( 'paged', $paged + 1 ) ); ?>"><?php esc_html_e( 'Older', 'sitekeel' ); ?></a>
		<?php endif; ?>
	</p>
	<?php
}

/**
 * @return int Days of history kept by the journal.
 */
function skeel_retention_days() {
	return max( 1, (int) apply_filters( 'cljournal_retention_days', 90 ) );
}

/**
 * @return int[] Periods offered in the timeline filter, never longer than the history kept.
 */
function skeel_timeline_periods() {
	$keep    = skeel_retention_days();
	$periods = array_filter(
		array( 1, 7, 30, 90, 180, 365 ),
		static function ( $d ) use ( $keep ) {
			return $d <= $keep;
		}
	);
	return array_values( $periods );
}

/**
 * Timeline filters from the request (read-only view state).
 *
 * @return array{source: string, days: int, important: bool}
 */
function skeel_timeline_filters() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only filters.
	$source    = isset( $_REQUEST['source'] ) ? sanitize_key( wp_unslash( $_REQUEST['source'] ) ) : '';
	$days      = isset( $_REQUEST['days'] ) ? absint( $_REQUEST['days'] ) : 30;
	$important = ! empty( $_REQUEST['important'] );
	// phpcs:enable

	return array(
		'source'    => $source,
		'days'      => in_array( $days, skeel_timeline_periods(), true ) ? $days : 30,
		'important' => $important,
	);
}

/**
 * @param array $filters From skeel_timeline_filters().
 * @param int   $limit   Rows.
 * @param int   $offset  Offset.
 * @return array cljournal_query() args.
 */
function skeel_timeline_query_args( array $filters, $limit, $offset = 0 ) {
	return array(
		'after'  => gmdate( 'Y-m-d H:i:s', time() - $filters['days'] * DAY_IN_SECONDS ),
		'source' => $filters['source'],
		'events' => $filters['important'] ? array_keys( skeel_important_events() ) : array(),
		'limit'  => $limit,
		'offset' => $offset,
	);
}

/**
 * @param array $row Journal row.
 * @return string Edit URL or empty.
 */
function skeel_object_edit_link( array $row ) {
	$id = (int) $row['object_id'];
	if ( ! $id || ! in_array( $row['object_type'], array( 'product', 'page', 'bar' ), true ) || ! current_user_can( 'edit_post', $id ) ) {
		return '';
	}
	return (string) get_edit_post_link( $id, 'raw' );
}

/**
 * Portfolio tab.
 */
function skeel_render_portfolio_tab() {
	$license_labels = array(
		'none'     => __( 'Free', 'sitekeel' ),
		'active'   => __( 'Pro, licensed', 'sitekeel' ),
		'inactive' => __( 'Pro, not licensed', 'sitekeel' ),
		'unknown'  => __( 'Pro', 'sitekeel' ),
	);
	$visibility_labels = array(
		'observed'     => __( 'Seen automatically', 'sitekeel' ),
		'needs_events' => __( 'Needs plugin update', 'sitekeel' ),
	);
	?>
	<p class="description"><?php esc_html_e( 'CognitoLab plugins on this site. Each one works on its own; together they share one change timeline.', 'sitekeel' ); ?></p>
	<p class="description">
		<?php
		/* translators: 1: journal library version, 2: event contract version. */
		echo esc_html( sprintf( __( 'Change journal %1$s, event contract v%2$d.', 'sitekeel' ), defined( 'CLJOURNAL_VERSION' ) ? CLJOURNAL_VERSION : '—', defined( 'CLJOURNAL_CONTRACT_VERSION' ) ? CLJOURNAL_CONTRACT_VERSION : 0 ) );
		?>
	</p>

	<table class="widefat striped skeel-portfolio">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Plugin', 'sitekeel' ); ?></th>
				<th><?php esc_html_e( 'Status', 'sitekeel' ); ?></th>
				<th><?php esc_html_e( 'Edition', 'sitekeel' ); ?></th>
				<th><?php esc_html_e( 'In the timeline', 'sitekeel' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( skeel_portfolio_status() as $item ) : ?>
				<tr>
					<td><strong><?php echo esc_html( $item['name'] ); ?></strong></td>
					<td>
						<?php
						if ( $item['active'] ) {
							/* translators: %s: version number. */
							echo esc_html( sprintf( __( 'Active, %s', 'sitekeel' ), $item['version'] ) );
						} elseif ( $item['installed'] ) {
							esc_html_e( 'Installed, inactive', 'sitekeel' );
						} else {
							esc_html_e( 'Not installed', 'sitekeel' );
						}
						?>
					</td>
					<td><?php echo esc_html( $item['installed'] ? $license_labels[ $item['license'] ] : '—' ); ?></td>
					<td>
						<span class="skeel-vis skeel-vis-<?php echo esc_attr( $item['visibility'] ); ?>"><?php echo esc_html( $visibility_labels[ $item['visibility'] ] ); ?></span>
						<br /><span class="description"><?php echo esc_html( $item['summary'] ); ?></span>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php

	skeel_render_ai_status();
}
