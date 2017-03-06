<?php

/**
 * Plugin info test case.
 */
class Test_Info extends WP_UnitTestCase {

	function test_expected_plugin_format() {

		global $plugininfo;

		$info = $plugininfo->get_plugin_info( 'plugin-info' );

		$this->assertNotEmpty( $info );
		$this->assertInternalType( 'array', $info );

		$expected = array(
			'name'              => 'Plugin Info',
			'slug'              => 'plugin-info',
			'version'           => null,
			'author'            => '<a href="https://johnblackbourn.com/">John Blackbourn</a>',
			'profile_url'       => 'https://profiles.wordpress.org/johnbillion',
			'contributors'      => null,
			'requires'          => null,
			'tested'            => null,
			'compatibility'     => null,
			'rating_raw'        => null,
			'num_ratings'       => null,
			'downloaded_raw'    => null,
			'updated_raw'       => null,
			'homepage_url'      => 'https://lud.icro.us/wordpress-plugin-info/',
			'description'       => null,
			'installation'      => null,
			'screenshots'       => null,
			'changelog'         => null,
			'faq'               => null,
			// 'other_notes' => null,
			'download_url'      => null,
			'donate_url'        => null,
			'tags'              => null,
			'compat_with'       => null,
			'downloaded'        => null,
			'rating'            => null,
			'link_url'          => 'https://wordpress.org/plugins/plugin-info/',
			'updated'           => null,
			'updated_ago'       => null,
			'download'          => null,
			'homepage'          => '<a href="https://lud.icro.us/wordpress-plugin-info/">%s</a>',
			'link'              => '<a href="https://wordpress.org/plugins/plugin-info/">%s</a>',
			'profile'           => '<a href="https://profiles.wordpress.org/johnbillion">%s</a>',
			// 'donate'            => null,
			'author_url'        => null,
			'author_name'       => null,
			'latest_change'     => null,
			'download_link'     => null,
			'tags_list'         => null,
			'extend'            => null,
			'last_updated_nice' => null,
			'last_updated'      => null,
			'last_updated_ago'  => null,
			'last_updated_raw'  => null,
		);

		$this->assertEquals( array_keys( $expected ), array_keys( $info ) );

		foreach ( $expected as $name => $value ) {
			if ( null === $value ) {
				continue;
			}

			$this->assertSame( $value, $info[ $name ], "Expecting '{$value}' for {$name}." );

		}

	}

}
