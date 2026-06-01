<?php
/**
 * Утилиты для администратора.
 *
 * @package wp-frame-lite
 */

// Фронтенд: показать PHP-файл шаблона который рендерит текущую страницу.
add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
	if ( is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	global $template;
	if ( empty( $template ) ) {
		return;
	}

	$name = basename( $template );

	$wp_admin_bar->add_node( array(
		'id'    => 'wp-frame-template',
		'title' => '&#11042; Шаблон страницы: ' . esc_html( $name ),
		'meta'  => array( 'title' => 'Шаблон страницы: ' . $name ),
	) );
}, 100 );

// Админка: показать page template при редактировании поста/страницы.
add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'post' !== $screen->base ) {
		return;
	}

	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
	if ( ! $post_id ) {
		return;
	}

	$tpl   = get_post_meta( $post_id, '_wp_page_template', true ) ?: 'default';
	$label = ( 'default' === $tpl )
		? __( '(по умолчанию)', 'wp-frame-lite' )
		: esc_html( $tpl );

	$wp_admin_bar->add_node( array(
		'id'    => 'wp-frame-template',
		'title' => '&#9670; ' . $label,
		'meta'  => array( 'title' => 'Шаблон: ' . $label ),
	) );
}, 100 );
