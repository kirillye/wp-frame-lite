<?php
/**
 * Базовая настройка темы.
 *
 * @package wp-frame-lite
 */

defined( 'ABSPATH' ) || exit;

/**
 * Регистрирует возможности темы: меню, логотип, миниатюры, поддержку блоков.
 */
function wp_frame_lite_setup(): void {
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );

	register_nav_menus(
		array(
			'menu-1' => 'Основное меню',
		)
	);

	add_theme_support( 'html5', array( 'gallery', 'caption', 'style', 'script' ) );

	// Работает в паре с layout.wideSize из theme.json.
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );

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

/**
 * Задаёт $content_width — от него WordPress считает размеры oEmbed.
 */
function wp_frame_lite_content_width(): void {
	// Ширина контента = --wpf-container-max (1140px) минус боковые отступы
	// --wpf-container-pad (2 × 1.5rem = 48px). Значение влияет на размеры oEmbed.
	$GLOBALS['content_width'] = apply_filters( 'wp_frame_lite_content_width', 1092 );
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
