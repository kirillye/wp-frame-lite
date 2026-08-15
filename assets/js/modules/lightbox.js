/**
 * Лайтбокс на GLightbox.
 *
 * Разметка — ссылка на полноразмерный файл с классом .glightbox:
 *
 *   <a href="/full.jpg" class="glightbox" data-gallery="services">
 *     <img src="/thumb.jpg" alt="">
 *   </a>
 *
 * Ссылки с одинаковым data-gallery листаются как одна галерея.
 *
 * ВАЖНО: тип слайда GLightbox определяет по расширению в URL. Если ссылка ведёт
 * на файл без расширения (или на data-URI), библиотека клик не перехватит и
 * браузер просто уйдёт по ссылке. В таких случаях тип задаётся явно:
 *
 *   <a href="/download?id=12" class="glightbox" data-type="image">
 *
 * Сама библиотека грузится не всегда — включайте её через
 * wp_frame_lite_use( 'glightbox' ) на хуке wp_enqueue_scripts.
 */

export function initLightbox() {
	const nodes = document.querySelectorAll('.glightbox');

	if (!nodes.length) return;

	if (typeof window.GLightbox !== 'function') {
		// eslint-disable-next-line no-console
		console.warn(
			'[wp-frame-lite] На странице есть .glightbox, но GLightbox не подключён. ' +
				"Добавьте wp_frame_lite_use( 'glightbox' ) на хук wp_enqueue_scripts.",
		);
		return;
	}

	window.GLightbox({
		selector: '.glightbox',
		touchNavigation: true,
		loop: true,
		openEffect: 'fade',
		closeEffect: 'fade',
	});
}
