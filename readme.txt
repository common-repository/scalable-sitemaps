=== Scalable Sitemaps ===
Contributors: rcoll
Tags: xml, sitemaps, sitemap, google sitemaps
Requires at least: 3.5
Tested up to: 4.4.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Scalable Sitemaps is a WordPress plugin that will provide fast, clean and efficient xml-compliant sitemaps for your website.

== Description ==

Scalable Sitemaps is a WordPress plugin that will provide fast, clean and efficient xml-compliant sitemaps for your website. Many sitemap and SEO plugins do not have great compatibility with large sites and will take a long time to generate a sitemap (if it gets generated at all). This solves that problem by using some basic raw connections to your database and generating simple sitemaps in real-time as they are requested.

== Installation ==

Automatic Installation:

1. Go to Admin - Plugins - Add New and search for "Scalable Sitemaps"
2. Click on Install
3. Click on Activate

Manual Installation:

1. Download scalable-sitemaps.zip
2. Unzip and upload the "scalable-sitemaps" folder to your "/wp-content/plugins/" directory
3. Activate the plugin through the "Plugins" menu in WordPress

Configuration:

This plugin does not have any administration interface and when activated "just works". If you're code saavy, however, here are a few filters that you can use to change the behavior of the plugin:
* scalable_sitemaps_news_category – Return the slug of the category which you want to appear on your news sitemap.
* scalable_sitemaps_news_days - Filter in the number of days' posts you wish the news sitemap to contain.
* scalable_sitemaps_pages – Return false to disable displaying pages in the sitemap – alternatively, filter which categories are shown.
* scalable_sitemaps_categories – Return false to disable displaying category pages in the sitemap – alternatively, filter which categories are shown.
* scalable_sitemaps_users – Return false to disable displaying user pages in the sitemap – alternatively, filter which users are shown.
* scalable_sitemaps_post_types - Filter to allow custom post types to be included in the daily sitemaps.
* scalable_sitemaps_custom_taxonomies - Filter to allow custom taxonomies to be included in sitemap-taxonomies.xml.
* scalable_sitemaps_tags - Return false to disable displaying tag pages in teh sitemap - alternatively, filter which tags are shown.

== Frequently Asked Questions ==

None yet.

== Changelog ==
= 1.1.3 =
* Prevent tags sitemap link from outputting when tags sitemap is disabled

= 1.1.2 =
* Added support for custom post types
* Added support for custom taxonomies
* Created individual sitemaps for users and categories instead of including in the pages sitemap

= 1.1 =
* Added additional sanitation and escaping where necessary
* Added tag sitemap
* Added docblocks and inline comments where needed

= 1.0.2 =
* Slight tweak to the datestamp on the news sitemap

= 1.0.1 =
* Tweaked the news sitemap to make it slighly more performant
* Added one new hook (scalable_sitemaps_news_days) to allow changing how many days of posts the news sitemap holds

= 1.0 =
* Initial submission of plugin files

== Upgrade Notice ==

= 1.0.2 =
* Slight tweak to the datestamp on the news sitemap

= 1.0.1 =
* Increase the performance of the news sitemap

= 1.0 =
* Download the first version of the plugin