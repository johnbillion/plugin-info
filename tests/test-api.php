<?php

/**
 * w.org API test case to ensure the API format is as expected.
 */
class Test_API extends WP_UnitTestCase {

	function test_expected_api_format() {

		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$info   = array();
		$plugin = plugins_api( 'plugin_information', array(
			'slug' => 'plugin-info',
		) );

		$this->assertNotEmpty( $plugin );
		$this->assertNotWPError( $plugin );
		$this->assertInstanceOf( 'stdClass', $plugin );

		$expected = array(
			'name'           => 'string',
			'slug'           => 'string',
			'version'        => 'string',
			'author'         => 'string',
			'author_profile' => 'string',
			'contributors'   => 'array',
			'requires'       => 'string',
			'tested'         => 'string',
			'compatibility'  => 'array',
			'rating'         => 'float',
			'num_ratings'    => 'string',
			'ratings'        => 'array',
			'downloaded'     => 'int',
			'last_updated'   => 'string',
			'added'          => 'string',
			'homepage'       => 'string',
			'sections'       => 'array',
			'download_link'  => 'string',
			'tags'           => 'array',
			'donate_link'    => 'string',
		);

		$this->assertEquals( array_keys( $expected ), array_keys( get_object_vars( $plugin ) ) );

		foreach ( $expected as $name => $type ) {
			$this->assertInternalType( $type, $plugin->{$name}, "Expecting {$type} for {$name}." );
		}

		$sections = array(
			'description',
			'installation',
			'screenshots',
			'changelog',
			'faq',
			// 'other_notes',
		);

		$this->assertEquals( $sections, array_keys( $plugin->sections ) );

		foreach ( $sections as $name ) {
			$this->assertInternalType( 'string', $plugin->sections[ $name ], "Expecting string for {$name} section." );
		}

	}

}
