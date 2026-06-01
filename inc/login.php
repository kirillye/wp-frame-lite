<?php
/**
 * Кастомный URL страницы входа.
 * Стандартный /wp-login.php блокируется, вход доступен только по slug ниже.
 *
 * @package wp-frame-lite
 */

define( 'WP_FRAME_LOGIN_SLUG', 'my-page-by-inveris' );

add_action( 'init', function() {
	global $pagenow;

	if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}

	$path = trim( parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ), '/' );

	// Показать форму входа по кастомному URL.
	if ( WP_FRAME_LOGIN_SLUG === $path ) {
		$_SERVER['PHP_SELF'] = '/wp-login.php';
		require ABSPATH . 'wp-login.php';
		exit;
	}

	// Заблокировать прямой GET-доступ к wp-login.php.
	if ( 'wp-login.php' === $pagenow && empty( $_POST ) ) {
		wp_safe_redirect( home_url( '/404' ) );
		exit;
	}
}, 1 );

// Заменить URL входа во всех ссылках WordPress.
add_filter( 'login_url', function( $login_url, $redirect, $force_reauth ) {
	$url = home_url( '/' . WP_FRAME_LOGIN_SLUG . '/' );
	if ( $redirect ) {
		$url = add_query_arg( 'redirect_to', urlencode( $redirect ), $url );
	}
	if ( $force_reauth ) {
		$url = add_query_arg( 'reauth', '1', $url );
	}
	return $url;
}, 10, 3 );

// Редирект после выхода на кастомный URL входа.
add_filter( 'logout_redirect', function() {
	return home_url( '/' . WP_FRAME_LOGIN_SLUG . '/?loggedout=true' );
} );

// Исправить порт в redirect_to (проблема Local by Flywheel с внутренним портом).
add_filter( 'login_redirect', function( $redirect_to, $requested_redirect_to ) {
	if ( $requested_redirect_to ) {
		$parsed   = wp_parse_url( $requested_redirect_to );
		$home     = wp_parse_url( home_url() );
		if ( isset( $parsed['host'] ) && $parsed['host'] === $home['host'] ) {
			$path = isset( $parsed['path'] ) ? $parsed['path'] : '/';
			$query = isset( $parsed['query'] ) ? '?' . $parsed['query'] : '';
			return home_url( $path . $query );
		}
	}
	return $redirect_to;
}, 10, 2 );
