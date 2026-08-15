import '../css/main.css';

import { initNavigation } from './modules/navigation.js';
import { initSliders } from './modules/slider.js';
import { initLightbox } from './modules/lightbox.js';

/**
 * Инициализация откладывается до DOMContentLoaded намеренно.
 *
 * Сторонние библиотеки подключаются условно и могут оказаться в подвале
 * как до этого скрипта, так и после него. К DOMContentLoaded все классические
 * скрипты документа уже выполнены, поэтому window.Swiper и window.GLightbox
 * доступны независимо от порядка вывода.
 */
function onReady(callback) {
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', callback, { once: true });
	} else {
		callback();
	}
}

onReady(() => {
	initNavigation();
	initSliders();
	initLightbox();
});
