=== Sitekeel ===
Contributors: lukystile
Tags: activity log, change log, timeline, woocommerce, audit
Requires at least: 6.3
Tested up to: 7.1
Stable tag: 0.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

One timeline of what changed on your site: WordPress, WooCommerce and CognitoLab plugins.

== Description ==

Sitekeel keeps a simple timeline of the changes that matter to a store: plugins and themes switched or updated, products published or taken offline, prices, sale prices and stock status changing, and - when those plugins are installed - Heralda bars turning on or off and ProofBlocks banners appearing on or disappearing from live pages.

* **No personal data.** The timeline stores object IDs, types and before/after values of settings only. Never customer names, emails, addresses or order contents.
* **Works on its own.** No other plugin is required. CognitoLab plugins that are installed simply show up in the timeline.
* **Portfolio screen.** See which CognitoLab plugins are active and which of their changes the timeline can see.

Events are kept for 90 days by default (filter `cljournal_retention_days`).

= For developers =

Any plugin can add its own events without depending on Sitekeel:

`do_action( 'cognitolab_journal_event', 'my-plugin', 'something_changed', array( 'object_type' => 'product', 'object_id' => 123, 'data' => array( 'before' => 'a', 'after' => 'b' ) ) );`

If nothing listens, the call does nothing.

== Frequently Asked Questions ==

= Does it slow my site down? =

Events are collected in memory and written once at the end of the request, in a single query.

= What happens to the timeline if I uninstall? =

The timeline table is shared with other CognitoLab plugins that use it. It is removed when the last of them is uninstalled.

== Changelog ==

= 0.1.0 =
* Skeleton: change journal, timeline, portfolio screen.
