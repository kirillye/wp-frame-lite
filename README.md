# wp-frame-lite

Стартовая WordPress-тема для клиентских проектов. Автор: [Inveris](https://github.com/kirillye)

## Стек

- ACF (Advanced Custom Fields) — обязательный плагин
- Yoast SEO — обязательный плагин
- Только русский язык
- Комментарии полностью отключены

## Структура

```
inc/
  custom-header.php   — поддержка кастомного заголовка
  customizer.php      — настройки кастомайзера
  template-functions.php — вспомогательные функции
  template-tags.php   — теги шаблона (дата, автор, рубрики)
js/
  navigation.js       — адаптивное меню
template-parts/
  content.php         — пост в списке/на странице
  content-none.php    — заглушка «ничего не найдено»
  content-page.php    — статическая страница
  content-search.php  — результат поиска
```

## Разработка

```sh
npm install
npm run compile:css   # компиляция SASS → CSS
npm run watch         # слежение за изменениями
```

```sh
composer install
composer lint:wpcs    # проверка PHP Coding Standards
composer lint:php     # синтаксис PHP
composer make-pot     # генерация .pot файла
```
