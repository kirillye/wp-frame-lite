<?php
/**
 * Очистка лишних возможностей WordPress.
 *
 * @package wp-frame-lite
 */

defined( 'ABSPATH' ) || exit;

// ── Emoji ─────────────────────────────────────────────────────────────────────

remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

/**
 * Убирает плагин эмодзи из TinyMCE.
 *
 * @param array<int, string> $plugins Подключённые плагины редактора.
 * @return array<int, string>
 */
function wp_frame_lite_remove_emoji_tinymce_plugin( array $plugins ): array {
	return array_diff( $plugins, array( 'wpemoji' ) );
}
add_filter( 'tiny_mce_plugins', 'wp_frame_lite_remove_emoji_tinymce_plugin' );

// ── Мета-теги и служебные эндпоинты ───────────────────────────────────────────

remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );

add_filter( 'xmlrpc_enabled', '__return_false' );

remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'wp_oembed_add_host_js' );
add_filter( 'embed_oembed_discover', '__return_false' );

// ── Поиск ─────────────────────────────────────────────────────────────────────
// Тема поиск не поддерживает: форма всегда пустая, поисковые запросы отдают 404.

add_filter( 'get_search_form', '__return_empty_string' );

/**
 * Превращает поисковый запрос в 404: поиска в теме нет.
 *
 * @param WP_Query $query Разбираемый запрос.
 */
function wp_frame_lite_disable_search_query( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$query->set( 's', '' );
	$query->set_404();

	status_header( 404 );
	nocache_headers();
}
add_action( 'parse_query', 'wp_frame_lite_disable_search_query' );
