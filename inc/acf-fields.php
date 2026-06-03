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
		if ( ! current_user_can( 'activate_plugins' ) || function_exists( 'acf' ) || class_exists( 'ACF' ) ) {
			return;
		}

		echo '<div class="notice notice-error"><p>';
		echo esc_html__( 'Для темы wp-frame-lite обязателен плагин Advanced Custom Fields.', 'wp-frame-lite' );
		echo '</p></div>';
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
 * @param string $field   ACF field name.
 * @param mixed  $default Fallback value.
 * @return mixed
 */
function wp_frame_lite_option( string $field, mixed $default = '' ): mixed {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}

	$value = get_field( $field, 'option' );

	return null !== $value && false !== $value && '' !== $value ? $value : $default;
}
