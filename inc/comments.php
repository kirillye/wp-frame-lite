<?php
/**
 * Полное отключение комментариев, пингбеков и трекбеков.
 *
 * Тема не поддерживает комментарии ни в каком виде: закрыты фронт, приём POST,
 * REST-эндпоинты, фиды и весь связанный интерфейс админки.
 *
 * Все обработчики именованные — если на конкретном проекте комментарии всё же
 * понадобятся, ненужное снимается через remove_action/remove_filter.
 *
 * @package wp-frame-lite
 */

defined( 'ABSPATH' ) || exit;

// ── Фронтенд ──────────────────────────────────────────────────────────────────

add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );
add_filter( 'get_comments_number', '__return_zero', 20 );

/**
 * Подменяет сохранённую настройку: новые записи создаются с закрытыми комментариями.
 */
function wp_frame_lite_force_comments_closed(): string {
	return 'closed';
}
add_filter( 'pre_option_default_comment_status', 'wp_frame_lite_force_comments_closed' );

/**
 * То же самое для пингбеков.
 */
function wp_frame_lite_force_pings_closed(): string {
	return 'closed';
}
add_filter( 'pre_option_default_ping_status', 'wp_frame_lite_force_pings_closed' );

/**
 * Убирает поддержку комментариев у всех типов записей.
 */
function wp_frame_lite_remove_comment_support(): void {
	foreach ( get_post_types() as $post_type ) {
		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}
}
add_action( 'init', 'wp_frame_lite_remove_comment_support', 100 );

// ── Приём комментариев ────────────────────────────────────────────────────────

/**
 * Закрывает wp-comments-post.php.
 */
function wp_frame_lite_block_comment_post(): void {
	wp_die( 'Комментарии отключены.', '', array( 'response' => 403 ) );
}
add_action( 'pre_comment_on_post', 'wp_frame_lite_block_comment_post' );

/**
 * Закрывает любой другой путь до wp_new_comment(): REST, XML-RPC, сторонний код.
 */
function wp_frame_lite_block_comment_data(): array {
	wp_die( 'Комментарии отключены.', '', array( 'response' => 403 ) );
}
add_filter( 'preprocess_comment', 'wp_frame_lite_block_comment_data' );

// ── REST API ──────────────────────────────────────────────────────────────────

/**
 * Снимает с регистрации маршруты комментариев.
 *
 * @param array<string, mixed> $endpoints Зарегистрированные маршруты REST.
 * @return array<string, mixed>
 */
function wp_frame_lite_remove_comment_rest_routes( array $endpoints ): array {
	unset(
		$endpoints['/wp/v2/comments'],
		$endpoints['/wp/v2/comments/(?P<id>[\d]+)']
	);

	return $endpoints;
}
add_filter( 'rest_endpoints', 'wp_frame_lite_remove_comment_rest_routes' );

// ── Фиды ──────────────────────────────────────────────────────────────────────

add_filter( 'feed_links_show_comments_feed', '__return_false' );
add_filter( 'post_comments_feed_link', '__return_empty_string' );

/**
 * Отдаёт 403 на фид комментариев.
 */
function wp_frame_lite_block_comment_feed(): void {
	if ( is_comment_feed() ) {
		wp_die( 'Комментарии отключены.', '', array( 'response' => 403 ) );
	}
}
add_action( 'template_redirect', 'wp_frame_lite_block_comment_feed' );

// ── Админка ───────────────────────────────────────────────────────────────────

/**
 * Убирает пункты меню «Комментарии» и «Настройки → Обсуждение».
 */
function wp_frame_lite_remove_comment_menus(): void {
	remove_menu_page( 'edit-comments.php' );
	remove_submenu_page( 'options-general.php', 'options-discussion.php' );
}
add_action( 'admin_menu', 'wp_frame_lite_remove_comment_menus' );

/**
 * Уводит с этих экранов при прямом заходе по URL в обход скрытых пунктов меню.
 */
function wp_frame_lite_redirect_comment_screens(): void {
	global $pagenow;

	if ( in_array( $pagenow, array( 'edit-comments.php', 'options-discussion.php' ), true ) ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
}
add_action( 'admin_init', 'wp_frame_lite_redirect_comment_screens' );

/**
 * Убирает виджет «Свежие комментарии» с консоли.
 */
function wp_frame_lite_remove_comment_dashboard_widget(): void {
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
}
add_action( 'wp_dashboard_setup', 'wp_frame_lite_remove_comment_dashboard_widget' );

/**
 * Убирает счётчик комментариев из админ-бара.
 *
 * @param WP_Admin_Bar $wp_admin_bar Админ-бар.
 */
function wp_frame_lite_remove_comment_admin_bar_node( WP_Admin_Bar $wp_admin_bar ): void {
	$wp_admin_bar->remove_node( 'comments' );
}
add_action( 'admin_bar_menu', 'wp_frame_lite_remove_comment_admin_bar_node', 999 );

/**
 * Снимает с регистрации виджет «Свежие комментарии».
 */
function wp_frame_lite_unregister_comment_widget(): void {
	unregister_widget( 'WP_Widget_Recent_Comments' );
}
add_action( 'widgets_init', 'wp_frame_lite_unregister_comment_widget', 20 );
