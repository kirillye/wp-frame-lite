# wp-frame-lite

Лёгкая стартовая WordPress-тема для небольших корпоративных сайтов. Рассчитана на кастомную разработку под конкретного клиента и не тянет SEO-плагины как обязательные зависимости.

## Стек и допущения

- **ACF PRO — обязательный плагин.** Нужен для страницы настроек темы (Options Page) и repeater-полей.
- ACF JSON — обязательная часть темы, директория синхронизации: `acf-json/`.
- **Комментарии удалены полностью** — фронт, приём POST, REST, фиды и интерфейс админки.
- **Поиска нет** — форма всегда пустая, запросы `?s=` отдают 404.
- **Тема одноязычная.** Текстового домена и файлов перевода нет, строки в шаблонах прямым текстом.
- **Препроцессора нет.** Стили — обычный CSS: переменные закрывает `--wpf-*`, сборку и инлайн `@import` — Vite.
- SEO-плагин не обязателен: можно использовать любой подходящий или штатный `title-tag`.

## Структура

```text
acf-json/                        - ACF JSON sync
  group_wpfl_site_settings.json  - поля страницы «Настройки сайта»
  group_wpfl_front_hero.json     - поля первого экрана главной
assets/
  js/main.js                     - точка входа JS
  js/modules/                    - navigation, slider, lightbox
  vendor/                        - Swiper и GLightbox (см. vendor/README.md)
  css/main.css                   - точка входа CSS (только @import, задаёт порядок каскада)
  css/base/variables.css         - дизайн-токены (см. ниже)
  css/{base,layout,components,utilities}/
  images/                        - исходники картинок темы
  webp/                          - их webp-версии + manifest.json (npm run images)
  dist/                          - production-сборка Vite
scripts/
  build-images.mjs               - конвертация картинок в webp
  compress-dist.mjs              - преджатие dist и vendor в .br и .gz
theme.json                       - палитра и layout для редактора (см. ниже)
front-page.php                   - главная: hero + контент/лента
inc/
  acf-fields.php                 - ACF JSON, admin-notice, хелперы доступа к полям
  admin.php                      - админ-утилиты
  assets.php                     - подключение CSS/JS
  breadcrumbs.php                - хлебные крошки со Schema.org
  images.php                     - <picture> с webp и фолбэком
  cleanup.php                    - очистка head/emoji/XML-RPC, отключение поиска
  comments.php                   - полное отключение комментариев
  options.php                    - страница настроек темы и хелперы
  security.php                   - базовое укрепление, SVG-загрузки
  setup.php                      - theme support и меню
template-parts/
```

## Настройки сайта

Страница появляется в админке отдельным пунктом меню («Настройки сайта», `wp-frame-settings`, доступ по `edit_theme_options`). Поля:

| Вкладка  | Поле               | Тип                                      |
| -------- | ------------------ | ---------------------------------------- |
| Контакты | `contact_phones`   | repeater (`phone_number`, `phone_label`) |
| Контакты | `contact_email`    | email                                    |
| Контакты | `contact_schedule` | text                                     |
| Контакты | `contact_address`  | textarea                                 |
| Контакты | `contact_map_url`  | url                                      |
| Соцсети  | `social_links`     | repeater (`social_title`, `social_url`)  |
| Подвал   | `footer_copyright` | text                                     |
| Подвал   | `footer_legal`     | textarea                                 |

Контакты, соцсети, копирайт и реквизиты выводятся в `footer.php` — как рабочий пример.

### Доступ к ACF-полям

Хелперы поверх `get_field()`: возвращают fallback вместо пустого значения и не падают, если ACF не установлен.

```php
wp_frame_lite_option( 'contact_email', 'info@example.com' );  // поле со страницы настроек
wp_frame_lite_rows( 'contact_phones' );                       // строки repeater, всегда массив
wp_frame_lite_field( 'hero_title', 'Заголовок', $post_id );   // поле записи
wp_frame_lite_flag( 'hero_show', true, $post_id );            // true_false
wp_frame_lite_tel( '+7 (495) 000-00-00' );                    // нормализация под href="tel:"
```

Для `true_false` нужен именно `wp_frame_lite_flag()`. Обычный хелпер считает `false` пустым значением и подменит его на fallback — выключенный тумблер прочитался бы как включённый.

## Главная страница

`front-page.php` обслуживает оба режима — и статическую страницу, и ленту записей. Первый экран редактируется в ACF на самой странице (группа «Главная — первый экран»):

| Поле            | Тип        | Поведение                                       |
| --------------- | ---------- | ----------------------------------------------- |
| `hero_show`     | true_false | Выключает первый экран целиком                  |
| `hero_title`    | text       | Пусто → название сайта                          |
| `hero_subtitle` | textarea   | Пусто → описание сайта                          |
| `hero_button`   | link       | Ссылка не задана → кнопки нет                   |
| `hero_image`    | image      | Задано → фон с затемнением (`.hero--has-image`) |

Если главная показывает ленту записей, ACF-полей нет и hero берёт заголовок с подзаголовком из настроек WordPress.

## Хлебные крошки

Самописные, без плагинов, с микроразметкой Schema.org (`BreadcrumbList`). Покрывают записи, страницы с вложенностью, рубрики, метки, произвольные таксономии, архивы по датам и типам записей, 404.

```php
get_template_part( 'template-parts/breadcrumbs' );
```

```php
wp_frame_lite_breadcrumbs( array(
	'separator'    => '→',
	'home_label'   => 'Главная',
	'show_on_home' => false,
) );
```

На главной по умолчанию не выводятся. Классы — `.breadcrumbs`, `.breadcrumbs__list`, `.breadcrumbs__item`, `.breadcrumbs__link`, `.breadcrumbs__current`, `.breadcrumbs__sep`; стилей в теме под них нет, они добавляются под конкретный проект.

## Дизайн-токены

Все цвета, типографика, отступы контейнера, радиусы и тени объявлены как CSS-переменные `--wpf-*` в единственном блоке `:root` — [assets/css/base/variables.css](assets/css/base/variables.css). Это единственное место с сырыми значениями, перекраска под клиента делается там.

Переопределить можно и в рантайме, без пересборки — на `:root` или на любом контейнере:

```css
:root {
  --wpf-color-primary: #2563eb;
  --wpf-color-primary-rgb: 37 99 235; /* менять вместе с --wpf-color-primary */
}
```

Пара нюансов:

- Для полупрозрачных вариантов цвета есть отдельный токен каналов `--wpf-color-primary-rgb` и запись `rgb(var(--wpf-color-primary-rgb) / 12%)`. Если меняете акцент — меняйте оба токена.
- Брейкпоинты в токены не вынесены: `var()` не работает внутри `@media`. Шкала описана комментарием в начале `variables.css` — `sm 480`, `md 768`, `lg 992`, `xl 1280`. Реально используется только `lg`: на нём разворачивается горизонтальное меню. Значение продублировано в `components/navigation.css` и `assets/js/modules/navigation.js` и меняется в обоих местах сразу.

### theme.json

Дублирует палитру, размеры шрифтов и ширину контейнера для редактора Гутенберга — иначе клиент видит в палитре дефолтные цвета WordPress, а не цвета проекта. Дефолтная палитра, градиенты и дуотон отключены, чтобы в подборе цвета остались только брендовые.

Значения в `theme.json` и `variables.css` **приходится держать синхронно вручную**: `theme.json` не понимает `var()`, ему нужны литералы. При смене палитры правьте оба файла.

Стилевого блока (`styles`) в `theme.json` намеренно нет — только `settings`. Внешний вид полностью остаётся за CSS темы, так что конфликтовать с ним нечему. `add_editor_style()` тоже не подключён: `main.css` содержит стили шапки, подвала и навигации, и в редакторе они мешают. Если понадобится — заводите отдельный урезанный файл под редактор.

## Стили

Обычный CSS без препроцессора, разложенный по тем же четырём слоям:

```text
base/        variables, reset, typography
layout/      layout, header, footer, front-page, posts
components/  buttons, forms, navigation, media, galleries
utilities/   accessibility, alignments
```

`main.css` состоит только из `@import` — их порядок и есть порядок каскада, менять осознанно. Vite инлайнит импорты на этапе сборки, поэтому в `assets/dist/css/main.css` попадает один файл без дополнительных сетевых запросов. Вложенности нет: селекторы записаны плоско, чтобы CSS оставался предсказуемым по специфичности и работал без транспиляции.

## Картинки темы

Касается только картинок, лежащих внутри темы. Медиатеку WordPress — изображения из постов, страниц и ACF-полей — эта сборка не трогает.

```text
assets/images/   — исходники (png, jpg, svg), правятся руками
assets/webp/     — конвертированные версии, зеркалят структуру исходников
```

Конвертирует `npm run images` (вшит в `npm run build` и `npm run dev`). Сборка инкрементальная: пересобирается только то, что новее своего webp, а webp без исходника подчищается. Рядом пишется `assets/webp/manifest.json` с размерами каждой картинки — из него PHP берёт `width`/`height`, чтобы не дёргать `getimagesize()` на каждый рендер и чтобы вёрстка не прыгала при загрузке.

`assets/webp/` **коммитится**. Тема часто уезжает на шаред-хостинг, где сборку никто не запускает, а картинки меняются редко — дешевле держать их в репозитории, чем требовать Node на проде.

### В шаблонах

```php
wp_frame_lite_image( 'hero.jpg', array(
	'alt'           => 'Первый экран',
	'loading'       => 'eager',       // по умолчанию lazy
	'fetchpriority' => 'high',
	'class'         => 'hero-bg',
) );
```

```html
<picture>
  <source srcset=".../assets/webp/hero.webp" type="image/webp" />
  <img
    src=".../assets/images/hero.jpg"
    alt="Первый экран"
    loading="eager"
    decoding="async"
    fetchpriority="high"
    class="hero-bg"
    width="1200"
    height="630"
  />
</picture>
```

`width`/`height` подставляются из манифеста, если не заданы явно; `null` в атрибуте убирает его. Для SVG и всего, у чего нет webp-версии, возвращается обычный `<img>` без обёртки. Если исходника нет на диске — пустая строка.

Ещё есть `wp_frame_lite_get_image()` (то же самое, но возвращает строку), `wp_frame_lite_image_url()` и `wp_frame_lite_webp_url()`.

### В CSS

```css
.hero {
  background-image: image-set(
    url("../webp/hero.webp") type("image/webp"),
    url("../images/hero.jpg") type("image/jpeg")
  );
}
```

Vite подхватит оба файла, положит в `assets/dist/img/` и перепишет пути; autoprefixer добавит `-webkit-image-set` для старых Safari.

### Почему `<picture>`, а не определение по заголовку Accept

Серверное определение поддержки webp несовместимо с кэшированием страниц. При включённом W3TC или Cloudflare закэшированный HTML отдаётся всем одинаковый — первый зашедший браузер зафиксировал бы выбор формата для всех остальных. `<picture>` и `image-set()` решают это на стороне браузера и переживают любой кэш и CDN, не требуя ни строчки в конфиге сервера.

## Слайдеры и лайтбокс

В теме лежат **Swiper 11** и **GLightbox 3** — файлами в `assets/vendor`, не пакетами npm. Вместе они весят около 200 кБ против 15 кБ собственной сборки, поэтому подключаются **не всегда, а по требованию**:

```php
add_action( 'wp_enqueue_scripts', function () {
	if ( is_front_page() ) {
		wp_frame_lite_use( 'swiper', 'glightbox' );
	}
} );
```

Вызывать нужно именно на `wp_enqueue_scripts`. Если дёрнуть функцию из шаблона, `wp_head` к тому моменту уже отработает, стили уедут в подвал и страница мигнёт нестилизованным блоком — при включённом `WP_DEBUG` функция об этом предупредит.

Порядок вывода скриптов при этом не важен: инициализация в `main.js` ждёт `DOMContentLoaded`, к которому все классические скрипты документа уже выполнены.

### Слайдер

```html
<div class="swiper js-slider" data-slider='{"loop":true,"slidesPerView":3}'>
  <div class="swiper-wrapper">
    <div class="swiper-slide">…</div>
  </div>
  <div class="swiper-pagination"></div>
  <button class="swiper-button-prev" type="button"></button>
  <button class="swiper-button-next" type="button"></button>
</div>
```

`data-slider` необязателен и принимает любые параметры Swiper в виде JSON. Стрелки и пагинация подключаются сами, если их разметка лежит внутри слайдера — во вложенных слайдерах элементы управления ищутся только свои. Экземпляр доступен как `document.querySelector('.js-slider').swiper`.

Подписи для скринридеров переведены на русский: Swiper по умолчанию ставит английские («Previous slide», «Go to slide 1»).

Цвета стрелок и точек привязаны к токенам темы в `components/slider.css` через переменные `--swiper-*`, отдельно перекрашивать ничего не нужно.

### Лайтбокс

```html
<a href="/full.jpg" class="glightbox" data-gallery="services">
  <img src="/thumb.jpg" alt="" />
</a>
```

Ссылки с одинаковым `data-gallery` листаются как одна галерея.

Известная особенность: тип слайда GLightbox определяет **по расширению в URL**. Если ссылка ведёт на файл без расширения, библиотека клик не перехватит и браузер просто уйдёт по ссылке. Лечится явным типом:

```html
<a href="/download?id=12" class="glightbox" data-type="image"></a>
```

Если разметка на странице есть, а библиотека не подключена, модули пишут об этом предупреждение в консоль и молча выходят — вёрстка не ломается.

## Разработка

```sh
npm install
npm run dev
```

`npm run dev` конвертирует картинки и запускает Vite в режиме watch. `npm run build` делает то же плюс минификацию и преджатие:

```sh
npm run build
```

Отдельными шагами, если нужно:

```sh
npm run images     # только конвертация картинок
npm run compress   # только .br и .gz для готовой сборки
```

PHP-линтеры ставятся через composer:

```sh
composer install
composer lint:wpcs
composer lint:php
```

`composer lint:wpcs` — WordPress Coding Standards 3.x, `lint:php` — параллельная проверка синтаксиса. Часть замечаний PHPCS чинится автоматически:

```sh
./vendor/bin/phpcbf
```

В `phpcs.xml.dist` два осознанных послабления:

- ruleset `WPThemeReview` убран — пакет `wptrt/wpthemereview` не обновлялся с 2020 года и тянул PHPCS 3.5, который не разбирает union-типы вроде `int|string|null`;
- сниф `PrefixAllGlobals.NonPrefixedVariableFound` проверяет только `inc/`. В шаблонах он даёт ложные срабатывания: WordPress подключает их через `load_template()`, то есть внутри функции, так что переменные вида `$footer_phones` глобальными не являются — а сниф требует префикс у каждой.

### Сборка и деплой

**`assets/dist/` коммитится в репозиторий** — это осознанное решение, а не недосмотр. Тема уезжает на шаред-хостинг, где Node никто не запускает, а подключается она только из `dist`: без собранных файлов сайт остался бы без стилей и скриптов. По той же причине коммитится `assets/webp/`.

Отсюда одно правило: **перед коммитом прогоняйте `npm run build`.** Иначе на прод уедет старая сборка. Карты исходников (`*.map`) из репозитория исключены, обе папки помечены в `.gitattributes` как сгенерированные, чтобы GitHub сворачивал их диффы.

Если на проекте есть CI, разумнее наоборот: вернуть `assets/dist/` в `.gitignore` и собирать на деплое.

### Отдача преджатых файлов

`npm run compress` кладёт рядом с бандлом `.br` и `.gz`, но **сами по себе они ничего не ускоряют** — их должен отдавать веб-сервер.

nginx:

```nginx
brotli_static on;
gzip_static on;
```

Apache — в `.htaccess` сайта (не темы):

```apache
<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteCond %{HTTP:Accept-Encoding} br
	RewriteCond %{REQUEST_FILENAME}.br -f
	RewriteRule ^(.*)$ $1.br [QSA,L]
	RewriteCond %{HTTP:Accept-Encoding} gzip
	RewriteCond %{REQUEST_FILENAME}.gz -f
	RewriteRule ^(.*)$ $1.gz [QSA,L]
</IfModule>
<FilesMatch "\.css\.(br|gz)$">
	ForceType text/css
	Header append Vary Accept-Encoding
</FilesMatch>
<FilesMatch "\.js\.(br|gz)$">
	ForceType application/javascript
	Header append Vary Accept-Encoding
</FilesMatch>
<IfModule mod_headers.c>
	<FilesMatch "\.br$">
		Header set Content-Encoding br
	</FilesMatch>
	<FilesMatch "\.gz$">
		Header set Content-Encoding gzip
	</FilesMatch>
</IfModule>
```

Если хостинг ничего из этого не умеет, шаг `compress` можно просто убрать из `build` — на работу темы он не влияет.

## Формат кода

Зоны ответственности разведены, чтобы инструменты не переписывали файлы друг за другом:

| Что                        | Чем                                     |
| -------------------------- | --------------------------------------- |
| Форматирование PHP         | DEVSENSE PHP Tools, пресет WordPress    |
| Анализ PHP                 | Intelephense + стабы WordPress          |
| Форматирование CSS, JS, MD | Prettier                                |
| Качество CSS               | stylelint (`stylelint-config-standard`) |
| Отступы, EOL               | `.editorconfig`                         |

```sh
npm run lint:css        # проверить CSS
npm run lint:css:fix    # починить, что чинится автоматически
npm run format          # прогнать Prettier по проекту
npm run format:check    # проверить без записи
```

Stylelint форматированием не занимается: правила `*-empty-line-before` в `.stylelintrc.json` отключены намеренно — они конфликтуют с Prettier за пустые строки после `{`.

Ещё два правила выключены осознанно:

- `import-notation` — конфиг по умолчанию требует `@import url("x.css")`, тема использует более современную запись строкой;
- `media-feature-range-notation` — конфиг требует `(width < 992px)` вместо `(max-width: 991.98px)`. Запись читается лучше, но не понимается Safari до 16.4 и Chrome до 104: там медиазапрос был бы проигнорирован целиком и на старом телефоне развернулось бы десктопное меню. Оставлена совместимая форма.

Изначально брался `@wordpress/stylelint-config`, но в 24-й версии он тянет `@wordpress/theme`, а тот — React, react-dom и часть пакетов Гутенберга. Для конфига линтера CSS это 58 лишних пакетов и постоянные предупреждения npm про peer-зависимости React, поэтому заменён на `stylelint-config-standard`.

### Настройка редактора

`.vscode/settings.json` и `.vscode/extensions.json` лежат в репозитории — форматирование при сохранении заводится само после установки рекомендованных расширений (VS Code предложит их при открытии проекта).

PHP-расширений в системе обычно стоит несколько, и каждое хочет и форматировать, и анализировать. Роли разведены явно, иначе получаются дублирующиеся подчёркивания и вопрос «каким форматтером сохранять?» на каждом Ctrl+S:

- **DEVSENSE PHP Tools** форматирует (`php.problems.scope: "none"`) — из трёх он единственный корректно держит смесь PHP и HTML в шаблонах;
- **Intelephense** анализирует (`intelephense.format.enable: false`).

Функции ядра WordPress подключены через стаб `wordpress` в `intelephense.stubs` — без него `get_bloginfo`, `esc_html`, `wp_nav_menu` и остальные подчёркиваются как неизвестные. Массив в настройке **замещает** значение по умолчанию, поэтому там перечислены все штатные стабы: добавляя свои, дописывайте в конец и ничего не удаляйте.

Функции ACF ядром не покрываются — плагин подключён отдельно через `intelephense.environment.includePaths`. Путь относительный от папки темы и рассчитан на стандартную установку WordPress; если ACF лежит иначе, поправьте его под себя.

Если формат-он-сейв ломает конкретный шаблон со сложной смесью PHP и HTML — исключите файл, не отключая всё:

```json
"php.format.exclude": ["**/header.php"]
```
