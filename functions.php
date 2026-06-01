<?php
/**
 * wp-frame-lite functions and definitions
 *
 * @package wp-frame-lite
 */

function wp_frame_lite_setup() {
	load_theme_textdomain( 'wp-frame-lite', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );

	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Основное меню', 'wp-frame-lite' ),
		)
	);

	add_theme_support(
		'html5',
		array(
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

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
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function wp_frame_lite_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'wp_frame_lite_content_width', 640 );
}
add_action( 'after_setup_theme', 'wp_frame_lite_content_width', 0 );

/**
 * Enqueue scripts and styles.
 */
function wp_frame_lite_scripts() {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();

	$css = '/assets/dist/css/main.css';
	$js  = '/assets/dist/js/main.js';

	wp_enqueue_style(
		'wp-frame-lite-style',
		$uri . $css,
		array(),
		file_exists( $dir . $css ) ? filemtime( $dir . $css ) : null
	);

	wp_enqueue_script(
		'wp-frame-lite-main',
		$uri . $js,
		array(),
		file_exists( $dir . $js ) ? filemtime( $dir . $js ) : null,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'wp_frame_lite_scripts' );

// Отключение поиска.
add_filter( 'get_search_form', '__return_empty_string' );
add_action( 'parse_query', function( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
		wp_redirect( home_url( '/' ), 301 );
		exit;
	}
} );

require get_template_directory() . '/inc/login.php';
require get_template_directory() . '/inc/admin.php';
require get_template_directory() . '/inc/security.php';

add_filter( 'body_class', function( $classes ) {
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}
	return $classes;
} );

/**
 * Отключение комментариев на всём сайте.
 */
add_filter( 'comments_open', '__return_false', 20, 2 );
add_filter( 'pings_open', '__return_false', 20, 2 );
add_filter( 'comments_array', '__return_empty_array', 10, 2 );

add_action( 'init', function() {
	foreach ( get_post_types() as $post_type ) {
		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}
}, 100 );

add_action( 'admin_menu', function() {
	remove_menu_page( 'edit-comments.php' );
} );

add_action( 'admin_bar_menu', function( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'comments' );
}, 999 );

add_action( 'template_redirect', function() {
	if ( is_comment_feed() ) {
		wp_die( esc_html__( 'Комментарии отключены.', 'wp-frame-lite' ), '', array( 'response' => 403 ) );
	}
} );

// Отключение Emoji — убирает лишний JS и DNS prefetch.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
add_filter( 'tiny_mce_plugins', function( $plugins ) {
	return array_diff( $plugins, array( 'wpemoji' ) );
} );

// Очистка <head> — убираем версию WP, wlwmanifest и rsd_link.
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );

// Отключение XML-RPC.
add_filter( 'xmlrpc_enabled', '__return_false' );

// Отключение oEmbed.
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'wp_oembed_add_host_js' );
add_filter( 'embed_oembed_discover', '__return_false' );

