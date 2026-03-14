# MVP - Главная страница с моковыми данными

## Цель MVP

Создать визуально готовую главную страницу со всеми основными элементами интерфейса, используя моковые данные. Это позволит:
- Показать заказчику визуальную концепцию
- Протестировать дизайн и UX
- Получить обратную связь до полной разработки
- Ускорить дальнейшую разработку (компоненты уже готовы)

---

## Шаг 0: Локальная настройка окружения

**Требования:** PHP 8.2+, Composer, Node.js 18+, MySQL 8.0 (или SQLite для MVP).

### Задачи:

#### 0.1 Установить зависимости
```bash
composer install
npm install
```

#### 0.2 Настроить окружение
```bash
cp .env.example .env
php artisan key:generate
```
Для MVP можно использовать SQLite (по умолчанию в Laravel). Для MySQL — отредактировать `.env`:
- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_DATABASE=ulplay`
- `DB_USERNAME=...`, `DB_PASSWORD=...`

#### 0.3 Собрать assets
```bash
npm run build
```

#### 0.4 Запустить проект
```bash
php artisan serve
```
Открыть **http://localhost:8000**

### Альтернатива: Docker (опционально)
При необходимости Docker-конфигурация есть в `docker-compose.yml`. Подробности — в README.

---

## Структура главной страницы

### 1. Header (Шапка сайта)

#### Элементы:
- **Логотип** (слева) - текст "UlPlay" или изображение
- **Навигационное меню** (центр):
  - PlayStation
  - Xbox
  - Услуги
  - Новости
  - Контакты
- **Правая часть:**
  - Иконка корзины с счетчиком (мок: "3")
  - Иконка пользователя (если не авторизован - "Войти")
  - Иконка закладок (опционально)

#### Стиль:
- Фон: `bg-white`
- Высота: фиксированная
- Тень снизу: `shadow-sm` или `border-b border-slate-200`
- Адаптивное меню (бургер на мобильных)

#### Моковые данные:
```php
$cartCount = 3; // Моковое количество товаров в корзине
$isAuthenticated = false; // Моковый статус авторизации
```

---

### 2. Hero Section (Баннер/Слайдер)

#### Элементы:
- **Слайдер баннеров** (3-4 баннера):
  - Изображение
  - Заголовок
  - Описание (опционально)
  - Кнопка "Подробнее" или "Смотреть каталог"
- **Навигация слайдера:**
  - Стрелки влево/вправо
  - Индикаторы точек внизу

#### Стиль:
- Полная ширина экрана
- Высота: `h-96` (384px) на десктопе, `h-64` на мобильных
- Затемнение изображения для читаемости текста (опционально)

#### Моковые данные:
```php
$banners = [
    [
        'id' => 1,
        'title' => 'Новые поступления PlayStation 4',
        'description' => 'Широкий выбор консолей и аксессуаров',
        'image' => '/images/banners/ps4-banner.jpg',
        'link' => '/products?category=playstation-4'
    ],
    [
        'id' => 2,
        'title' => 'Ремонт игровых приставок',
        'description' => 'Профессиональный ремонт любой сложности',
        'image' => '/images/banners/repair-banner.jpg',
        'link' => '/services/repair'
    ],
    [
        'id' => 3,
        'title' => 'Купим ваше устройство',
        'description' => 'Выгодная сдача игровых консолей',
        'image' => '/images/banners/buy-banner.jpg',
        'link' => '/services/buy'
    ]
];
```

---

### 3. Категории (Быстрая навигация)

#### Элементы:
- Заголовок секции: "Категории"
- Сетка категорий (4-6 категорий):
  - Иконка или изображение категории
  - Название категории
  - Количество товаров (мок)

#### Стиль:
- Сетка: `grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4`
- Карточки категорий: белый фон, граница, ховер эффект

#### Моковые данные:
```php
$categories = [
    ['id' => 1, 'name' => 'PlayStation 4', 'slug' => 'playstation-4', 'image' => '/images/categories/ps4.jpg', 'count' => 14],
    ['id' => 2, 'name' => 'PlayStation 3', 'slug' => 'playstation-3', 'image' => '/images/categories/ps3.jpg', 'count' => 13],
    ['id' => 3, 'name' => 'Xbox ONE', 'slug' => 'xbox-one', 'image' => '/images/categories/xbox-one.jpg', 'count' => 0],
    ['id' => 4, 'name' => 'Xbox 360', 'slug' => 'xbox-360', 'image' => '/images/categories/xbox-360.jpg', 'count' => 11],
    ['id' => 5, 'name' => 'Аксессуары', 'slug' => 'accessories', 'image' => '/images/categories/accessories.jpg', 'count' => 15],
    ['id' => 6, 'name' => 'Игры', 'slug' => 'games', 'image' => '/images/categories/games.jpg', 'count' => 27],
];
```

---

### 4. Новые товары

#### Элементы:
- Заголовок секции: "Новые поступления"
- Сетка товаров (4-6 товаров):
  - Изображение товара
  - Название
  - Цена
  - Кнопка "В корзину"
  - Бейдж "Новинка" (опционально)

#### Стиль:
- Сетка: `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6`
- Карточки товаров по дизайн-системе

#### Моковые данные:
```php
$newProducts = [
    [
        'id' => 1,
        'title' => 'Dobe Зарядная станция для геймпадов PS4 Dualshock 4',
        'slug' => 'dobe-charging-station-ps4',
        'price' => 899,
        'image' => '/images/products/dobe-charging-station.jpg',
        'in_stock' => true,
        'is_new' => true
    ],
    [
        'id' => 2,
        'title' => 'Белые беспроводные геймпады DualShock 3 для PS3',
        'slug' => 'white-dualshock-3-ps3',
        'price' => 1499,
        'image' => '/images/products/dualshock-3-white.jpg',
        'in_stock' => true,
        'is_new' => true
    ],
    [
        'id' => 3,
        'title' => 'Dobe Подставка для PlayStation 4 Slim/Pro',
        'slug' => 'dobe-stand-ps4',
        'price' => 899,
        'image' => '/images/products/dobe-stand.jpg',
        'in_stock' => true,
        'is_new' => true
    ],
    [
        'id' => 4,
        'title' => 'Геймпад Microsoft Xbox 360 черный',
        'slug' => 'xbox-360-controller-black',
        'price' => 1999,
        'image' => '/images/products/xbox-360-controller.jpg',
        'in_stock' => true,
        'is_new' => false
    ],
    // ... еще 2-4 товара
];
```

---

### 5. Популярные товары / Рекомендуемые

#### Элементы:
- Заголовок секции: "Рекомендуемые"
- Сетка товаров (аналогично новым товарам)
- Можно использовать те же карточки

#### Моковые данные:
```php
$recommendedProducts = [
    [
        'id' => 5,
        'title' => 'Геймпад черный MICROSOFT TF5-01708, для Xbox One',
        'slug' => 'xbox-one-controller',
        'price' => 3499,
        'image' => '/images/products/xbox-one-controller.jpg',
        'in_stock' => true
    ],
    // ... еще товары
];
```

---

### 6. Услуги

#### Элементы:
- Заголовок секции: "Наши услуги"
- Сетка услуг (2-3 услуги):
  - Изображение услуги
  - Название
  - Краткое описание
  - Цена (если есть) или "От 0₽"
  - Кнопка "Подробнее" или "Заказать"

#### Стиль:
- Сетка: `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6`
- Карточки услуг (отличаются от товаров - больше описания)

#### Моковые данные:
```php
$services = [
    [
        'id' => 1,
        'title' => 'Ремонт игровых приставок',
        'slug' => 'repair',
        'description' => 'Профессиональный ремонт PlayStation и Xbox любой сложности. Гарантия на работы.',
        'price' => null, // или 'От 0₽'
        'type' => 'repair',
        'image' => '/images/services/repair.jpg'
    ],
    [
        'id' => 2,
        'title' => 'Купим ваше устройство',
        'slug' => 'buy',
        'description' => 'Выгодная сдача игровых консолей, аксессуаров и игр. Быстрая оценка и оплата.',
        'price' => null,
        'type' => 'buy',
        'image' => '/images/services/buy.jpg'
    ],
];
```

---

### 7. Новости / Блог

#### Элементы:
- Заголовок секции: "Последние новости"
- Сетка новостей (3-4 новости):
  - Изображение новости
  - Дата публикации
  - Заголовок
  - Краткое описание (1-2 строки)
  - Кнопка "Читать далее"

#### Стиль:
- Сетка: `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6`
- Карточки новостей (горизонтальный или вертикальный layout)

#### Моковые данные:
```php
$news = [
    [
        'id' => 1,
        'title' => 'Новые поступления PlayStation 4',
        'slug' => 'new-ps4-arrivals',
        'description' => 'В нашем магазине появились новые модели PlayStation 4 и аксессуары к ним.',
        'image' => '/images/news/ps4-news.jpg',
        'published_at' => '2024-01-15',
        'author' => 'Администратор'
    ],
    [
        'id' => 2,
        'title' => 'Обновление прайс-листа на ремонт',
        'slug' => 'repair-price-update',
        'description' => 'Мы обновили прайс-лист на ремонт игровых приставок. Теперь еще выгоднее!',
        'image' => '/images/news/repair-news.jpg',
        'published_at' => '2024-01-10',
        'author' => 'Администратор'
    ],
    [
        'id' => 3,
        'title' => 'Акция: скидка 10% на все аксессуары',
        'slug' => 'accessories-sale',
        'description' => 'Специальное предложение на все аксессуары для игровых консолей до конца месяца.',
        'image' => '/images/news/sale-news.jpg',
        'published_at' => '2024-01-05',
        'author' => 'Администратор'
    ],
];
```

---

### 8. Footer (Подвал)

#### Элементы:
- **Верхняя часть (3-4 колонки):**
  - О компании / Информация
  - Каталог (ссылки на категории)
  - Услуги
  - Контакты
- **Нижняя часть:**
  - Копирайт
  - Социальные сети (иконки)
  - Политика конфиденциальности (ссылка)

#### Стиль:
- Фон: `bg-slate-900` или `bg-slate-800`
- Текст: `text-slate-300`
- Ссылки: `hover:text-white`

#### Моковые данные:
```php
$footerData = [
    'company' => [
        'name' => 'UlPlay',
        'description' => 'Интернет-магазин игровых консолей и аксессуаров в Ульяновске',
        'phone' => '+7 (927) 988-88-70',
        'email' => 'info@ulplay.com'
    ],
    'categories' => [
        ['name' => 'PlayStation', 'url' => '/products?category=playstation'],
        ['name' => 'Xbox', 'url' => '/products?category=xbox'],
        ['name' => 'Аксессуары', 'url' => '/products?category=accessories'],
        ['name' => 'Игры', 'url' => '/products?category=games'],
    ],
    'services' => [
        ['name' => 'Ремонт', 'url' => '/services/repair'],
        ['name' => 'Купим ваше', 'url' => '/services/buy'],
    ],
    'links' => [
        ['name' => 'О нас', 'url' => '/about'],
        ['name' => 'Доставка и оплата', 'url' => '/delivery'],
        ['name' => 'Контакты', 'url' => '/contacts'],
    ],
    'social' => [
        ['name' => 'VK', 'url' => '#', 'icon' => 'vk'],
        ['name' => 'Telegram', 'url' => '#', 'icon' => 'telegram'],
    ]
];
```

---

## Технические детали MVP

### Файлы для создания

#### Backend (Laravel)
```
app/Http/Controllers/HomeController.php
app/Models/Banner.php (модель для будущего)
resources/views/home.blade.php
resources/views/components/product-card.blade.php
resources/views/components/service-card.blade.php
resources/views/components/news-card.blade.php
resources/views/layouts/app.blade.php (header + footer)
```

#### Frontend
```
resources/css/app.css (TailwindCSS)
public/images/ (папка для изображений)
```

### Моковые данные

#### Вариант 1: В контроллере (проще для MVP)
```php
// app/Http/Controllers/HomeController.php
public function index()
{
    return view('home', [
        'banners' => $this->getMockBanners(),
        'categories' => $this->getMockCategories(),
        'newProducts' => $this->getMockNewProducts(),
        'recommendedProducts' => $this->getMockRecommendedProducts(),
        'services' => $this->getMockServices(),
        'news' => $this->getMockNews(),
        'cartCount' => 3, // Мок
    ]);
}

private function getMockBanners() { /* ... */ }
private function getMockCategories() { /* ... */ }
// и т.д.
```

#### Вариант 2: Seeder (ближе к реальности)
```php
// database/seeders/MockDataSeeder.php
// Создать моковые данные в БД через seeder
```

### Изображения

Для MVP можно использовать:
- **Placeholder изображения:** Unsplash, Pexels (бесплатные)
- **Иконки:** Heroicons (бесплатные SVG)
- **Логотип:** Простой текст или SVG

Рекомендуемые размеры:
- Баннеры: 1920x600px
- Товары: 400x400px
- Услуги: 600x400px
- Новости: 600x400px
- Категории: 200x200px

---

## Чеклист MVP

### Окружение
- [ ] PHP 8.2+, Composer, Node.js 18+ установлены
- [ ] `composer install`
- [ ] `npm install` и `npm run build`
- [ ] `.env` создан, `APP_KEY` сгенерирован

### Backend
- [ ] Создать `HomeController` с методом `index()`
- [ ] Добавить моковые данные в контроллер
- [ ] Создать роут `/` для главной страницы
- [ ] Подготовить структуру Blade шаблонов

### Frontend
- [ ] Создать layout `app.blade.php` (header + footer)
- [ ] Создать компоненты:
  - [ ] `product-card.blade.php`
  - [ ] `service-card.blade.php`
  - [ ] `news-card.blade.php`
  - [ ] `category-card.blade.php`
- [ ] Создать `home.blade.php` с всеми секциями
- [ ] Настроить TailwindCSS
- [ ] Подключить Alpine.js (для слайдера и меню)
- [ ] Подключить Swiper.js (для слайдера баннеров)

### Дизайн
- [ ] Реализовать Header согласно дизайн-системе
- [ ] Реализовать Hero Section (слайдер)
- [ ] Реализовать секцию категорий
- [ ] Реализовать секцию новых товаров
- [ ] Реализовать секцию рекомендуемых товаров
- [ ] Реализовать секцию услуг
- [ ] Реализовать секцию новостей
- [ ] Реализовать Footer

### Адаптивность
- [ ] Проверить на мобильных устройствах
- [ ] Проверить на планшетах
- [ ] Проверить на десктопе
- [ ] Адаптивное меню (бургер)

### Функциональность (минимальная)
- [ ] Слайдер баннеров работает (Swiper.js)
- [ ] Мобильное меню открывается/закрывается (Alpine.js)
- [ ] Все ссылки ведут на правильные страницы (даже если страниц еще нет)
- [ ] Кнопки "В корзину" показывают уведомление (мок)

---

## Временная оценка MVP

| Задача | Время |
|--------|-------|
| **ШАГ 0: Локальная настройка** | **30-60 мин** |
| Настройка TailwindCSS и базового layout | 2-3 часа |
| Создание Header | 2-3 часа |
| Создание Footer | 1-2 часа |
| Hero Section (слайдер) | 2-3 часа |
| Секция категорий | 1-2 часа |
| Секция товаров (карточки) | 3-4 часа |
| Секция услуг | 2-3 часа |
| Секция новостей | 2-3 часа |
| Адаптивность и полировка | 3-4 часа |
| **ИТОГО** | **21-30 часов** (~3-4 дня) |

---

## Следующие шаги после MVP

1. **Получить обратную связь** от заказчика
2. **Внести правки** в дизайн при необходимости
3. **Начать разработку** остальных страниц (каталог, товар, корзина и т.д.)
4. **Подключить реальные данные** из БД вместо моковых

---

## Пример структуры Blade шаблона

```blade
{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('content')
    {{-- Hero Section --}}
    @include('home.sections.hero', ['banners' => $banners])

    {{-- Categories --}}
    @include('home.sections.categories', ['categories' => $categories])

    {{-- New Products --}}
    @include('home.sections.new-products', ['products' => $newProducts])

    {{-- Recommended Products --}}
    @include('home.sections.recommended', ['products' => $recommendedProducts])

    {{-- Services --}}
    @include('home.sections.services', ['services' => $services])

    {{-- News --}}
    @include('home.sections.news', ['news' => $news])
@endsection
```

---

**Дата создания:** 2024  
**Версия:** 1.0  
**Статус:** План для разработки MVP главной страницы
