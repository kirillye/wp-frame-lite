<?php
/**
 * Полное отключение комментариев, пингбеков и трекбеков.
 *
 * Тема не поддерживает комментарии ни в каком виде: закрыты фронт, приём POST,
 * REST-эндпоинты, фиды и весь связанный интерфейс админки.
 *
 * @package wp-frame-lite
 */

defined( 'ABSPATH' ) || exit;

// ── Фронтенд ──────────────────────────────────────────────────────────────────

add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );
add_filter( 'get_comments_number', '__return_zero', 20 );

add_filter(
	'pre_option_default_comment_status',
	function (): string {
		return 'closed';
	}
);

add_filter(
	'pre_option_default_ping_status',
	function (): string {
		return 'closed';
	}
);

// Убираем поддержку комментариев у всех типов записей.
add_action(
	'init',
	function (): void {
		foreach ( get_post_types() as $post_type ) {
			if ( post_type_supports( $post_type, 'comments' ) ) {
				remove_post_type_support( $post_type, 'comments' );
				remove_post_type_support( $post_type, 'trackbacks' );
			}
		}
	},
	100
);

// ── Приём комментариев ────────────────────────────────────────────────────────

// wp-comments-post.php.
add_action(
	'pre_comment_on_post',
	function (): void {
		wp_die( 'Комментарии отключены.', '', array( 'response' => 403 ) );
	}
);

// Любой другой путь до wp_new_comment(): REST, XML-RPC, сторонний код.
add_filter(
	'preprocess_comment',
	function (): array {
		wp_die( 'Комментарии отключены.', '', array( 'response' => 403 ) );
	}
);

// ── REST API ──────────────────────────────────────────────────────────────────

add_filter(
	'rest_endpoints',
	function ( array $endpoints ): array {
		unset(
			$endpoints['/wp/v2/comments'],
			$endpoints['/wp/v2/comments/(?P<id>[\d]+)']
		);

		return $endpoints;
	}
);

// ── Фиды ──────────────────────────────────────────────────────────────────────

add_filter( 'feed_links_show_comments_feed', '__return_false' );
add_filter( 'post_comments_feed_link', '__return_empty_string' );

add_action(
	'template_redirect',
	function (): void {
		if ( is_comment_feed() ) {
			wp_die( 'Комментарии отключены.', '', array( 'response' => 403 ) );
		}
	}
);

// ── Админка ───────────────────────────────────────────────────────────────────

add_action(
	'admin_menu',
	function (): void {
		remove_menu_page( 'edit-comments.php' );
		remove_submenu_page( 'options-general.php', 'options-discussion.php' );
	}
);

// Прямой заход по URL в обход скрытых пунктов меню.
add_action(
	'admin_init',
	function (): void {
		global $pagenow;

		if ( in_array( $pagenow, array( 'edit-comments.php', 'options-discussion.php' ), true ) ) {
			wp_safe_redirect( admin_url() );
			exit;
		}
	}
);

add_action(
	'wp_dashboard_setup',
	function (): void {
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	}
);

add_action(
	'admin_bar_menu',
	function ( WP_Admin_Bar $wp_admin_bar ): void {
		$wp_admin_bar->remove_node( 'comments' );
	},
	999
);

add_action(
	'widgets_init',
	function (): void {
		unregister_widget( 'WP_Widget_Recent_Comments' );
	},
	20
);
