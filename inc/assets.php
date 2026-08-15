<?php
/**
 * Подключение стилей и скриптов темы.
 *
 * @package wp-frame-lite
 */

defined( 'ABSPATH' ) || exit;

/**
 * Сторонние библиотеки из assets/vendor.
 *
 * Регистрируются всегда, подключаются только по требованию: вместе они весят
 * около 200 кБ против 15 кБ собственной сборки темы, и грузить их на каждой
 * странице корпоративного сайта незачем.
 *
 * Ключ — хендл, значение — файлы относительно assets/vendor.
 */
const WP_FRAME_LITE_VENDOR = array(
	'swiper'    => array(
		'css' => 'swiper/swiper-bundle.min.css',
		'js'  => 'swiper/swiper-bundle.min.js',
	),
	'glightbox' => array(
		'css' => 'glightbox/glightbox.min.css',
		'js'  => 'glightbox/glightbox.min.js',
	),
);

/**
 * Версия ассета по времени изменения файла.
 *
 * Кэш сбрасывается при пересборке и не меняется на деплое без изменений.
 *
 * @param string $relative Путь относительно корня темы, со слешем в начале.
 */
function wp_frame_lite_asset_version( string $relative ): string {
	$path = get_template_directory() . $relative;

	if ( file_exists( $path ) ) {
		return (string) filemtime( $path );
	}

	return (string) wp_get_theme()->get( 'Version' );
}

/**
 * Подключает собранные стили и скрипты темы и регистрирует библиотеки.
 */
function wp_frame_lite_scripts(): void {
	$uri = get_template_directory_uri();

	$css = '/assets/dist/css/main.css';
	$js  = '/assets/dist/js/main.js';

	wp_enqueue_style( 'wp-frame-lite-style', $uri . $css, array(), wp_frame_lite_asset_version( $css ) );
	wp_enqueue_script( 'wp-frame-lite-main', $uri . $js, array(), wp_frame_lite_asset_version( $js ), true );

	foreach ( WP_FRAME_LITE_VENDOR as $handle => $files ) {
		$style  = '/assets/vendor/' . $files['css'];
		$script = '/assets/vendor/' . $files['js'];

		wp_register_style( $handle, $uri . $style, array(), wp_frame_lite_asset_version( $style ) );
		wp_register_script( $handle, $uri . $script, array(), wp_frame_lite_asset_version( $script ), true );
	}
}
add_action( 'wp_enqueue_scripts', 'wp_frame_lite_scripts' );

/**
 * Подключить сторонние библиотеки на текущей странице.
 *
 * Вызывать на хуке wp_enqueue_scripts:
 *
 *     add_action( 'wp_enqueue_scripts', function () {
 *         if ( is_front_page() ) {
 *             wp_frame_lite_use( 'swiper', 'glightbox' );
 *         }
 *     } );
 *
 * Из шаблона вызывать поздно нельзя: wp_head к тому моменту уже отработал,
 * стили уедут в подвал и страница мигнёт нестилизованным блоком.
 *
 * Порядок вывода скриптов при этом не важен: инициализация в main.js ждёт
 * DOMContentLoaded, к которому все классические скрипты уже выполнены.
 *
 * @param string ...$handles Хендлы из WP_FRAME_LITE_VENDOR.
 */
function wp_frame_lite_use( string ...$handles ): void {
	foreach ( $handles as $handle ) {
		if ( ! isset( WP_FRAME_LITE_VENDOR[ $handle ] ) ) {
			_doing_it_wrong(
				__FUNCTION__,
				esc_html( sprintf( 'Неизвестная библиотека «%s».', $handle ) ),
				'1.3.0'
			);
			continue;
		}

		if ( did_action( 'wp_head' ) ) {
			_doing_it_wrong(
				__FUNCTION__,
				esc_html( sprintf( 'Библиотека «%s» запрошена после wp_head — её стили уедут в подвал.', $handle ) ),
				'1.3.0'
			);
		}

		wp_enqueue_style( $handle );
		wp_enqueue_script( $handle );
	}
}
