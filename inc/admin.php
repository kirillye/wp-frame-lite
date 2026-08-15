<?php
/**
 * Утилиты для администратора.
 *
 * @package wp-frame-lite
 */

defined( 'ABSPATH' ) || exit;

/**
 * Показывает в админ-баре, каким шаблоном отрисована страница.
 *
 * На фронте — реальный подключённый файл, в админке на экране правки —
 * шаблон, выбранный у записи. Обе ветки регистрируют один и тот же узел,
 * поэтому живут в одной функции: два обработчика с одинаковым id молча
 * перетирали бы друг друга при любой правке условий.
 *
 * @param WP_Admin_Bar $wp_admin_bar Админ-бар.
 */
function wp_frame_lite_admin_bar_template_node( WP_Admin_Bar $wp_admin_bar ): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( is_admin() ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen || 'post' !== $screen->base ) {
			return;
		}

		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! $post_id ) {
			return;
		}

		$template = get_post_meta( $post_id, '_wp_page_template', true );

		if ( ! $template ) {
			$template = 'default';
		}

		$label = 'default' === $template ? '(по умолчанию)' : $template;
	} else {
		global $template;

		if ( empty( $template ) ) {
			return;
		}

		$label = basename( $template );
	}

	$wp_admin_bar->add_node(
		array(
			'id'    => 'wp-frame-template',
			'title' => 'Шаблон: ' . esc_html( $label ),
			'meta'  => array( 'title' => 'Шаблон: ' . esc_attr( $label ) ),
		)
	);
}
add_action( 'admin_bar_menu', 'wp_frame_lite_admin_bar_template_node', 100 );
