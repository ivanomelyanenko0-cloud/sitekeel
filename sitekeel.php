<?php
/**
 * Plugin Name:       Sitekeel – Activity Log & Audit Log with Plain-Language Timeline
 * Plugin URI:        https://cognitolab.net/products/sitekeel
 * Description:       A plain-language timeline of what changed on your site: plugins, theme, pages, WooCommerce products and prices, promo bars and banners.
 * Version:           1.0.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            CognitoLab
 * Author URI:        https://cognitolab.net
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sitekeel
 * Domain Path:       /languages
 *
 * "Sitekeel" is a working name - every internal identifier lives behind the
 * SKEEL_/skeel_ prefix so a rename stays a mechanical find/replace. The shared
 * change journal uses the company prefix (cljournal_, 'cognitolab_journal_event')
 * so product renames never touch it.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SKEEL_VERSION', '1.0.0' );
define( 'SKEEL_PLUGIN_FILE', __FILE__ );
define( 'SKEEL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SKEEL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once SKEEL_PLUGIN_DIR . 'lib/cognitolab-journal/loader.php';
cljournal_register_consumer( 'sitekeel' );

require_once SKEEL_PLUGIN_DIR . 'includes/describe.php';
require_once SKEEL_PLUGIN_DIR . 'includes/importance.php';
require_once SKEEL_PLUGIN_DIR . 'includes/ai-status.php';
require_once SKEEL_PLUGIN_DIR . 'includes/dashboard.php';
require_once SKEEL_PLUGIN_DIR . 'includes/portfolio.php';
require_once SKEEL_PLUGIN_DIR . 'includes/admin-page.php';

/**
 * @param string[] $links Plugin action links.
 * @return string[]
 */
function skeel_action_links( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=sitekeel' ) ) . '">' . esc_html__( 'Timeline', 'sitekeel' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'skeel_action_links' );
