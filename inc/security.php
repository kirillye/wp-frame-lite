<?php
/**
 * Базовое укрепление темы.
 *
 * Все обработчики — именованные функции, а не замыкания: тему форкают и
 * допиливают под клиента, и снять ненужное через remove_action/remove_filter
 * должно быть возможно.
 *
 * @package wp-frame-lite
 */

defined( 'ABSPATH' ) || exit;

// ── Перечисление авторов ──────────────────────────────────────────────────────

/**
 * Отдаёт 403 на запросы вида ?author=1, которыми перебирают логины.
 */
function wp_frame_lite_block_author_query(): void {
	$query_string = isset( $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : '';

	// Якорь обязателен: без него шаблон срабатывал бы и на ?coauthor=1.
	if ( ! is_admin() && preg_match( '/(^|&)author=\d/i', $query_string ) ) {
		wp_die( '', '', array( 'response' => 403 ) );
	}
}
add_action( 'init', 'wp_frame_lite_block_author_query' );

/**
 * Уводит архивы авторов на главную: отдельных страниц авторов тема не делает.
 */
function wp_frame_lite_redirect_author_archive(): void {
	if ( is_author() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'wp_frame_lite_redirect_author_archive' );

/**
 * Закрывает /wp-json/wp/v2/users от неавторизованных.
 *
 * Без этого блокировка ?author=1 остаётся полумерой: список логинов спокойно
 * забирается через REST. Авторизованным с правом list_users эндпоинт нужен —
 * его использует редактор блоков.
 *
 * @param array<string, mixed> $endpoints Зарегистрированные маршруты REST.
 * @return array<string, mixed>
 */
function wp_frame_lite_restrict_user_rest_routes( array $endpoints ): array {
	if ( current_user_can( 'list_users' ) ) {
		return $endpoints;
	}

	unset(
		$endpoints['/wp/v2/users'],
		$endpoints['/wp/v2/users/(?P<id>[\d]+)']
	);

	return $endpoints;
}
add_filter( 'rest_endpoints', 'wp_frame_lite_restrict_user_rest_routes' );

/**
 * Убирает имя и ссылку автора из ответа oEmbed — третий путь перечисления.
 *
 * @param array<string, mixed> $data Данные ответа oEmbed.
 * @return array<string, mixed>
 */
function wp_frame_lite_strip_oembed_author( array $data ): array {
	unset( $data['author_name'], $data['author_url'] );

	return $data;
}
add_filter( 'oembed_response_data', 'wp_frame_lite_strip_oembed_author' );

// ── Заголовки ответа ──────────────────────────────────────────────────────────

/**
 * Убирает X-Pingback: пингбеки в теме отключены.
 *
 * @param array<string, string> $headers Заголовки ответа.
 * @return array<string, string>
 */
function wp_frame_lite_remove_pingback_header( array $headers ): array {
	unset( $headers['X-Pingback'] );

	return $headers;
}
add_filter( 'wp_headers', 'wp_frame_lite_remove_pingback_header' );

/**
 * Добавляет базовые заголовки безопасности.
 *
 * Намеренно без Content-Security-Policy и Strict-Transport-Security: первая
 * слишком зависит от набора скриптов конкретного проекта, вторая должна
 * ставиться на уровне сервера и при неполностью настроенном HTTPS способна
 * сделать сайт недоступным.
 *
 * Набор фильтруется — на проекте его можно дополнить или урезать:
 *
 *     add_filter( 'wp_frame_lite_security_headers', function ( array $h ): array {
 *         $h['X-Frame-Options'] = 'DENY';
 *         return $h;
 *     } );
 *
 * @param array<string, string> $headers Заголовки ответа.
 * @return array<string, string>
 */
function wp_frame_lite_security_headers( array $headers ): array {
	$security = apply_filters(
		'wp_frame_lite_security_headers',
		array(
			'X-Content-Type-Options' => 'nosniff',
			'X-Frame-Options'        => 'SAMEORIGIN',
			'Referrer-Policy'        => 'strict-origin-when-cross-origin',
			'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=(), payment=()',
		)
	);

	return array_merge( $headers, $security );
}
add_filter( 'wp_headers', 'wp_frame_lite_security_headers' );

// ── Вход и скрипты ────────────────────────────────────────────────────────────

/**
 * Единый текст ошибки входа: штатный подсказывает, существует ли логин.
 */
function wp_frame_lite_generic_login_error(): string {
	return 'Неверный логин или пароль.';
}
add_filter( 'login_errors', 'wp_frame_lite_generic_login_error' );

/**
 * Отвязывает jquery-migrate от jQuery на фронте.
 *
 * @param WP_Scripts $scripts Реестр скриптов.
 */
function wp_frame_lite_remove_jquery_migrate( WP_Scripts $scripts ): void {
	if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
		$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
	}
}
add_action( 'wp_default_scripts', 'wp_frame_lite_remove_jquery_migrate' );

// ── Загрузка SVG ──────────────────────────────────────────────────────────────

/**
 * Разрешает загрузку SVG тем, кому вообще можно загружать файлы.
 *
 * @param array<string, string> $mimes Разрешённые типы.
 * @return array<string, string>
 */
function wp_frame_lite_allow_svg_upload( array $mimes ): array {
	if ( current_user_can( 'upload_files' ) ) {
		$mimes['svg'] = 'image/svg+xml';
	}

	return $mimes;
}
add_filter( 'upload_mimes', 'wp_frame_lite_allow_svg_upload' );

/**
 * Проставляет SVG корректный тип: штатная проверка по содержимому его не узнаёт.
 *
 * @param array<string, mixed> $data     Результат проверки типа.
 * @param string               $file     Путь к файлу.
 * @param string               $filename Имя файла.
 * @return array<string, mixed>
 */
function wp_frame_lite_fix_svg_filetype( array $data, string $file, string $filename ): array {
	if ( ! current_user_can( 'upload_files' ) ) {
		return $data;
	}

	if ( 'svg' === strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) ) {
		$data['ext']  = 'svg';
		$data['type'] = 'image/svg+xml';
	}

	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'wp_frame_lite_fix_svg_filetype', 10, 3 );

/**
 * Вырезает из загружаемого SVG исполняемые куски.
 *
 * @param array<string, mixed> $file Загружаемый файл.
 * @return array<string, mixed>
 */
function wp_frame_lite_sanitize_svg_upload( array $file ): array {
	if ( 'image/svg+xml' !== $file['type'] ) {
		return $file;
	}

	$content = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	if ( false === $content ) {
		$file['error'] = 'Не удалось прочитать SVG-файл.';
		return $file;
	}

	$dom = new DOMDocument();
	libxml_use_internal_errors( true );
	$loaded = $dom->loadXML( $content, LIBXML_NONET );
	libxml_clear_errors();

	if ( ! $loaded ) {
		$file['error'] = 'Некорректный SVG-файл.';
		return $file;
	}

	$dangerous_tags = array( 'script', 'foreignObject', 'use', 'set', 'animate', 'animateTransform', 'animateMotion', 'animateColor' );

	foreach ( $dangerous_tags as $tag ) {
		foreach ( iterator_to_array( $dom->getElementsByTagName( $tag ) ) as $node ) {
			$node->parentNode->removeChild( $node ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- свойство расширения DOM, переименовать нельзя.
		}
	}

	$xpath = new DOMXPath( $dom );

	// Обработчики on* и ссылки на javascript: в href/xlink:href.
	$expressions = array(
		'//@*[starts-with(name(), "on")]',
		'//@*[(local-name() = "href") and starts-with(normalize-space(translate(., "JAVSCRIPT", "javscript")), "javascript:")]',
	);

	foreach ( $expressions as $expression ) {
		foreach ( iterator_to_array( $xpath->query( $expression ) ) as $attr ) {
			$attr->ownerElement->removeAttributeNode( $attr ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- свойство расширения DOM, переименовать нельзя.
		}
	}

	file_put_contents( $file['tmp_name'], $dom->saveXML() ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'wp_frame_lite_sanitize_svg_upload' );
