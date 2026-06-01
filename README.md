# wp-frame-lite

Стартовая WordPress-тема для клиентских проектов. Автор: [Inveris](https://github.com/kirillye)

## Стек

- ACF (Advanced Custom Fields) — обязательный плагин
- Yoast SEO — обязательный плагин
- Только русский язык
- Комментарии полностью отключены

## Структура

```
assets/
  scss/
    base/
      _variables.scss     — цвета, шрифты, breakpoints
      _reset.scss         — сброс стилей
      _typography.scss    — типографика, ссылки, таблицы
    components/
      _buttons.scss
      _forms.scss
      _navigation.scss
      _widgets.scss
      _media.scss
      _galleries.scss
    layout/
      _layout.scss        — .site, .container
      _header.scss
      _footer.scss
      _posts.scss
    utilities/
      _accessibility.scss
      _alignments.scss
    main.scss             — точка входа, импортирует всё
  js/
    main.js               — точка входа JS
  dist/                   — генерируется при сборке (в .gitignore)
inc/
  admin.php
  login.php
  security.php
page-templates/
  full-width.php          — страница без сайдбара
template-parts/
  content.php
  content-none.php
  content-page.php
```

## Разработка

```sh
npm install
npm run dev    # следит за изменениями и пересобирает
npm run build  # разовая продакшн-сборка
```

```sh
composer install
composer lint:wpcs    # проверка PHP Coding Standards
composer lint:php     # синтаксис PHP
composer make-pot     # генерация .pot файла
```
