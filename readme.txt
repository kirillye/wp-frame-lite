=== wp-frame-lite ===

Contributors: inveris
Tags: custom-logo, custom-menu, featured-images

Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.2.0
License: GNU General Public License v2 or later
License URI: LICENSE

Лёгкая стартовая тема для небольших корпоративных сайтов на WordPress.

== Description ==

Тема использует ACF PRO и ACF JSON как обязательную часть разработки. Комментарии и поиск отключены полностью, тема одноязычная. SEO-плагины не являются обязательными зависимостями.

== Changelog ==

= 1.2.0 =
* SCSS убран, стили переведены на обычный CSS с прежней структурой слоёв
* Палитра, типографика и тени вынесены в CSS-переменные --wpf-*
* Добавлен front-page.php: первый экран главной редактируется через ACF
* Добавлен конвейер картинок темы: assets/images -> assets/webp, вывод через <picture>
* Исправлен base в Vite: ассеты из CSS больше не ведут в корень сайта
* content_width приведён к реальной ширине контейнера (1092 вместо 640)
* .container добавлен во все шаблоны, page-templates/full-width.php удалён
* Настроены stylelint, Prettier, .editorconfig и общие настройки VS Code
* Добавлен theme.json: палитра, размеры шрифтов и ширина контейнера для редактора
* Добавлены align-wide и responsive-embeds
* Сборка преджимается в .br и .gz (npm run compress)
* assets/dist теперь коммитится — тема работает на хостинге без Node
* Брейкпоинт мобильного меню поднят с 600px до 992px, JS переведён на matchMedia
* PHP-линтеры переведены с заброшенного wptrt/wpthemereview на WPCS 3.x
* Удалены widgets.css без зарегистрированных сайдбаров и мёртвый .eslintrc
* Заменён screenshot.png

= 1.1.0 =
* Поиск удалён полностью, запросы отдают 404
* Комментарии отключены целиком, включая приём POST, REST и фиды
* Добавлена страница настроек темы на ACF Options (контакты, соцсети, подвал)
* Удалён текстовый домен и вся интернационализация
* Удалена мёртвая директория js/

= 1.0.0 =
* Начальный релиз
