# wp-frame-lite

Стартовая WordPress-тема для клиентских проектов. Тема рассчитана на кастомную разработку под конкретного клиента и не тянет SEO-плагины как обязательные зависимости.

## Стек

- Advanced Custom Fields - обязательный плагин.
- ACF JSON - обязательная часть темы, директория синхронизации: `acf-json/`.
- Комментарии отключены.
- Поиск отключен по умолчанию.
- SEO-плагин не обязателен: можно использовать любой подходящий SEO-плагин или штатный `title-tag`.

## Структура

```text
acf-json/               - ACF JSON sync
assets/
  js/main.js            - точка входа JS
  scss/main.scss        - точка входа SCSS
  dist/                 - production-сборка Vite
inc/
  acf-fields.php        - ACF JSON и ACF helpers
  admin.php             - админ-утилиты
  assets.php            - подключение CSS/JS
  cleanup.php           - очистка head/emoji/XML-RPC
  comments.php          - отключение комментариев
  search.php            - отключение поиска
  security.php          - базовое укрепление
  setup.php             - theme support и меню
page-templates/
template-parts/
```

## Разработка

```sh
npm install
npm run dev
npm run build
```

```sh
composer install
composer lint:wpcs
composer lint:php
composer make-pot
```
