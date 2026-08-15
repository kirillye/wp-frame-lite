<?php
/**
 * Страница настроек темы.
 *
 * Реализована на ACF Options Page (требуется ACF PRO).
 * Поля описаны в acf-json/group_wpfl_site_settings.json.
 *
 * Использование в шаблонах:
 *   wp_frame_lite_option( 'contact_email' );
 *   wp_frame_lite_rows( 'contact_phones' );
 *
 * @package wp-frame-lite
 */

defined( 'ABSPATH' ) || exit;

const WP_FRAME_LITE_OPTIONS_SLUG = 'wp-frame-settings';

/**
 * Регистрирует страницу настроек темы. Требует ACF PRO.
 */
function wp_frame_lite_register_options_page(): void {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title' => 'Настройки сайта',
			'menu_title' => 'Настройки сайта',
			'menu_slug'  => WP_FRAME_LITE_OPTIONS_SLUG,
			'capability' => 'edit_theme_options',
			'position'   => 59,
			'icon_url'   => 'dashicons-admin-generic',
			'redirect'   => false,
			'autoload'   => true,
		)
	);
}
add_action( 'acf/init', 'wp_frame_lite_register_options_page' );

/**
 * Получить строки repeater-поля со страницы настроек.
 *
 * @param string $field Имя ACF-поля.
 * @return array<int, array<string, mixed>>
 */
function wp_frame_lite_rows( string $field ): array {
	$rows = wp_frame_lite_option( $field, array() );

	return is_array( $rows ) ? $rows : array();
}

/**
 * Привести телефон к виду, пригодному для href="tel:".
 *
 * @param string $phone Телефон в произвольном формате.
 */
function wp_frame_lite_tel( string $phone ): string {
	return (string) preg_replace( '/[^\d+]/', '', $phone );
}
