<?php
/**
 * Утилиты для администратора.
 *
 * @package wp-frame-lite
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_bar_menu',
	function ( WP_Admin_Bar $wp_admin_bar ): void {
		if ( is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $template;

		if ( empty( $template ) ) {
			return;
		}

		$name = basename( $template );

		$wp_admin_bar->add_node(
			array(
				'id'    => 'wp-frame-template',
				'title' => 'Шаблон страницы: ' . esc_html( $name ),
				'meta'  => array( 'title' => 'Шаблон страницы: ' . esc_attr( $name ) ),
			)
		);
	},
	100
);

add_action(
	'admin_bar_menu',
	function ( WP_Admin_Bar $wp_admin_bar ): void {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen || 'post' !== $screen->base ) {
			return;
		}

		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! $post_id ) {
			return;
		}

		$template = get_post_meta( $post_id, '_wp_page_template', true ) ?: 'default';
		$label    = 'default' === $template ? __( '(по умолчанию)', 'wp-frame-lite' ) : $template;

		$wp_admin_bar->add_node(
			array(
				'id'    => 'wp-frame-template',
				'title' => 'Шаблон: ' . esc_html( $label ),
				'meta'  => array( 'title' => 'Шаблон: ' . esc_attr( $label ) ),
			)
		);
	},
	100
);
