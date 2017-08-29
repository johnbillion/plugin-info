<?php

/**
 * w.org API test case to ensure the API format is as expected.
 */
class Test_API extends WP_UnitTestCase {

	protected static $plugin_info = null;

	public static function setUpBeforeClass() {
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		self::$plugin_info = plugins_api( 'plugin_information', array(
			'slug' => 'plugin-info',
		) );

		parent::setUpBeforeClass();
	}

	public function setUp() {
		parent::setUp();

		$this->assertNotEmpty( self::$plugin_info );
		$this->assertNotWPError( self::$plugin_info );
		$this->assertInstanceOf( 'stdClass', self::$plugin_info );
	}

	public function test_expected_api_format() {
		$info = array();

		$expected = array(
			'added'                    => 'string',
			'author'                   => 'string',
			'author_profile'           => 'string',
			'compatibility'            => 'array',
			'contributors'             => 'array',
			'donate_link'              => 'string',
			'download_link'            => 'string',
			'downloaded'               => 'int',
			'homepage'                 => 'string',
			'last_updated'             => 'string',
			'name'                     => 'string',
			'num_ratings'              => 'int',
			'rating'                   => 'int',
			'ratings'                  => 'array',
			'requires'                 => 'string',
			'screenshots'              => 'array',
			'sections'                 => 'array',
			'slug'                     => 'string',
			'support_threads'          => 'int',
			'support_threads_resolved' => 'int',
			'tags'                     => 'array',
			'tested'                   => 'string',
			'version'                  => 'string',
			'versions'                 => 'array',
		);
		$actual = array_keys( get_object_vars( self::$plugin_info ) );
		sort( $actual );

		$this->assertEquals( array_keys( $expected ), $actual );

		foreach ( $expected as $name => $type ) {
			$this->assertInternalType( $type, self::$plugin_info->{$name}, "Expecting {$type} for {$name}." );
		}
	}

	public function test_expected_api_sections() {
		$this->assertObjectHasAttribute( 'sections', self::$plugin_info );

		$expected = array(
			'changelog'    => 'string',
			'description'  => 'string',
			'faq'          => 'string',
			'installation' => 'string',
			// 'other_notes' => 'string',
			'screenshots'  => 'string',
		);
		$actual = array_keys( self::$plugin_info->sections );
		sort( $actual );

		$this->assertEquals( array_keys( $expected ), $actual );

		foreach ( $expected as $name => $type ) {
			$this->assertInternalType( $type, self::$plugin_info->sections[ $name ], "Expecting {$type} for {$name} section." );
		}
	}

}
