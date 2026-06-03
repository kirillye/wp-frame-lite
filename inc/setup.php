<?php
/**
 * Базовая настройка темы.
 *
 * @package wp-frame-lite
 */

defined( 'ABSPATH' ) || exit;

function wp_frame_lite_setup(): void {
	load_theme_textdomain( 'wp-frame-lite', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );

	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Основное меню', 'wp-frame-lite' ),
		)
	);

	add_theme_support( 'html5', array( 'gallery', 'caption', 'style', 'script' ) );

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'wp_frame_lite_setup' );

function wp_frame_lite_content_width(): void {
	$GLOBALS['content_width'] = apply_filters( 'wp_frame_lite_content_width', 640 );
}
add_action( 'after_setup_theme', 'wp_frame_lite_content_width', 0 );

add_filter(
	'body_class',
	function ( array $classes ): array {
		if ( ! is_singular() ) {
			$classes[] = 'hfeed';
		}

		return $classes;
	}
);
