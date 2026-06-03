<?php
/**
 * Базовое укрепление темы.
 *
 * @package wp-frame-lite
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function (): void {
		$query_string = isset( $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : '';

		if ( ! is_admin() && preg_match( '/author=\d/i', $query_string ) ) {
			wp_die( '', '', array( 'response' => 403 ) );
		}
	}
);

add_action(
	'template_redirect',
	function (): void {
		if ( is_author() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}
);

add_filter(
	'wp_headers',
	function ( array $headers ): array {
		unset( $headers['X-Pingback'] );

		return $headers;
	}
);

add_filter(
	'login_errors',
	function (): string {
		return esc_html__( 'Неверный логин или пароль.', 'wp-frame-lite' );
	}
);

add_action(
	'wp_default_scripts',
	function ( WP_Scripts $scripts ): void {
		if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
			$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
		}
	}
);

// SVG upload support — only for users who can upload files.
add_filter(
	'upload_mimes',
	function ( array $mimes ): array {
		if ( current_user_can( 'upload_files' ) ) {
			$mimes['svg']  = 'image/svg+xml';
			$mimes['svgz'] = 'image/svg+xml';
		}

		return $mimes;
	}
);

add_filter(
	'wp_check_filetype_and_ext',
	function ( array $data, string $file, string $filename ): array {
		if ( ! current_user_can( 'upload_files' ) ) {
			return $data;
		}

		$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

		if ( 'svg' === $ext || 'svgz' === $ext ) {
			$data['ext']  = $ext;
			$data['type'] = 'image/svg+xml';
		}

		return $data;
	},
	10,
	3
);

// Strip potentially dangerous elements from uploaded SVGs.
add_filter(
	'wp_handle_upload_prefilter',
	function ( array $file ): array {
		if ( 'image/svg+xml' !== $file['type'] ) {
			return $file;
		}

		$content = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( false === $content ) {
			$file['error'] = esc_html__( 'Не удалось прочитать SVG-файл.', 'wp-frame-lite' );
			return $file;
		}

		$dom = new DOMDocument();
		libxml_use_internal_errors( true );
		$loaded = $dom->loadXML( $content, LIBXML_NONET );
		libxml_clear_errors();

		if ( ! $loaded ) {
			$file['error'] = esc_html__( 'Некорректный SVG-файл.', 'wp-frame-lite' );
			return $file;
		}

		$dangerous_tags = array( 'script', 'foreignObject', 'use', 'set', 'animate', 'animateTransform', 'animateMotion', 'animateColor' );

		foreach ( $dangerous_tags as $tag ) {
			foreach ( $dom->getElementsByTagName( $tag ) as $node ) {
				$node->parentNode->removeChild( $node );
			}
		}

		// Strip on* event attributes from all elements.
		$xpath = new DOMXPath( $dom );
		foreach ( $xpath->query( '//@*[starts-with(name(), "on")]' ) as $attr ) {
			$attr->ownerElement->removeAttributeNode( $attr );
		}

		file_put_contents( $file['tmp_name'], $dom->saveXML() ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

		return $file;
	}
);
