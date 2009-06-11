<?php
/*
Plugin Name:  Plugin Info
Description:  Provides a simple way of displaying up-to-date information about specific WordPress Plugin Directory hosted plugins in your blog posts and pages.
Plugin URI:   http://lud.icro.us/wordpress-plugin-info/
Version:      0.6
Author:       John Blackbourn
Author URI:   http://johnblackbourn.com/
License:      GNU General Public License
Requires:     2.7
Tested up to: 2.8

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

Changelog:

0.6     2009/03/25
Shortcodes in the post meta box can now be clicked to insert them into your post.
Addition of custom sub-headings contained in the 'other notes' section.

0.5.1   2009/03/14
Ensure all 'plugin info' posts are found in the update process.

0.5     2009/03/11
Matt Martz is in ur plugins fixin ur codes. (Hourly updates now work.)

0.4.1   2009/03/06
Addition of 'profile', 'profile_url' and 'other_notes' shortcodes.

0.4     2009/03/05
Periodic updating of plugin information using WP-Cron.
Addition of 'screenshots' shortcode.
Addition of a nice meta box on the writing screen.
Better overall error handling.
    More props to: Matt Martz.

0.3     2009/01/31
A completely broken release :(

0.2     2009/01/20
Additions and updates to several shortcode attributes.
    Mad props to: Matt Martz & Kim Parsell.

0.1     2008/12/16
Initial release.

*/

class PluginInfo {

	var $plugin;

	function PluginInfo() {

		add_action( 'admin_menu',         array( &$this, 'admin_menu' ) );
		add_action( 'admin_head',         array( &$this, 'admin_head' ) );
		add_action( 'save_post',          array( &$this, 'save_plugin_info' ) );
		add_action( 'update_plugin_info', array( &$this, 'update_plugin_info' ) );
		add_filter( 'ozh_adminmenu_icon', array( &$this, 'ozh_adminmenu_icon' ) );
		add_shortcode( 'plugin',          array( &$this, 'plugin_info_shortcode' ) );

		register_activation_hook( __FILE__, array( &$this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );

		$this->plugin = array(
			'url' => WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) ),
			'dir' => WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) )
		);

	}

	function get_plugin_info( $slug = null ) {

		if ( !$slug )
			return false;

		require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

		$info   = array();
		$slug   = sanitize_title( $slug );
		$plugin = plugins_api( 'plugin_information', array( 'slug' => $slug ) );

		if ( !$plugin or is_wp_error( $plugin ) )
			return false;

		#die( '<pre>' . print_r( $plugin, true ) . '</pre>' );

		$attributes = array(
			'name'             => 'name',
			'slug'             => 'slug',
			'version'          => 'version',
			'author'           => 'author',
			'profile_url'      => 'author_profile',
			'requires'         => 'requires',
			'tested'           => 'tested',
			'rating_raw'       => 'rating',
			'downloaded_raw'   => 'downloaded',
			'updated_raw'      => 'last_updated',
			'num_ratings'      => 'num_ratings',
			'description'      => array( 'sections', 'description' ),
			'installation'     => array( 'sections', 'installation' ),
			'faq'              => array( 'sections', 'faq' ),
			'screenshots'      => array( 'sections', 'screenshots' ),
			'other_notes'      => array( 'sections', 'other_notes' ),
			'download_url'     => 'download_link',
			'homepage_url'     => 'homepage',
			'tags'             => 'tags'
		);

		foreach ( $attributes as $name => $key ) {
		
			if ( is_array( $key ) ) {
				$_key = $plugin->$key[0];
				if ( isset( $_key[$key[1]] ) )
					$info[$name] = $_key[$key[1]];
			} else {
				if ( isset( $plugin->$key ) )
					$info[$name] = $plugin->$key;
			}

			if ( isset( $info[$name] ) and is_array( $info[$name] ) )
				$info[$name] = implode( ', ', $info[$name] );

		}

		$info['downloaded']  = number_format( $info['downloaded_raw'] );
		$info['rating']      = ceil( 0.05 * $info['rating_raw'] );
		$info['link_url']    = "http://wordpress.org/extend/plugins/{$info['slug']}/";
		$info['updated']     = date( get_option('date_format'), strtotime( $info['updated_raw'] ) );
		$info['updated_ago'] = sprintf( __('%s ago'), human_time_diff( strtotime( $info['updated_raw'] ) ) );
		$info['download']    = '<a href="' . $info['download_url'] . '">%s</a>';
		$info['homepage']    = '<a href="' . $info['homepage_url'] . '">%s</a>';
		$info['link']        = '<a href="' . $info['link_url']     . '">%s</a>';
		$info['profile']     = '<a href="' . $info['profile_url']  . '">%s</a>';

		if ( isset( $info['screenshots'] ) )
			$info['screenshots'] = preg_replace( "|src='([^\']+)'|i","src='{$info['link_url']}$1'", $info['screenshots'] );

		if ( preg_match( '|href="([^"]+)"|i', $info['author'], $matches ) )
			$info['author_url'] = $matches[1];

		if ( preg_match( '|>([^<]+)<|i', $info['author'], $matches ) )
			$info['author_name'] = $matches[1];
		else
			$info['author_name'] = $info['author'];

		if ( isset( $info['other_notes'] ) and preg_match_all( '|<h3>([^<]+)</h3>|i', $info['other_notes'], $matches, PREG_SET_ORDER ) ) {
			for ( $i = 0; isset( $matches[$i] ); $i++ ) {
				$end = isset( $matches[$i+1][0] ) ? $matches[$i+1][0] : '$';
				preg_match( '|' . $matches[$i][0] . '(.*)' . $end . '|si', $info['other_notes'], $match );
				$info[sanitize_title( $matches[$i][1] )] = $match[1];
			}
		}

		# The following values are *deprecated* but remain for those who may be using them:
		$info['download_link']     = $info['download_url']; # use download_url instead
		$info['tags_list']         = $info['tags'];         # use tags instead
		$info['extend']            = $info['link_url'];     # use link_url instead
		$info['last_updated_nice'] = $info['updated'];      # use updated instead
		$info['last_updated']      = $info['updated'];      # use updated instead
		$info['last_updated_ago']  = $info['updated_ago'];  # use updated_ago instead
		$info['last_updated_raw']  = $info['updated_raw'];  # use updated_raw instead

		/*
		 * The `plugin_info` filter below allows a plugin/theme to add or
		 * modify the available shortcodes.
		 *
		 * Example 1:
		 *
		 * function myfunction( $info ) {
		 * 	$info['fullname'] = $info['name'] . ' v' . $info['version'];
		 * 	return $info;
		 * }
		 * add_filter( 'plugin_info', 'myfunction' );
		 *
		 * The above code would create a `[plugin fullname]` shortcode which
		 * would return something like `My Wonderful Plugin v1.0`
		 *
		 * Example 2:
		 *
		 * function myfunction( $info ) {
		 * 	$info['requires'] = 'Requires at least WordPress version ' . $info['requires'];
		 * 	return $info;
		 * }
		 * add_filter( 'plugin_info', 'myfunction' );
		 *
		 * The above would modify the `[plugin requires]` shortcode so it returns
		 * a full sentence explaining the minimum WP version requirement.
		 *
		 */

		return apply_filters( 'plugin_info', $info );

	}

	function update_plugin_info() {

		$q = new WP_Query;

		$posts = $q->query( array(
			'posts_per_page' => -1,
			'meta_key'       => 'plugin',
			'post_type'      => 'any'
		) );

		if ( !count( $posts ) )
			return;

		foreach ( $posts as $p ) {
			$plugin_info = $this->get_plugin_info( stripslashes( $p->meta_value ) );
			if ( $plugin_info )
				update_post_meta( $p->ID, 'plugin-info', $plugin_info );
		}

	}

	function save_plugin_info( $post_ID ) {

		if ( wp_is_post_revision( $post_ID ) or wp_is_post_autosave( $post_ID ) )
			return;

		if ( !isset( $_POST['plugin_info'] ) ) {

			delete_post_meta( $post_ID, 'plugin' );
			delete_post_meta( $post_ID, 'plugin-info' );

		} else {

			$plugin = trim( $_POST['plugin_info'] );
			$plugin_info = $this->get_plugin_info( $plugin );

			if ( !$plugin_info )
				return false; # @TODO: display error msg?

			if ( !update_post_meta( $post_ID, 'plugin', $plugin ) )
				add_post_meta( $post_ID, 'plugin', $plugin );
			if ( !update_post_meta( $post_ID, 'plugin-info', $plugin_info ) )
				add_post_meta( $post_ID, 'plugin-info', $plugin_info );

		}

		return;

	}

	function plugin_info_shortcode( $atts ) {

		global $post;

		$atts = shortcode_atts( array(
			0      => 'name',
			'text' => ''
		), $atts );

		$meta = get_post_meta( $post->ID, 'plugin-info', true );

		if ( !isset( $meta[$atts[0]] ) )
			return '';

		if ( false !== strpos( $meta[$atts[0]], '%s' ) ) {

			$texts = array(
				'download' => __( 'Download', 'plugin-info' ),
				'homepage' => __( 'Visit plugin homepage', 'plugin-info' ),
				'link'     => $meta['name'],
				'profile'  => $meta['author_name']
			);

			$text = ( $atts['text'] ) ? $atts['text'] : $texts[$atts[0]];
			$meta[$atts[0]] = str_replace( '%s', $text, $meta[$atts[0]] );

		}

		return $meta[$atts[0]];

	}

	function admin_head() {
		if ( $this->is_post_writing_screen() ) {
		?>
		<script type="text/javascript"><!--

			jQuery(function($) {

				$('#plugin_info_shortcodes').hide();
				$('#plugin_info_show_shortcodes').show();
				$('#plugin_info_show_shortcodes').click(function(){
					$('#plugin_info_shortcodes').toggle();
					text = $('#plugin_info_shortcodes').is(':visible') ? '[ hide ]' : '[ show ]';
					$(this).text(text);
					return false;
				});
				$('#plugin_info_shortcodes dt').click(function(){
					if ( ( typeof window.tinyMCE != 'undefined' ) && ( window.tinyMCE.activeEditor ) && ( !tinyMCE.activeEditor.isHidden() ) ) {
						tinyMCE.execCommand('mceInsertContent', false, $(this).text() + '</p>');
					} else {
						edInsertContent(document.getElementById('content'), $(this).text());
					}
				});

			} );

		--></script>
		<style type="text/css">

			#plugin_info {
				width: 98%;
				margin-top: 5px
			}

			#plugin_info_shortcodes dl {
				margin: 5px 5px 10px;
			}

			#plugin_info_shortcodes dl {
				overflow: auto;
				font-size: 0.9em;
				border-bottom: 1px solid #dfdfdf;
				padding-bottom: 8px;
			}

			#plugin_info_shortcodes dt {
				float: left;
				clear: left;
				width: 50%;
				margin: 0px 1% 5px 0px;
				cursor: pointer;
			}

			#plugin_info_shortcodes dt:hover {
				color: #D54E21;
			}

			#plugin_info_shortcodes dd {
				float: left;
				width: 49%;
				margin-bottom: 5px;
			}

			#plugin_info_show_shortcodes {
				display: none;
			}

			#plugin_info_shortcodes p {
				font-style: italic;
			}

		</style>
		<?php
		}
	}

	function meta_box( $post ) {
		?>
		<label for="plugin_info"><?php _e( 'Plugin slug:', 'plugin_info' ); ?></label>
		<input type="text" name="plugin_info" id="plugin_info" value="<?php echo attribute_escape( get_post_meta( $post->ID, 'plugin', true ) ); ?>" />
		<p class="howto"><?php _e( 'To display information about a plugin, you should use one of the shortcodes below.', 'plugin_info' ); ?></p>
		<?php # @TODO: i18n on this list: ?>
		<div id="plugin_info_shortcodes">
			<p>Plain info:</p>
			<dl>
				<dt>[plugin author_name]</dt>
				<dd class="howto">Author&rsquo;s name</dd>
				<dt>[plugin author_url]</dt>
				<dd class="howto">Author&rsquo;s URL</dd>
				<dt>[plugin download_url]</dt>
				<dd class="howto">URL of ZIP file</dd>
				<dt>[plugin downloaded]</dt>
				<dd class="howto">Download count</dd>
				<dt>[plugin homepage_url]</dt>
				<dd class="howto">URL of homepage</dd>
				<dt>[plugin link_url]</dt>
				<dd class="howto">URL of wp.org page</dd>
				<dt>[plugin name]</dt>
				<dd class="howto">Name</dd>
				<dt>[plugin profile_url]</dt>
				<dd class="howto">URL of author&rsquo;s wp.org profile</dd>
				<dt>[plugin requires]</dt>
				<dd class="howto">&rsquo;Requires at least&lsquo; version number</dd>
				<dt>[plugin rating]</dt>
				<dd class="howto">Rating out of 5</dd>
				<dt>[plugin slug]</dt>
				<dd class="howto">Slug</dd>
				<dt>[plugin tags]</dt>
				<dd class="howto">List of tags</dd>
				<dt>[plugin tested]</dt>
				<dd class="howto">&rsquo;Tested up to&lsquo; version number</dd>
				<dt>[plugin updated_ago]</dt>
				<dd class="howto">Last updated ago (hours/days/weeks)</dd>
				<dt>[plugin updated]</dt>
				<dd class="howto">Last updated date</dd>
				<dt>[plugin version]</dt>
				<dd class="howto">Version number</dd>
			</dl>
			<p>Formatted info:</p>
			<dl>
				<dt>[plugin author]</dt>
				<dd class="howto">Link to author&rsquo;s homepage</dd>
				<dt>[plugin description]</dt>
				<dd class="howto">Long description</dd>
				<dt>[plugin download]</dt>
				<dd class="howto">Link to ZIP file</dd>
				<dt>[plugin homepage]</dt>
				<dd class="howto">Link to homepage</dd>
				<dt>[plugin link]</dt>
				<dd class="howto">Link to wp.org page</dd>
				<dt>[plugin profile]</dt>
				<dd class="howto">Link to author&rsquo;s wp.org profile</dd>
				<dt>[plugin screenshots]</dt>
				<dd class="howto">List of screenshots</dd>
				<dt>[plugin other_notes]</dt>
				<dd class="howto">Other notes</dd>
			</dl>
		</div>
		<p><a href="#" id="plugin_info_show_shortcodes">[ show ]</a></p>
		<?php
	}

	function admin_menu() {
		add_meta_box(
			'plugininfo',
			__( 'Plugin Info', 'plugin_info' ),
			array( &$this, 'meta_box' ),
			'post',
			'side'
		);
		add_meta_box(
			'plugininfo',
			__( 'Plugin Info', 'plugin_info' ),
			array( &$this, 'meta_box' ),
			'page',
			'side'
		);
	}

	function is_post_writing_screen() {
		foreach ( array( 'post.php', 'post-new.php', 'page.php', 'page-new.php' ) as $file )
			if ( strpos( $_SERVER['REQUEST_URI'], $file ) )
				return true;
		return false;
	}

	function ozh_adminmenu_icon( $hook ) {
		if ( $hook == 'plugin_info' )
			return $this->plugin['url'] . '/icon.png';
		return $hook;
	}

	function activate() {
		wp_schedule_event( time(), 'hourly', 'update_plugin_info' );
	}

	function deactivate() {
		wp_clear_scheduled_hook( 'update_plugin_info' );
	}

}

load_plugin_textdomain( 'plugin_info', PLUGINDIR . '/' . dirname( plugin_basename( __FILE__ ) ), dirname( plugin_basename( __FILE__ ) ) ); # eugh

$plugininfo = new PluginInfo();

?>