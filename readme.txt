=== Plugin Info ===
Contributors: johnbillion
Tags: plugin, info, data, utility, developer, meta, tool
Requires at least: 2.7
Tested up to: 2.7
Stable tag: trunk

Provides a simple way of displaying up-to-date information about specific WordPress Plugin Directory hosted plugins in your blog posts and pages.

== Description ==

**This plugin is currently under active development.** Please feel free to try it out but it is not feature complete and may well be very broken at any point in time. [Feedback](http://lud.icro.us/wordpress-plugin-info/#comments) is very much appreciated.

This plugin provides a simple way of displaying up-to-date information about specific plugins hosted on the WordPress Plugin Directory in your blog posts and pages. It is intended for plugin authors who want to display details of their own plugins from the WP Plugin Directory on their blog and want those details to remain up to date. It's also useful for bloggers who may blog about plugins and would like the details in their blog posts to remain up to date.

This plugin uses WordPress shortcodes so it's ridiculously easy to include any information about a particular plugin in your post or page.

= Er, what? =

Ok, here's how it works. You write a new plugin and get it accepted into the WordPress Plugin Directory. Now you want to blog about your plugin and include various details of the plugin in your blog post (eg. the plugin version number or the last updated date).

You could type this information into your post but this means that in a few days/weeks/months' time when you update your plugin, you'll end up having to edit this information which can become a bore if you regularly release updates to the plugin.

This plugin allows you to use shortcodes in your blog posts and pages which fetches this information right from the WordPress Plugin Directory (using the little-documented Plugin API), and therefore the information always remains up to date. You can commit changes to your plugin and the information on your blog will remain current.

= Here's an example =

This plugin uses shortcodes so it's ridiculously easy to include any information about a particular plugin in your post or page:

My plugin has been downloaded `[plugin downloaded]` times!

This will produce the following content in your blog post:

My plugin has been downloaded 1,650 times!

The download count will remain current without you having to touch your blog post again.

== Installation ==

This plugin only works with WordPress 2.7 or later. Earlier versions of WordPress did not include the necessary Plugin API for the plugin to function.

1. Unzip the ZIP file and drop the folder straight into your wp-content/plugins directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Now read the usage guidelines below.

= Usage =

1. Write a new blog post or page, or open an existing post or page for editing.
2. Add a custom field to the post with the name 'plugin' and give it a value of the slug of one of your plugins in the WP Plugin Directory (see [screenshot #1](http://wordpress.org/extend/plugins/plugin-info/screenshots/)).
3. Add a shortcode to your blog entry like this: `[plugin version]` and save the post. (That's the word 'plugin' and not the slug of your plugin by the way).
4. Take a look at your post and the version number of your plugin will be displayed.

For a list of all the shortcodes you can use, see the [FAQ page](http://wordpress.org/extend/plugins/plugin-info/faq/).

== Frequently Asked Questions ==

= Is this plugin for me? =

This plugin is only going to be of use to you if:

1. You are a plugin author and you want a ridiculously easy way to include up to date information about any of your plugins in your blog posts or pages.
2. You are the author of a blog that highlights plugins of interest and you want to ensure that information in your posts remains up to date.

= Which attributes of my plugin can I display? =

Below is a list of all the available shortcodes.

Most shortcodes that display a formatted link can have their default link text overridden by adding a 'text' parameter to the shortcode. For example: `[plugin homepage text='Homepage']` will display a link to the plugin homepage with the link text 'Homepage'.

* `[plugin name]` - The plugin name
* `[plugin homepage]` - A formatted link to the plugin's homepage with 'Visit plugin homepage' as the link text
* `[plugin homepage_url]` - The plain URL of the plugin's homepage
* `[plugin link]` - A formatted link to the plugin's page on the WP Plugin Directory with the plugin name as the link text
* `[plugin link_url]` - The plain URL of the plugin's page on the WP Plugin Directory
* `[plugin download]` - A formatted link to the plugin's ZIP file with 'Download' as the link text
* `[plugin download_url]` - The plain URL of the plugin's ZIP file
* `[plugin author]` - A formatted link to the plugin author's homepage with the author's name as the link text (if the author doesn't have a homepage this will simply display their name)
* `[plugin author_url]` - The plain URL of the plugin author's homepage
* `[plugin author_name]` - The plugin author's name
* `[plugin version]` - The plugin version number
* `[plugin description]` - The full description of the plugin
* `[plugin requires]` - The 'Requires at least' WP version number
* `[plugin tested]` - The 'Tested up to' WP version number
* `[plugin downloaded]` - The all time download count with comma-separated thousands (eg. "12,345")
* `[plugin downloaded_raw]` - The all time download count as a raw number (eg. "12345")
* `[plugin last_updated]` - The date the plugin was last updated, formatted according to your Date Format settings under Settings->General (eg. "20 January 2008")
* `[plugin last_updated_ago]` - How long ago the plugin was last updated (eg. "20 days ago")
* `[plugin last_updated_raw]` - The date the plugin was last updated, in the format "yyyy-mm-dd"
* `[plugin slug]` - The plugin slug
* `[plugin rating]` - The plugin's star rating as a whole number out of 5 (given by visitors to wp.org)
* `[plugin rating_raw]` - The plugin's actual average rating as a score out of 100 (given by visitors to wp.org)
* `[plugin num_ratings]` - The number of people who've rated your plugin on wp.org
* `[plugin tags]` - A comma-separated list of the plugin's tags

= The geek stuff =

The plugin information is collected from wp.org each time you save your post or page. It uses the new Plugin API available in WordPress 2.7 (which is used mainly for browsing/downloading plugins from within WordPress). The plugin data is stored as an associative array in a custom field called 'plugin-info' linked to your post or page.

**This plugin is not feature complete** because the plugin does not periodically update the data from wp.org, which is of course the whole point of this plugin. This is only because I simply haven't written that bit yet. Updates will come soon.
