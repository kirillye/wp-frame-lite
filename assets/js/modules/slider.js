/**
 * Слайдеры на Swiper.
 *
 * Разметка:
 *
 *   <div class="swiper js-slider" data-slider='{"loop":true,"slidesPerView":3}'>
 *     <div class="swiper-wrapper">
 *       <div class="swiper-slide">…</div>
 *     </div>
 *     <div class="swiper-pagination"></div>
 *     <button class="swiper-button-prev" type="button"></button>
 *     <button class="swiper-button-next" type="button"></button>
 *   </div>
 *
 * Стрелки и пагинация подключаются сами, если их разметка лежит внутри слайдера.
 * data-slider необязателен и принимает любые параметры Swiper в виде JSON.
 *
 * Сама библиотека грузится не всегда — включайте её через
 * wp_frame_lite_use( 'swiper' ) на хуке wp_enqueue_scripts.
 */

const DEFAULTS = {
	slidesPerView: 1,
	spaceBetween: 16,

	// Swiper по умолчанию подписывает стрелки и точки по-английски
	// («Previous slide», «Go to slide 1»). Тема одноязычная — переводим.
	a11y: {
		enabled: true,
		prevSlideMessage: 'Предыдущий слайд',
		nextSlideMessage: 'Следующий слайд',
		firstSlideMessage: 'Это первый слайд',
		lastSlideMessage: 'Это последний слайд',
		paginationBulletMessage: 'Перейти к слайду {{index}}',
		slideLabelMessage: 'Слайд {{index}} из {{slidesLength}}',
	},
};

export function initSliders() {
	const nodes = document.querySelectorAll('.js-slider');

	if (!nodes.length) return;

	if (typeof window.Swiper !== 'function') {
		// eslint-disable-next-line no-console
		console.warn(
			'[wp-frame-lite] На странице есть .js-slider, но Swiper не подключён. ' +
				"Добавьте wp_frame_lite_use( 'swiper' ) на хук wp_enqueue_scripts.",
		);
		return;
	}

	nodes.forEach((node) => {
		// Ищем только собственные элементы управления: во вложенных слайдерах
		// querySelector иначе схватил бы стрелки дочернего.
		const own = (selector) => {
			const el = node.querySelector(selector);
			return el && el.closest('.swiper') === node ? el : null;
		};

		let options = {};

		if (node.dataset.slider) {
			try {
				options = JSON.parse(node.dataset.slider);
			} catch (error) {
				// eslint-disable-next-line no-console
				console.warn('[wp-frame-lite] Некорректный JSON в data-slider', node, error);
			}
		}

		const config = { ...DEFAULTS, ...options };

		// Swiper двигает слайды через JS-трансформации, поэтому CSS-правило
		// prefers-reduced-motion его не останавливает: гасим переход и
		// автопрокрутку здесь.
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			config.speed = 0;
			config.autoplay = false;
		}

		const next = own('.swiper-button-next');
		const prev = own('.swiper-button-prev');
		const pagination = own('.swiper-pagination');

		if (!config.navigation && (next || prev)) {
			config.navigation = { nextEl: next, prevEl: prev };
		}

		if (!config.pagination && pagination) {
			config.pagination = { el: pagination, clickable: true };
		}

		// Swiper кладёт экземпляр в node.swiper — оттуда его можно достать
		// из другого кода: document.querySelector('.js-slider').swiper.slideNext()
		new window.Swiper(node, config);
	});
}
