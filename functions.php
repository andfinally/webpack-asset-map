<?php

require_once 'class-built-assets.php';

add_action( 'wp_enqueue_scripts', 'enqueue_assets' );

function enqueue_assets() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style(
		'demo-style',
		get_stylesheet_directory_uri() . '/dist/' . Built_Assets::get_hashed_filename( 'bundle.min.css' )
	);
	wp_enqueue_script(
		'demo-script',
		get_stylesheet_directory_uri() . '/dist/' . Built_Assets::get_hashed_filename( 'bundle.min.js' )
	);
}
