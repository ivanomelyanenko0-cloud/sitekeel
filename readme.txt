=== Sitekeel ===
Contributors: lukystile
Tags: activity log, change log, timeline, woocommerce, audit
Requires at least: 6.3
Tested up to: 7.1
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A plain-language timeline of what changed on your site: plugins, theme, pages, WooCommerce products and prices, promo bars and banners.

== Description ==

When something on a site suddenly looks different, sells less or breaks, the first question is always "what changed?". Sitekeel answers it with one readable timeline:

* **WordPress:** plugins activated, deactivated or updated, theme switched or updated, WordPress updated, pages published or taken offline.
* **WooCommerce:** products published or taken offline, price, sale price and stock status changes.
* **Promotions and more, when those plugins are active:** Heralda bars turned on or off, rescheduled or retargeted; ProofBlocks banners, counters and pricing tables added to or removed from live pages; Watermark Guru setting changes; changes AI agents made through Tillkeeper.
* **Important changes stand out.** Filter the timeline to the changes that can take a promotion, a product or the whole shop offline, and see them marked on the dashboard widget.
* **Who did it.** Every change shows the user who made it, or "System" for automatic updates.
* **Portfolio screen.** See which CognitoLab plugins are active, what the timeline can see from each, and whether the WordPress AI Client is ready for plugins that use AI.

Nothing needs setting up: activate the plugin and the timeline starts filling in.

= Private and light =

The timeline stores IDs, types and before/after values of settings only - never customer names, emails, addresses or order contents. Changes are collected in memory and written once at the end of the request, in a single query. Changes are kept for 90 days.

= Sitekeel Pro =

[Sitekeel Pro](https://cognitolab.net/products/sitekeel) keeps changes for up to two years or for good, exports any filtered view as CSV, and emails you when an important change happens.

= For developers =

Any plugin can add its own changes without depending on Sitekeel:

`do_action( 'cognitolab_journal_event', 'my-plugin', 'something_changed', array( 'object_type' => 'product', 'object_id' => 123, 'data' => array( 'before' => 'a', 'after' => 'b' ) ) );`

If nothing listens, the call does nothing.

== External services ==

This plugin does not connect to any external service. Everything is recorded and shown on your own site.

== Installation ==

1. Install and activate Sitekeel.
2. Open **Sitekeel** in the admin menu, or look at the **Recent site changes** widget on the dashboard.

== Frequently Asked Questions ==

= Does it record changes made before it was installed? =

No. The timeline starts when the plugin is activated.

= Does it slow my site down? =

No. Sitekeel only does work when something actually changes - for example when a product sells out during checkout. Changes are gathered in memory and written in one query at the end of the request, and old entries are cleaned up once a day. Visitors browsing the shop trigger nothing.

= What happens to the timeline if I uninstall? =

The timeline is shared with other CognitoLab plugins that use it, such as StoreWhy. It is removed when the last of them is uninstalled.

== Screenshots ==

1. The timeline, with important changes marked.
2. The Portfolio screen: which plugins the timeline sees, and the WordPress AI status.
3. The Recent site changes dashboard widget.

== Changelog ==

= 1.0.0 =
* First public release.
* Timeline of WordPress, WooCommerce and CognitoLab plugin changes, with source, period and "important only" filters.
* Recent site changes dashboard widget.
* Portfolio screen with WordPress AI status.
