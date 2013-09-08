=== Plugin Info ===
Contributors: johnbillion, sivel
Tags: plugin, info, data, utility, developer, meta, tool
Requires at least: 3.4
Tested up to: 3.6
Stable tag: trunk

Provides a simple way of displaying up-to-date information about specific WordPress Plugin Directory hosted plugins in your blog posts and pages.

== Description ==

This plugin provides a simple way of displaying up-to-date information about specific plugins hosted on the WordPress Plugin Directory in your blog posts and pages. It is intended for plugin authors who want to display details of their own plugins from the WP Plugin Directory on their blog and want those details to remain up to date. It's also useful for bloggers who may blog about plugins and would like the details in their blog posts to remain up to date.

This plugin uses WordPress shortcodes so it's ridiculously easy to include any information about a particular plugin in your post or page.

= Er, what? =

You want to blog about a particular plugin on your blog and include various details of it in your blog post (eg. the number of downloads or the last updated date). You could manually type this information into your post but this means that in a few days/weeks/months' time the information will be out of date.

This plugin allows you to use shortcodes in your blog posts and pages which fetches this information right from the WordPress Plugin Directory, therefore ensuring the information always remains up to date.

= Here's an example =

This plugin uses WordPress shortcodes so it's ridiculously easy to include any information about a particular plugin in your post or page.

> This plugin has been downloaded `[plugin downloaded]` times!

This will produce the following content in your blog post:

> This plugin has been downloaded 1,650 times!

The download count will remain current without you having to touch your blog post again.

== Installation ==

You can install this plugin directly from your WordPress dashboard:

 1. Go to the *Plugins* menu and click *Add New*.
 2. Search for *Plugin Info*.
 3. Click *Install Now* next to the Plugin Info plugin.
 4. Activate the plugin.
 5. Now read the usage guidelines below.

Alternatively, see the guide to [Manually Installing Plugins](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Usage =

1. Write a new blog post or page, or open an existing post or page for editing.
2. In the 'Plugin Info' box on that screen, type the slug of the plugin (see [screenshot #1](http://wordpress.org/plugins/plugin-info/screenshots/)). The plugin slug is the last part of the URL of the plugin's page on wordpress.org.
3. Add a shortcode to your blog entry like this: `[plugin version]` and save the post. (That's the word 'plugin' and not the slug of the plugin by the way).
4. Take a look at your post and the version number of the plugin will be displayed.

For a complete list of all the shortcodes you can use, see the [FAQ page](http://wordpress.org/plugins/plugin-info/faq/).

== Frequently Asked Questions ==

= Is this plugin for me? =

This plugin is only going to be of use to you if:

1. You are a plugin author and you want a ridiculously easy way to include up to date information about any of your plugins in your blog posts or pages.
2. You are the author of a blog that highlights plugins of interest and you want to ensure that information in your posts remains up to date.

= Which attributes of a plugin can I display? =

The majority of the available shortcodes can be seen from the post writing screen. Just click the '[show]' link in the 'Plugin Info' box.

Please see http://lud.icro.us/wordpress-plugin-info/ for a complete list of all the available shortcodes. There are a few additional (less useful) shortcodes listed there.

Shortcodes which display a formatted hyperlink can have their default link text overridden by adding a 'text' parameter. For example: `[plugin homepage text='Homepage']` will display a link to the plugin homepage with the link text 'Homepage'.

= Can I display plugin info outside of my blog posts? =

Yes! You can use the `plugin_info()` function anywhere in your template. The function takes two parameters, the slug of your plugin and the attribute you'd like to display. The following example will display the last updated date for my User Switching plugin:

`<?php plugin_info( 'user-switching', 'updated' ); ?>`

You can also get the info rather than printing it out using the `get_plugin_info()` function:

`<?php $updated = get_plugin_info( 'user-switching', 'updated' ); ?>`

= The geek stuff =

The plugin information is collected from wp.org each time you save your post or page. It is updated hourly using WordPress' cron system and uses the Plugin API available in WordPress 2.7 or later. The plugin data is stored as an associative array in a custom field called 'plugin-info', and the plugin slug you enter is saved as a custom field called 'plugin'. For supergeeks, this means you can also access the plugin data using `get_post_meta()`, but I'll let you figure that out for yourself.

== Screenshots ==

1. Adding a plugin to a post. Remember to use the slug and not the name, as using the name isn't 100% reliable.

== Upgrade Notice ==

= 0.8.1 =
* Add support for the new [plugin donate] attribute and add some missing i18n.

== Changelog ==

= 0.8.1 =
* Add support for the new [plugin donate] attribute and add some missing i18n.

= 0.8 =
* Update the URL of plugins in the repo.

= 0.7.8 =
* Small UI fix for WordPress 3.3.
* Add some missing l10n.

= 0.7.7 =
* Removed a PHP notice that was causing problems when WP_DEBUG was on.

= 0.7.6 =
* Addition of a new <code>plugin_info()</code> template tag for displaying plugin info outside of your posts. Based on code by Melvin Ram.
* Various code improvements including caching improvements by Matt Martz.

= 0.7.5 =
* Fix problems preventing some sites from updating the plugin info.

= 0.7.4 =
* Support for the [plugin compatibility](http://wordpress.org/development/2009/10/plugin-compatibility-beta/) attribute.
* Addition of a '<code>plugin_info_shortcode</code>' filter so plugins/themes can format the shortcode output.

= 0.7.3 =
* Fixed absolute URLs for screenshots

= 0.7.2 =
* Fixed WordPress 2.8 compatibility.

= 0.7.1 =
* Improvement to the [plugin latest_change] detection.

= 0.7 =
* Bugfix to prevent loss of plugin info when using Quick Edit.
* Support for changelogs.

= 0.6 =
* Shortcodes in the post meta box can now be clicked to insert them into your post.
* Addition of custom sub-headings contained in the 'other notes' section.

= 0.5.1 =
* Ensure all 'plugin info' posts are found in the update process.

= 0.5 =
* Matt Martz is in ur plugins fixin ur codes. (Hourly updates now work.)

= 0.4.1 =
* Addition of 'profile', 'profile_url' and 'other_notes' shortcodes.

= 0.4 =
* Periodic updating of plugin information using WP-Cron.
* Addition of 'screenshots' shortcode.
* Addition of a nice meta box on the writing screen.
* Better overall error handling.
* More props to: Matt Martz.

= 0.3 =
* A completely broken release :(

= 0.2 =
* Additions and updates to several shortcode attributes.
* Mad props to: Matt Martz & Kim Parsell.

= 0.1 =
* Initial release.
