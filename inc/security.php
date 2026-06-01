<?php
/**
 * Безопасность и укрепление темы.
 *
 * @package wp-frame-lite
 */

// 1. Защита от перебора пользователей (/?author=1 → главная).
add_action( 'init', function() {
	if ( ! is_admin() && isset( $_SERVER['QUERY_STRING'] ) && preg_match( '/author=\d/i', $_SERVER['QUERY_STRING'] ) ) {
		wp_die( '', '', array( 'response' => 403 ) );
	}
} );
add_action( 'template_redirect', function() {
	if ( is_author() ) {
		wp_redirect( home_url( '/' ), 301 );
		exit;
	}
} );

// 2. Убрать заголовок X-Pingback.
add_filter( 'wp_headers', function( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
} );

// 3. Обезличить сообщения об ошибке входа.
add_filter( 'login_errors', function() {
	return esc_html__( 'Неверный логин или пароль.', 'wp-frame-lite' );
} );

// 4. Отключить REST API для незалогиненных пользователей.
add_filter( 'rest_authentication_errors', function( $result ) {
	if ( ! empty( $result ) ) {
		return $result;
	}
	if ( ! is_user_logged_in() ) {
		return new WP_Error(
			'rest_not_logged_in',
			esc_html__( 'Доступ запрещён.', 'wp-frame-lite' ),
			array( 'status' => 401 )
		);
	}
	return $result;
} );

// 5. Убрать jQuery Migrate на фронтенде.
add_action( 'wp_default_scripts', function( $scripts ) {
	if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
		$scripts->registered['jquery']->deps = array_diff(
			$scripts->registered['jquery']->deps,
			array( 'jquery-migrate' )
		);
	}
} );

// 10. Поддержка SVG в медиазагрузках — только для администраторов.
add_filter( 'upload_mimes', function( $mimes ) {
	if ( current_user_can( 'manage_options' ) ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
	}
	return $mimes;
} );

add_filter( 'wp_check_filetype_and_ext', function( $data, $file, $filename, $mimes ) {
	if ( ! $data['type'] && current_user_can( 'manage_options' ) ) {
		$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
		if ( in_array( $ext, array( 'svg', 'svgz' ), true ) ) {
			$data['type'] = 'image/svg+xml';
			$data['ext']  = $ext;
		}
	}
	return $data;
}, 10, 4 );
