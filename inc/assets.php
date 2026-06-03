<?php
/**
 * Подключение стилей и скриптов темы.
 *
 * @package wp-frame-lite
 */

defined( 'ABSPATH' ) || exit;

function wp_frame_lite_scripts(): void {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();

	$css = '/assets/dist/css/main.css';
	$js  = '/assets/dist/js/main.js';

	wp_enqueue_style(
		'wp-frame-lite-style',
		$uri . $css,
		array(),
		file_exists( $dir . $css ) ? (string) filemtime( $dir . $css ) : wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_script(
		'wp-frame-lite-main',
		$uri . $js,
		array(),
		file_exists( $dir . $js ) ? (string) filemtime( $dir . $js ) : wp_get_theme()->get( 'Version' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'wp_frame_lite_scripts' );
