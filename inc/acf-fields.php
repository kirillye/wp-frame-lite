<?php
/**
 * Настройки ACF и вспомогательные функции.
 *
 * @package wp-frame-lite
 */

defined( 'ABSPATH' ) || exit;

const WP_FRAME_LITE_ACF_JSON_DIR = '/acf-json';

add_action(
	'admin_notices',
	function (): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( ! function_exists( 'acf' ) && ! class_exists( 'ACF' ) ) {
			echo '<div class="notice notice-error"><p>';
			echo esc_html( 'Для темы wp-frame-lite обязателен плагин Advanced Custom Fields PRO.' );
			echo '</p></div>';

			return;
		}

		if ( ! function_exists( 'acf_add_options_page' ) ) {
			echo '<div class="notice notice-warning"><p>';
			echo esc_html( 'Установлена бесплатная версия ACF: страница «Настройки сайта» недоступна. Нужна ACF PRO.' );
			echo '</p></div>';
		}
	}
);

add_filter(
	'acf/settings/save_json',
	function (): string {
		return get_template_directory() . WP_FRAME_LITE_ACF_JSON_DIR;
	}
);

add_filter(
	'acf/settings/load_json',
	function ( array $paths ): array {
		$paths[] = get_template_directory() . WP_FRAME_LITE_ACF_JSON_DIR;

		return array_values( array_unique( $paths ) );
	}
);

/**
 * Получить ACF option field с fallback-значением.
 *
 * Не использовать для true_false — см. wp_frame_lite_flag().
 *
 * @param string $field   ACF field name.
 * @param mixed  $fallback Fallback value.
 * @return mixed
 */
function wp_frame_lite_option( string $field, mixed $fallback = '' ): mixed {
	return wp_frame_lite_field( $field, $fallback, 'option' );
}

/**
 * Получить ACF-поле записи с fallback-значением.
 *
 * Не использовать для true_false: выключенный чекбокс вернёт false и будет
 * заменён на $fallback. Для флагов есть wp_frame_lite_flag().
 *
 * @param string          $field   ACF field name.
 * @param mixed           $fallback Fallback value.
 * @param int|string|null $post_id ID записи, 'option' или null (текущая запись).
 * @return mixed
 */
function wp_frame_lite_field( string $field, mixed $fallback = '', int|string|null $post_id = null ): mixed {
	if ( ! function_exists( 'get_field' ) ) {
		return $fallback;
	}

	$value = get_field( $field, $post_id ?? false );

	return null !== $value && false !== $value && '' !== $value && array() !== $value ? $value : $fallback;
}

/**
 * Получить булево ACF-поле (true_false), сохраняя явное «выключено».
 *
 * @param string          $field   ACF field name.
 * @param bool            $fallback Значение, если поле ещё ни разу не сохраняли.
 * @param int|string|null $post_id ID записи, 'option' или null (текущая запись).
 */
function wp_frame_lite_flag( string $field, bool $fallback = true, int|string|null $post_id = null ): bool {
	if ( ! function_exists( 'get_field' ) ) {
		return $fallback;
	}

	$value = get_field( $field, $post_id ?? false );

	if ( null === $value || '' === $value ) {
		return $fallback;
	}

	return (bool) $value;
}
