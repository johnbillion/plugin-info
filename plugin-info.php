<?php
/*
Plugin Name: Plugin Info
Description: Provides a simple way of displaying up-to-date information about specific WordPress Plugin Directory hosted plugins in your blog posts and pages.
Plugin URI:  http://lud.icro.us/wordpress-plugin-info/
Version:     0.3
License:     GNU General Public License
Author:      John Blackbourn
Author URI:  http://johnblackbourn.com/

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

Changelog:

0.3 - 2009/01/31
Implemented periodic updating of plugin information using WP-Cron.

0.2 - 2009/01/20
Additions and updates to several shortcode attributes.
    Mad props to: Matt Martz & Kim Parsell.

0.1 - 2008/12/16
Initial release.

*/

class PluginInfo {

	var $plugin;

	function PluginInfo() {

		add_action( 'admin_menu',         array( &$this, 'admin_menu' ) );
		add_action( 'save_post',          array( &$this, 'save_plugin_info' ) );
		add_action( 'save_page',          array( &$this, 'save_plugin_info' ) );
		add_action( 'update_plugin_info', array( &$this, 'update_plugin_info' ) );
		add_filter( 'ozh_adminmenu_icon', array( &$this, 'ozh_adminmenu_icon' ) );
		add_shortcode( 'plugin',          array( &$this, 'plugin_info_shortcode' ) );

		register_activation_hook( __FILE__, array( &$this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );

		$this->plugin = array(
			'url' => WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) ),
			'ver' => '0.3'
		);

	}

	function admin_menu() {
		add_options_page( 'Plugin Info Settings', 'Plugin Info', 'manage_options', 'plugin_info', array( &$this, 'settings' ) );
	}

	function get_plugin_info( $slug = null ) {

		if ( !$slug )
			return false;

		require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

		$slug   = sanitize_title( $slug );
		$plugin = plugins_api( 'plugin_information', array( 'slug' => $slug ) );
		$info   = array();

		/*echo '<pre>';
		print_r( $plugin );
		echo '</pre>';
		die();*/

		$attributes = array(
			'name'             => 'name',
			'slug'             => 'slug',
			'version'          => 'version',
			'author'           => 'author',
			'requires'         => 'requires',
			'tested'           => 'tested',
			'rating_raw'       => 'rating',
			'downloaded_raw'   => 'downloaded',
			'last_updated_raw' => 'last_updated',
			'num_ratings'      => 'num_ratings',
			'description'      => array( 'sections', 'description' ),
			'faq'              => array( 'sections', 'faq' ),
			'installation'     => array( 'sections', 'installation' ),
			#'screenshots'      => array( 'sections', 'screenshots' ), # awaiting API support
			#'other_notes'      => array( 'sections', 'other_notes' ), # awaiting API support
			'download_url'     => 'download_link',
			'homepage_url'     => 'homepage',
			'tags'             => 'tags'
		);

		foreach ( $attributes as $name => $key ) {
		
			if ( is_array( $key ) ) {
				$_key = $plugin->$key[0];
				$info[$name] = $_key[$key[1]];
			} else {
				$info[$name] = $plugin->$key;
			}

			if ( is_array( $info[$name] ) )
				$info[$name] = implode( ', ', $info[$name] );

		}

		$info['downloaded']       = number_format( $info['downloaded_raw'] );
		$info['rating']           = ceil( 0.05 * $info['rating_raw'] );
		$info['link_url']         = "http://wordpress.org/extend/plugins/{$info['slug']}/";
		$info['last_updated']     = date( get_option('date_format'), strtotime( $info['last_updated_raw'] ) );
		$info['last_updated_ago'] = sprintf( __('%s ago'), human_time_diff( strtotime( $info['last_updated_raw'] ) ) );
		$info['download']         = '<a href="' . $info['download_url'] . '">%s</a>';
		$info['homepage']         = '<a href="' . $info['homepage_url'] . '">%s</a>';
		$info['link']             = '<a href="' . $info['link_url']   . '">%s</a>';
		#$info['screenshots']      = preg_replace( "/src='([^\']+)'/i","src='{$info['link_url']}$1'", $info['screenshots'] ); # awaiting API support

		if ( preg_match( '/href="([^"]+)"/i', $info['author'], $matches ) )
			$info['author_url'] = $matches[1];

		if ( preg_match( '/>([^<]+)</i', $info['author'], $matches ) )
			$info['author_name'] = $matches[1];
		else
			$info['author_name'] = $info['author'];

		# The following values are *deprecated* but remain for those who may be using them:
		$info['download_link'] = $info['download_url']; # use download_url instead
		$info['tags_list']     = $info['tags'];         # use tags instead
		$info['extend']        = $info['link_url'];     # use link_url instead

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
			'meta_key'  => 'plugin',
			'post_type' => 'any'
		) );

		if ( !count( $posts ) )
			return;

		foreach ( $posts as $p ) {

			$info = $this->get_plugin_info( stripslashes( $p['meta_value'] ) );
			update_post_meta( $p->ID, 'plugin-info', $info );

		}

	}

	function save_plugin_info( $post_ID ) {

		if ( !isset( $_POST['meta'] ) or !is_array( $_POST['meta'] ) )
			return;

		foreach ( $_POST['meta'] as $meta ) {

			if ( $meta['key'] != 'plugin' )
				continue;

			$info = $this->get_plugin_info( stripslashes( $meta['value'] ) );

			if ( !update_post_meta( $post_ID, 'plugin-info', $info ) )
				add_post_meta( $post_ID, 'plugin-info', $info );

			return true;

		}

	}

	function plugin_info_shortcode( $atts ) {

		global $post;

		$atts = shortcode_atts( array(
			0      => 'name',
			'text' => ''
		), $atts );

		$meta = get_post_meta( $post->ID, 'plugin-info', true );

		if ( false !== strpos( $meta[$atts[0]], '%s' ) ) {

			$texts = array(
				'download' => __( 'Download' ),
				'homepage' => __( 'Visit plugin homepage' ),
				'link'     => $meta['name']
			);

			$text = ( $atts['text'] ) ? $atts['text'] : $texts[$atts[0]];
			$meta[$atts[0]] = str_replace( '%s', $text, $meta[$atts[0]] );

		}

		return $meta[$atts[0]];

	}

	function settings() {
		?>

	<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2><?php _e( 'Plugin Info Settings', 'plugin_info' ); ?></h2>

	<form method="post" action="options.php">
	<?php wp_nonce_field( 'update-options' ); ?>
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="plugin_info_shortcode,plugin_info_filter" />

	<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e( '1', 'plugin_info' ); ?></th>
		<td>2</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( '3', 'plugin_info' ); ?></th>
		<td>4</td>
	</tr>
	</table>

	<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
	</form>

	</div>

	<?php
	}

	function ozh_adminmenu_icon( $hook ) {
		if ( $hook == 'plugin_info' )
			return $this->plugin['url'] . '/icon.png';
		return $hook;
	}

	function activate() {
		wp_schedule_event( ( time() + 86400 ), 'daily', 'update_plugin_info' );
	}

	function deactivate() {
		wp_clear_scheduled_hook( 'update_plugin_info' );
	}

}

if ( !defined( 'WP_CONTENT_URL' ) )
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( !defined( 'WP_CONTENT_DIR' ) )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( !defined( 'WP_PLUGIN_URL' ) )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( !defined( 'WP_PLUGIN_DIR' ) )
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

load_plugin_textdomain( 'plugin_info', PLUGINDIR . '/' . dirname( plugin_basename( __FILE__ ) ), dirname( plugin_basename( __FILE__ ) ) ); # eugh

$plugininfo = new PluginInfo();

?>