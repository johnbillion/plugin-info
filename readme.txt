=== Plugin Info ===
Contributors: johnbillion, sivel
Tags: plugin, info, data, utility, developer, meta, tool
Requires at least: 3.4
Tested up to: 4.8
Stable tag: 0.9.0

Provides a simple way of displaying up-to-date information about specific WordPress Plugin Directory hosted plugins in your blog posts and pages.

== Description ==

This plugin provides a simple way of displaying up-to-date information about specific plugins hosted on the WordPress Plugin Directory in your blog posts and pages. It is intended for plugin authors who want to display details of their own plugins from the WP Plugin Directory on their blog and want those details to remain up to date. It's also useful for bloggers who may blog about plugins and would like the details in their blog posts to remain up to date.

This plugin uses WordPress shortcodes so it's easy to include any information about a particular plugin in your post or page.

= Er, what? =

You want to blog about a particular plugin on your blog and include various details of it in your blog post (eg. the number of downloads or the last updated date). You could manually type this information into your post but this means that in a few days/weeks/months' time the information will be out of date.

This plugin allows you to use shortcodes in your blog posts and pages which periodically fetches this information from the WordPress Plugin Directory, therefore ensuring the information always remains up to date.

= Here's an example =

This plugin uses WordPress shortcodes so it's easy to include any information about a particular plugin in your post or page.

> This plugin has been downloaded [plugin downloaded] times!

This will produce the following content in your blog post:

> This plugin has been downloaded 1,650 times!

The download count will remain current without you having to touch your blog post again.

= Usage =

1. Write a new blog post or page, or open an existing post or page for editing.
2. In the 'Plugin Info' box on that screen, type the slug of the plugin (see [screenshot #1](https://wordpress.org/plugins/plugin-info/screenshots/)). The plugin slug is the last part of the URL of the plugin's page on wordpress.org.
3. Add a shortcode to your blog entry like this: `[plugin version]` and save the post. (That's the word `plugin` and not the slug of the plugin).
4. Take a look at your post and the version number of the plugin will be displayed.

For a complete list of all the fields you can display, see the [FAQ page](https://wordpress.org/plugins/plugin-info/faq/).

== Frequently Asked Questions ==

= Is this plugin for me? =

This plugin will be of use to you if:

1. You are a plugin author and you want an easy way to include up to date information about any of your plugins in your blog posts or pages.
2. You are the author of a blog that highlights plugins of interest and you want to ensure that information in your posts remains up to date.

= Which attributes of a plugin can I display? =

The available shortcodes can be seen from the post writing screen. Just click the `[show]` link in the 'Plugin Info' box.

Shortcodes which display a formatted hyperlink can have their default link text overridden via the `text` attribute on the shortcode. For example: `[plugin homepage text='Home Page']` will display a link to the plugin homepage with the link text 'Home Page'.

= Can I display plugin info outside of my blog posts? =

Yes! You can use the `plugin_info()` function anywhere in your template. The function takes two parameters, the slug of your plugin and the attribute you'd like to display. The following example will display the last updated date for my User Switching plugin:

    plugin_info( 'user-switching', 'updated' );`

You can also get the info rather than printing it out using the `get_plugin_info()` function:

    $updated = get_plugin_info( 'user-switching', 'updated' );`

Be aware that plugin information isn't cached between page requests using this method, so be sure to implement some caching of your own.

= The geek stuff =

The plugin information is collected from wordpress.org each time you save your post or page. It is updated hourly using WordPress' cron system and uses the Plugin API in WordPress core. The plugin data is cached as an associative array in a custom field called `plugin-info`, and the plugin slug you enter is saved as a custom field called `plugin`. This means you can also access the plugin data using `get_post_meta()`, but I'll let you figure that out for yourself.

== Screenshots ==

1. Adding a plugin to a post. Remember to use the slug and not the name, as using the name isn't 100% reliable.

== Changelog ==

For Plugin Info's changelog, please see [the Releases page on GitHub](https://github.com/johnbillion/plugin-info/releases).
