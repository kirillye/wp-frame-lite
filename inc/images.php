<?php
/**
 * Картинки темы: WebP с фолбэком на исходник.
 *
 * Исходники лежат в assets/images, конвертированные версии — в assets/webp
 * (собираются командой `npm run images`).
 *
 * Формат выбирает браузер через <picture>, а не сервер по заголовку Accept:
 * при включённом кэшировании страниц (W3TC, Cloudflare) закэшированный HTML
 * отдал бы один и тот же URL всем подряд, независимо от поддержки WebP.
 *
 * Медиатеки WordPress это не касается — только файлы внутри темы.
 *
 * @package wp-frame-lite
 */

defined( 'ABSPATH' ) || exit;

const WP_FRAME_LITE_IMAGES_DIR = '/assets/images';
const WP_FRAME_LITE_WEBP_DIR   = '/assets/webp';

/**
 * Манифест размеров, собранный `npm run images`.
 *
 * Нужен, чтобы отдавать width/height без getimagesize() на каждый рендер
 * и не давать вёрстке прыгать во время загрузки.
 *
 * @return array<string, array{w:int, h:int, webp:?string}>
 */
function wp_frame_lite_image_manifest(): array {
	static $manifest = null;

	if ( null !== $manifest ) {
		return $manifest;
	}

	$manifest = array();
	$path     = get_template_directory() . WP_FRAME_LITE_WEBP_DIR . '/manifest.json';

	if ( is_readable( $path ) ) {
		$raw     = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$decoded = json_decode( (string) $raw, true );

		if ( is_array( $decoded ) ) {
			$manifest = $decoded;
		}
	}

	return $manifest;
}

/**
 * URL исходника картинки темы.
 *
 * @param string $file Путь относительно assets/images, например 'hero.jpg'.
 */
function wp_frame_lite_image_url( string $file ): string {
	return get_template_directory_uri() . WP_FRAME_LITE_IMAGES_DIR . '/' . ltrim( $file, '/' );
}

/**
 * URL WebP-версии или пустая строка, если её нет.
 *
 * @param string $file Путь относительно assets/images, например 'hero.jpg'.
 */
function wp_frame_lite_webp_url( string $file ): string {
	$entry = wp_frame_lite_image_manifest()[ ltrim( $file, '/' ) ] ?? array();

	if ( empty( $entry['webp'] ) ) {
		return '';
	}

	return get_template_directory_uri() . WP_FRAME_LITE_WEBP_DIR . '/' . $entry['webp'];
}

/**
 * Разметка <picture> с WebP-источником и фолбэком на исходник.
 *
 * Если WebP-версии нет (SVG, не запускали сборку) — возвращается обычный <img>.
 * Если исходника нет на диске — пустая строка.
 *
 * @param string               $file  Путь относительно assets/images, например 'hero.jpg'.
 * @param array<string, mixed> $attrs Атрибуты <img>. Значение null убирает атрибут.
 *                                    По умолчанию alt пустой (декоративная картинка),
 *                                    loading="lazy", decoding="async".
 */
function wp_frame_lite_get_image( string $file, array $attrs = array() ): string {
	$file = ltrim( $file, '/' );

	if ( ! is_readable( get_template_directory() . WP_FRAME_LITE_IMAGES_DIR . '/' . $file ) ) {
		return '';
	}

	$entry = wp_frame_lite_image_manifest()[ $file ] ?? array();

	$attrs = array_merge(
		array(
			'src'      => wp_frame_lite_image_url( $file ),
			'alt'      => '',
			'loading'  => 'lazy',
			'decoding' => 'async',
		),
		$attrs
	);

	// Размеры из манифеста — только если их не задали явно.
	if ( ! empty( $entry['w'] ) && ! empty( $entry['h'] ) ) {
		$attrs += array(
			'width'  => $entry['w'],
			'height' => $entry['h'],
		);
	}

	$html = '<img';

	foreach ( $attrs as $name => $value ) {
		if ( null === $value || false === $value ) {
			continue;
		}

		if ( true === $value ) {
			$html .= ' ' . esc_attr( $name );
			continue;
		}

		$html .= sprintf(
			' %s="%s"',
			esc_attr( $name ),
			'src' === $name ? esc_url( (string) $value ) : esc_attr( (string) $value )
		);
	}

	$html .= '>';

	$webp = wp_frame_lite_webp_url( $file );

	if ( '' === $webp ) {
		return $html;
	}

	return sprintf(
		'<picture><source srcset="%s" type="image/webp">%s</picture>',
		esc_url( $webp ),
		$html
	);
}

/**
 * Вывести разметку из wp_frame_lite_get_image().
 *
 * @param string               $file  Путь относительно assets/images.
 * @param array<string, mixed> $attrs Атрибуты <img>.
 */
function wp_frame_lite_image( string $file, array $attrs = array() ): void {
	echo wp_frame_lite_get_image( $file, $attrs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- разметка экранируется внутри.
}
