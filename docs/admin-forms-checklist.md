# Чеклист: админские формы создания/редактирования

Эталон — форма товара (`admin/products/form.blade.php`). Привести все формы к одному виду.

## Шапка формы

- [ ] Форма оборачивает шапку (form в начале)
- [ ] Шапка: `flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4`
- [ ] Слева: кнопка «К списку» (стрелка) + иконка сущности + заголовок + подзаголовок
- [ ] Справа: кнопки «Сохранить» и «Отмена» (в одной строке с заголовком)
- [ ] При редактировании: подзаголовок — ссылка на просмотр на сайте (если есть маршрут), `target="_blank"`, иконка/цвет sky-600
- [ ] При создании: подзаголовок — текст «Заполните поля» или аналогичный

**Сущности с публичной страницей:**
| Форма | Маршрут | Поле для подзаголовка |
|-------|---------|------------------------|
| products | `products.show` | title |
| services | `services.show` | title |
| news | `news.show` | title |
| categories | `products.index` + `category` | name |
| banners | — | нет страницы |
| users | — | нет страницы |

## Секции и поля

- [ ] Использовать `<x-admin.form-section title="..." icon="...">` для каждой секции
- [ ] Парные поля (2 колонки) — обёртка: `<div class="grid grid-cols-1 lg:grid-cols-2 gap-x-6 gap-y-4">`
- [ ] Кастомные label (select, textarea): `min-h-[1.5rem]`, иконка `text-sky-500 shrink-0`
- [ ] Select: `h-11`, обёртка `flex flex-col`
- [ ] Чекбоксы: блок с `flex flex-wrap items-center gap-6`, без `pt-1`
- [ ] File input: компонент `<x-ui.file-input>` (кастомная кнопка, без outline при hover)

## Layout секций

- [ ] Две колонки на десктопе: `grid grid-cols-1 lg:grid-cols-2 gap-4` для карточек секций
- [ ] Секция на всю ширину: без обёртки в 2 колонки
- [ ] form-section внутри использует `space-y-4` (не grid), группировка полей — через вложенный grid

## Кнопки внизу

- [ ] Убрать — перенесены в шапку справа

## Список форм для правки

| Форма | Файл | Статус |
|-------|------|--------|
| Товар | `admin/products/form.blade.php` | ✅ эталон |
| Категория | `admin/categories/form.blade.php` | ✅ |
| Услуга | `admin/services/form.blade.php` | ✅ |
| Новость | `admin/news/form.blade.php` | ✅ |
| Баннер | `admin/banners/form.blade.php` | ✅ |
| Пользователь | `admin/users/form.blade.php` | 🔲 |

## Детали по формам

### categories ✅
- Форма оборачивает шапку, кнопки справа
- Ссылка при редактировании: `route('products.index', ['category' => $category->slug])` — каталог с фильтром
- Секция: одна — Категория (name, slug, parent, description, image, is_featured)
- File-input с existing-url и lightbox-group

### services ✅
- Форма оборачивает шапку, кнопки справа
- Ссылка при редактировании: `route('services.show', $service)`, текст — title
- Секции: Основное (title, slug, price, type, description), Изображения — обе на всю ширину
- Иконки text-sky-500, select h-11, flex flex-col

### news ✅
- Форма оборачивает шапку, кнопки справа
- Ссылка при редактировании: `route('news.show', $news)`, текст — title
- Секции: Основное (title, slug, video_url, published_at, description, content), Изображения, Просмотры (если есть) — на всю ширину
- Иконки text-sky-500, группировка: title|slug, video_url|published_at

### banners ✅
- Форма оборачивает шапку, кнопки справа
- Ссылки на сайт нет (баннеры на главной)
- Секция: одна — Баннер (title, link, description, image, active) на всю ширину
- Иконки text-sky-500, группировка: title|link

### users
- Ссылка на сайт: нет
- Секции: Контакты, Пароль, Права
- Layout: 2 колонки (Контакты | Пароль), Права на всю ширину
