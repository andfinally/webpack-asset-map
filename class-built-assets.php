<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', array( 'Built_Assets', 'init' ) );

/**
 * Class Built_Assets
 *
 * Finds the correct filename when you want to enqueue assets
 * from a webpack build whose filenames contain content hashes.
 * Use with webpack-plugin-outputmap, which generates a
 * map.json file with the filenames in the dist folder.
 */
class Built_Assets {

	public static $asset_map = array();
	const ASSET_MAP_TRANSIENT = 'asset-map';
	const ASSET_MAP_MODIFIED_TIME_TRANSIENT = 'asset-map-modified-time';

	/**
	 * Check a transient for the modified time of the last loaded asset map.
	 * If webpack has created a more recent asset map file, load it and
	 * update the transient.
	 */
	static function init() {
		$asset_map_modified_time = filemtime( get_stylesheet_directory() . '/dist/map.json' );
		$stored_modified_time = get_transient( self::ASSET_MAP_MODIFIED_TIME_TRANSIENT );
		if ( empty( $stored_modified_time ) || $asset_map_modified_time > $stored_modified_time ) {
			self::$asset_map = self::load_asset_map();
			if ( ! empty( self::$asset_map ) ) {
				set_transient( self::ASSET_MAP_MODIFIED_TIME_TRANSIENT, $asset_map_modified_time, WEEK_IN_SECONDS );
			}
		}

		if ( empty( self::$asset_map ) ) {
			self::get_asset_map();
		}
	}

	/**
	 * Load the map.json file from the dist folder, read its contents into an array,
	 * save the array as a transient, and return it.
	 *
	 * @return array|mixed Array normal and hashed filenames
	 */
	static function load_asset_map() {
		$asset_map_json = file_get_contents( get_stylesheet_directory() . '/dist/map.json' );
		if ( ! $asset_map_json ) {
			return array();
		}
		$asset_map = json_decode( $asset_map_json, true );
		set_transient( self::ASSET_MAP_TRANSIENT, $asset_map, WEEK_IN_SECONDS );

		return $asset_map;
	}

	/**
	 * Try to get the asset map from a transient,
	 * or load it from the map.json file.
	 */
	static function get_asset_map() {
		self::$asset_map = get_transient( self::ASSET_MAP_TRANSIENT );
		if ( empty( self::$asset_map ) ) {
			self::$asset_map = self::load_asset_map();
		}
	}

	/**
	 * Given a filename like bundle.min.js, return the filename with a content hash generated
	 * by webpack, like bundle.06586b0506b8484b7358.min.js.
	 *
	 * @param $filename
	 *
	 * @return mixed
	 */
	static function get_hashed_filename( $filename ) {
		if ( ! empty( self::$asset_map[ $filename ] ) ) {
			return self::$asset_map[ $filename ];
		}

		return $filename;
	}

}
