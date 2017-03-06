<?php
/*
Plugin Name:  Plugin Info
Description:  Provides a simple way of displaying up-to-date information about specific WordPress Plugin Directory hosted plugins in your blog posts and pages.
Plugin URI:   https://lud.icro.us/wordpress-plugin-info/
Version:      0.8.2
Author:       John Blackbourn
Author URI:   https://johnblackbourn.com/
Text Domain:  plugin-info
Domain Path:  /languages/
License:      GPL v2 or later

Copyright © 2013 John Blackbourn

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

class PluginInfo {

	public $plugin;
	public $meta;

	public function __construct() {

		add_action( 'init',               array( $this, 'action_init' ) );
		add_action( 'admin_menu',         array( $this, 'action_admin_menu' ) );
		add_action( 'admin_head',         array( $this, 'action_admin_head' ) );
		add_action( 'save_post',          array( $this, 'action_save_post' ) );
		add_action( 'update_plugin_info', array( $this, 'action_update_plugin_info' ) );
		add_action( 'admin_init',         array( $this, 'action_admin_init' ) );

		add_shortcode( 'plugin',          array( $this, 'shortcode_plugin_info' ) );

		$this->plugin = array(
			'url' => plugin_dir_url( __FILE__ ),
			'dir' => plugin_dir_path( __FILE__ ),
		);

	}

	public function action_init() {
		load_plugin_textdomain( 'plugin-info', false, dirname( plugin_basename( __FILE__ ) ) );
	}

	public function get_plugin_info( $slug = null ) {

		if ( !$slug )
			return false;

		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$info   = array();
		$slug   = sanitize_title( $slug );
		$plugin = plugins_api( 'plugin_information', array( 'slug' => $slug ) );

		if ( !$plugin or is_wp_error( $plugin ) )
			return false;

		$attributes = array(
			'name'           => 'name',
			'slug'           => 'slug',
			'version'        => 'version',
			'author'         => 'author',
			'profile_url'    => 'author_profile',
			'contributors'   => 'contributors',
			'requires'       => 'requires',
			'tested'         => 'tested',
			'compatibility'  => 'compatibility',
			'rating_raw'     => 'rating',
			'num_ratings'    => 'num_ratings',
			'downloaded_raw' => 'downloaded',
			'updated_raw'    => 'last_updated',
			'homepage_url'   => 'homepage',
			'description'    => array( 'sections', 'description' ),
			'installation'   => array( 'sections', 'installation' ),
			'screenshots'    => array( 'sections', 'screenshots' ),
			'changelog'      => array( 'sections', 'changelog' ),
			'faq'            => array( 'sections', 'faq' ),
			'other_notes'    => array( 'sections', 'other_notes' ),
			'download_url'   => 'download_link',
			'donate_url'     => 'donate_link',
			'tags'           => 'tags',
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

		}

		if ( is_array( $info['compatibility'] ) and !empty( $info['compatibility'][$GLOBALS['wp_version']] ) )
			$info['compatibility'] = $info['compatibility'][$GLOBALS['wp_version']][$info['version']][0] . '%';
		else
			$info['compatibility'] = __( 'Unknown', 'plugin-info' );

		$info['compat_with'] = $GLOBALS['wp_version'];
		$info['downloaded']  = number_format_i18n( $info['downloaded_raw'] );
		$info['rating']      = ceil( 0.05 * $info['rating_raw'] );
		$info['link_url']    = "https://wordpress.org/plugins/{$info['slug']}/";
		$info['updated']     = date_i18n( get_option('date_format'), strtotime( $info['updated_raw'] ) );
		$info['updated_ago'] = sprintf( __( '%s ago', 'plugin-info' ), human_time_diff( strtotime( $info['updated_raw'] ) ) );
		$info['download']    = '<a href="' . $info['download_url'] . '">%s</a>';
		$info['homepage']    = '<a href="' . $info['homepage_url'] . '">%s</a>';
		$info['link']        = '<a href="' . $info['link_url']     . '">%s</a>';
		$info['profile']     = '<a href="' . $info['profile_url']  . '">%s</a>';

		if ( isset( $info['donate_url'] ) )
			$info['donate'] = '<a href="' . $info['donate_url'] . '">%s</a>';

		if ( ! empty( $info['contributors'] ) ) {
			foreach ( (array) $info['contributors'] as $name => $link )
				$info['contributors'][$name] = '<a href="' . $link . '">' . $name . '</a>';
			$info['contributors'] = implode( ', ', $info['contributors'] );
		}

		if ( ! empty( $info['tags'] ) )
			$info['tags'] = implode( ', ', (array) $info['tags'] );
		else
			$info['tags'] = '';

		if ( preg_match( '|href="([^"]+)"|i', $info['author'], $matches ) )
			$info['author_url'] = $matches[1];

		if ( preg_match( '|>([^<]+)<|i', $info['author'], $matches ) )
			$info['author_name'] = $matches[1];
		else
			$info['author_name'] = $info['author'];

		if ( ! empty( $info['changelog'] ) and preg_match( "#<h4>{$info['version']}[^<]*</h4>(.*?)(<h4>|$)#is", $info['changelog'], $matches ) )
			$info['latest_change'] = trim( $matches[1] );

		if ( ! empty( $info['other_notes'] ) and preg_match_all( '|<h3>([^<]+)</h3>|i', $info['other_notes'], $matches, PREG_SET_ORDER ) ) {
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
		 * function myfunction( $info, $slug, $plugin ) {
		 * 	$info['fullname'] = $info['name'] . ' v' . $info['version'];
		 * 	return $info;
		 * }
		 * add_filter( 'plugin_info', 'myfunction', 10, 3 );
		 *
		 * The above code would create a `[plugin fullname]` shortcode which
		 * would return something like `My Wonderful Plugin v1.0`
		 *
		 * Example 2:
		 *
		 * function myfunction( $info, $slug, $plugin ) {
		 * 	$info['requires'] = 'Requires at least WordPress version ' . $info['requires'];
		 * 	return $info;
		 * }
		 * add_filter( 'plugin_info', 'myfunction', 10, 3 );
		 *
		 * The above would modify the `[plugin requires]` shortcode so it returns
		 * a full sentence explaining the minimum WP version requirement.
		 *
		 */

		return apply_filters( 'plugin_info', $info, $slug, $plugin );

	}

	public function action_update_plugin_info() {

		$q = new WP_Query;

		$posts = $q->query( array(
			'posts_per_page' => -1,
			'meta_key'       => 'plugin',
			'post_type'      => 'any'
		) );

		if ( !count( $posts ) )
			return;

		foreach ( $posts as $p ) {
			$plugin_info = $this->get_plugin_info( get_post_meta( $p->ID, 'plugin', true ) );
			if ( $plugin_info )
				update_post_meta( $p->ID, 'plugin-info', $plugin_info );
		}

	}

	public function action_save_post( $post_ID ) {

		if ( wp_is_post_revision( $post_ID ) or wp_is_post_autosave( $post_ID ) )
			return;

		if ( !isset( $_POST['plugin_info'] ) )
			return;

		if ( empty( $_POST['plugin_info'] ) ) {

			delete_post_meta( $post_ID, 'plugin' );
			delete_post_meta( $post_ID, 'plugin-info' );

		} else {

			$plugin = trim( stripslashes( $_POST['plugin_info'] ) );
			$plugin_info = $this->get_plugin_info( $plugin );

			if ( !$plugin_info )
				return false; # @TODO: display error msg?

			update_post_meta( $post_ID, 'plugin', $plugin );
			update_post_meta( $post_ID, 'plugin-info', $plugin_info );

		}

		return;

	}

	public function shortcode_plugin_info( $atts ) {

		global $post;

		$atts = shortcode_atts( array(
			0      => 'name',
			'text' => ''
		), $atts, 'plugin' );

		$att = $atts[0];
		$key = $post->ID;

		if ( empty( $this->meta[$key] ) )
			$this->meta[$key] = get_post_meta( $post->ID, 'plugin-info', true );

		if ( !isset( $this->meta[$key][$att] ) )
			return '';

		if ( false !== strpos( $this->meta[$key][$att], '%s' ) ) {

			$texts = array(
				'download' => __( 'Download', 'plugin-info' ),
				'homepage' => __( 'Visit plugin homepage', 'plugin-info' ),
				'donate'   => __( 'Donate', 'plugin-info' ),
				'link'     => $this->meta[$key]['name'],
				'profile'  => $this->meta[$key]['author_name']
			);

			$text = ( $atts['text'] ) ? $atts['text'] : $texts[$att];
			$this->meta[$key][$att] = str_replace( '%s', $text, $this->meta[$key][$att] );

		}

		/*
		 * The `plugin_info_shortcode` filter below allows a plugin/theme
		 * to format or otherwise modify the output of the shortcode.
		 *
		 * Example:
		 *
		 * function myfunction( $output, $attribute, $slug ) {
		 * 	if ( 'screenshots' == $attribute ) {
		 *   $output = str_replace( array( '<ol', '</ol' ), array( '<ul', '</ul' ), $output );
		 *  }
		 * 	return $output;
		 * }
		 * add_filter( 'plugin_info_shortcode', 'myfunction', 10, 3 );
		 *
		 * The above would modify the 'screenshots' output so the screenhots are
		 * displayed in an unordered list instead of an ordered list.
		 *
		 */

		return apply_filters( 'plugin_info_shortcode', $this->meta[$key][$att], $att, $this->meta[$key]['slug'] );

	}

	public function action_admin_head() {
		if ( self::is_post_writing_screen() ) {
		?>
		<script type="text/javascript">

			jQuery(function($) {

				$('#plugin_info_shortcodes').hide();
				$('#plugin_info_show_shortcodes').show().click(function(){
					$('#plugin_info_shortcodes').toggle();
					text = $('#plugin_info_shortcodes').is(':visible') ? '<?php esc_js( _e( '[ hide ]', 'plugin-info' ) ); ?>' : '<?php esc_js( _e( '[ show ]', 'plugin-info' ) ); ?>';
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

		</script>
		<style type="text/css">

			#plugin_info {
				width: 98%;
				margin-top: 5px
			}

			#plugin_info_shortcodes dl {
				margin: 5px 5px 10px;
				overflow: auto;
				font-size: 0.9em;
				border-bottom: 1px solid #dfdfdf;
				padding-bottom: 8px;
			}

			#plugin_info_shortcodes dt {
				float: left;
				clear: left;
				width: 52%;
				margin: 0 1% 5px 0;
				cursor: pointer;
			}

			#plugin_info_shortcodes dt:hover {
				color: #D54E21;
			}

			#plugin_info_shortcodes dd {
				float: left;
				width: 47%;
				margin: 0 0 5px 0;
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

	public function meta_box( $post ) {
		?>
		<label for="plugin_info"><?php _e( 'Plugin slug:', 'plugin-info' ); ?></label>
		<input type="text" name="plugin_info" id="plugin_info" value="<?php esc_attr_e( get_post_meta( $post->ID, 'plugin', true ) ); ?>" />
		<p class="howto"><?php _e( 'To display information about a plugin, you should use one of the shortcodes below.', 'plugin-info' ); ?></p>
		<div id="plugin_info_shortcodes">
			<p><?php _e( 'Plain info:', 'plugin-info' ); ?></p>
			<dl>
				<dt>[plugin author_name]</dt>
				<dd class="howto"><?php _e( 'Author&rsquo;s name', 'plugin-info' ); ?></dd>
				<dt>[plugin author_url]</dt>
				<dd class="howto"><?php _e( 'Author&rsquo;s URL', 'plugin-info' ); ?></dd>
				<dt>[plugin compatibility]</dt>
				<dd class="howto"><?php _e( 'Concensus on compatibility with latest WP version (% of people who say it works)', 'plugin-info' ); ?></dd>
				<dt>[plugin compat_with]</dt>
				<dd class="howto"><?php _e( 'Version of WordPress used for the compatibility concensus', 'plugin-info' ); ?></dd>
				<dt>[plugin download_url]</dt>
				<dd class="howto"><?php _e( 'URL of ZIP file', 'plugin-info' ); ?></dd>
				<dt>[plugin downloaded]</dt>
				<dd class="howto"><?php _e( 'Download count', 'plugin-info' ); ?></dd>
				<dt>[plugin homepage_url]</dt>
				<dd class="howto"><?php _e( 'URL of homepage', 'plugin-info' ); ?></dd>
				<dt>[plugin donate_url]</dt>
				<dd class="howto"><?php _e( 'URL of donations page', 'plugin-info' ); ?></dd>
				<dt>[plugin link_url]</dt>
				<dd class="howto"><?php _e( 'URL of wp.org page', 'plugin-info' ); ?></dd>
				<dt>[plugin name]</dt>
				<dd class="howto"><?php _e( 'Name', 'plugin-info' ); ?></dd>
				<dt>[plugin profile_url]</dt>
				<dd class="howto"><?php _e( 'URL of author&rsquo;s wp.org profile', 'plugin-info' ); ?></dd>
				<dt>[plugin requires]</dt>
				<dd class="howto"><?php _e( '&rsquo;Requires at least&lsquo; version number', 'plugin-info' ); ?></dd>
				<dt>[plugin rating]</dt>
				<dd class="howto"><?php _e( 'Rating out of 5', 'plugin-info' ); ?></dd>
				<dt>[plugin slug]</dt>
				<dd class="howto"><?php _e( 'Slug', 'plugin-info' ); ?></dd>
				<dt>[plugin tags]</dt>
				<dd class="howto"><?php _e( 'List of tags', 'plugin-info' ); ?></dd>
				<dt>[plugin tested]</dt>
				<dd class="howto"><?php _e( '&rsquo;Tested up to&lsquo; version number', 'plugin-info' ); ?></dd>
				<dt>[plugin updated_ago]</dt>
				<dd class="howto"><?php _e( 'Last updated ago (hours/days/weeks)', 'plugin-info' ); ?></dd>
				<dt>[plugin updated]</dt>
				<dd class="howto"><?php _e( 'Last updated date', 'plugin-info' ); ?></dd>
				<dt>[plugin version]</dt>
				<dd class="howto"><?php _e( 'Version number', 'plugin-info' ); ?></dd>
			</dl>
			<p><?php _e( 'Formatted info:', 'plugin-info' ); ?></p>
			<dl>
				<dt>[plugin author]</dt>
				<dd class="howto"><?php _e( 'Link to author&rsquo;s homepage', 'plugin-info' ); ?></dd>
				<dt>[plugin contributors]</dt>
				<dd class="howto"><?php _e( 'List of contributors', 'plugin-info' ); ?></dd>
				<dt>[plugin description]</dt>
				<dd class="howto"><?php _e( 'Long description', 'plugin-info' ); ?></dd>
				<dt>[plugin installation]</dt>
				<dd class="howto"><?php _e( 'Installation directions', 'plugin-info' ); ?></dd>
				<dt>[plugin faq]</dt>
				<dd class="howto"><?php _e( 'List of FAQs', 'plugin-info' ); ?></dd>
				<dt>[plugin download]</dt>
				<dd class="howto"><?php _e( 'Link to ZIP file', 'plugin-info' ); ?></dd>
				<dt>[plugin homepage]</dt>
				<dd class="howto"><?php _e( 'Link to homepage', 'plugin-info' ); ?></dd>
				<dt>[plugin donate]</dt>
				<dd class="howto"><?php _e( 'Link to donations page', 'plugin-info' ); ?></dd>
				<dt>[plugin link]</dt>
				<dd class="howto"><?php _e( 'Link to wp.org page', 'plugin-info' ); ?></dd>
				<dt>[plugin profile]</dt>
				<dd class="howto"><?php _e( 'Link to author&rsquo;s wp.org profile', 'plugin-info' ); ?></dd>
				<dt>[plugin screenshots]</dt>
				<dd class="howto"><?php _e( 'List of screenshots', 'plugin-info' ); ?></dd>
				<dt>[plugin changelog]</dt>
				<dd class="howto"><?php _e( 'List of changelog entries', 'plugin-info' ); ?></dd>
				<dt>[plugin latest_change]</dt>
				<dd class="howto"><?php _e( 'Latest changelog entry', 'plugin-info' ); ?></dd>
				<dt>[plugin other_notes]</dt>
				<dd class="howto"><?php _e( 'Other notes', 'plugin-info' ); ?></dd>
			</dl>
		</div>
		<p><a href="#" id="plugin_info_show_shortcodes"><?php _e( '[ show ]', 'plugin-info' ); ?></a></p>
		<?php
	}

	public function action_admin_menu() {
		add_meta_box(
			'plugininfo',
			__( 'Plugin Info', 'plugin-info' ),
			array( $this, 'meta_box' ),
			'post',
			'side'
		);
		add_meta_box(
			'plugininfo',
			__( 'Plugin Info', 'plugin-info' ),
			array( $this, 'meta_box' ),
			'page',
			'side'
		);
	}

	public static function is_post_writing_screen() {
		foreach ( array( 'post.php', 'post-new.php', 'page.php', 'page-new.php' ) as $file )
			if ( strpos( $_SERVER['REQUEST_URI'], $file ) )
				return true;
		return false;
	}

	public function action_admin_init() {
		if ( !wp_next_scheduled( 'update_plugin_info' ) )
			wp_schedule_event( time(), 'hourly', 'update_plugin_info' );
	}

}

function get_plugin_info( $slug, $attribute = 'version' ) {

	global $plugininfo;

	$slug = sanitize_title( $slug );

	if ( empty( $plugininfo->meta[$slug] ) )
		$plugininfo->meta[$slug] = $plugininfo->get_plugin_info( $slug );

	if ( isset( $plugininfo->meta[$slug][$attribute] ) )
		return $plugininfo->meta[$slug][$attribute];
	else
		return false;

}

function plugin_info( $slug, $attribute = 'version' ) {
	echo get_plugin_info( $slug, $attribute );
}

global $plugin_info;

$plugininfo = new PluginInfo;
