=== WPML Calendar Widget ===
Contributors: headonfire
Tags: widget, calendar, wpml, multilingual, translation
Donate link: https://ihorvorotnov.com/donate
Requires at least: 4.0
Tested up to: 4.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A calendar widget compatible with WPML, counts only posts available in current language.

== Description ==
This is a fork of standard WordPress Calendar widget, but with WPML support.

The problem with standard calendar widget is that it runs custom SQL queries and has no hooks to alter that queries from a translation plugin like WPML. That results in a calendar counting all posts, regardless of what current language is and are there **translated** posts available in currently selected language.

This plugin fixes this problem. Use it instead of standard calendar if you have multilingual website powered by WPML plugin.

= Extras =
It also adds 2 additional CSS classes for styling purposes:
- `is-today` for, well, today in your calendar
- `is-current-day` for current day on a daily archive page

= Contributing =
Development takes place on [GitHub](https://github.com/ihorvorotnov/wpml-calendar-widget). Contributions are always welcome!

File issues here (on plugin support forum), or on GitHub.

== Installation ==
= Requirements and dependencies =
- This plugins depends on WPML, so it checks if WPML (WPML Multilingual CMS) is installed and activated. If WPML isn't there, you'll get nothing.
- PHP 5.3+

= From your WordPress dashboard =
1. Visit 'Plugins > Add New'
2. Search for 'WPML Calendar Widget'
3. Activate the plugin from your Plugins page.

= From WordPress.org =
1. Download WPML Calendar Widget
2. Extract the archive
3. Upload the 'wpml-calendar-widget' directory to your '/wp-content/plugins/' directory, using your favorite method (ftp, sftp, scp, etc...)
4. Activate the plugin from your Plugins page.

== Frequently Asked Questions ==
= I don't see any new widget =
This widget depends on WPML plugin. If it's not installed or activated, the widget will **not** be added.

= How to translate month names to my language? =
You don't need to. The widget takes month names from WordPress Core. You should be fine out of the box.

== Screenshots ==
1. New calendar widget is available
2. New calendar widget shows only links to days with posts in current language

== Changelog ==
= 1.1.0 =
Added class `is-today` to current calendar day's table cell. Also, if you are browsing daily archives, the day you're currently viewing has an extra `is-current-day` class.

= 1.0.0 =
Initial plugin release. Standard calendar with modified SQL queries to count only translated posts.

== Upgrade Notice ==
None.
