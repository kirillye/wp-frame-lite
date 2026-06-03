<?php
/**
 * Отключение комментариев на всём сайте.
 *
 * @package wp-frame-lite
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );

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

add_action(
	'admin_menu',
	function (): void {
		remove_menu_page( 'edit-comments.php' );
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
	'template_redirect',
	function (): void {
		if ( is_comment_feed() ) {
			wp_die( esc_html__( 'Комментарии отключены.', 'wp-frame-lite' ), '', array( 'response' => 403 ) );
		}
	}
);
