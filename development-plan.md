# План разработки сайта ulplay.com

## О проекте

**Тематика:** Интернет-магазин игровых консолей, аксессуаров и услуг ремонта  
**Основные категории:** PlayStation (PS2, PS3, PS4, PS Vita, PSP), Microsoft Xbox (Xbox 360, Xbox ONE), аксессуары, игры, услуги ремонта  
**Регион:** Ульяновск, Россия  
**Особенности:** Продажа новых и б/у товаров, услуги ремонта игровых приставок, покупка использованных устройств

> 📐 **Дизайн-система:** Подробная цветовая палитра, типографика и компоненты описаны в файле [`design.md`](design.md)  
> 🚀 **MVP план:** План разработки главной страницы с моковыми данными описан в файле [`mvp.md`](mvp.md)

---

## Технологический стек

### Backend
- **Laravel 11** (PHP 8.2+)
- **MySQL** (база данных)
- **Laravel Sanctum** (авторизация, опционально для будущего API)
- **Файловый кэш** (без Redis)
- **Database очереди** (без Horizon)

### Frontend
- **Blade шаблоны** (серверный рендеринг)
- **TailwindCSS** (стилизация)
- **Alpine.js** (легкая интерактивность, опционально)
- **Vite** (только для компиляции CSS)
- **Swiper.js** (для слайдеров, через CDN)

### Окружение
- **Локально:** PHP 8.2+, Composer, Node.js 18+, MySQL
- **Docker** — опционально (есть `docker-compose.yml`)

---

## Структура проекта

```
ulplay/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── ProductController.php
│   │   │   ├── ServiceController.php
│   │   │   ├── NewsController.php
│   │   │   ├── CommentController.php
│   │   │   ├── CartController.php
│   │   │   ├── OrderController.php
│   │   │   ├── ProfileController.php
│   │   │   └── Admin/
│   │   │       ├── ProductController.php
│   │   │       ├── ServiceController.php
│   │   │       ├── NewsController.php
│   │   │       ├── CommentController.php
│   │   │       └── UserController.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Service.php
│   │   ├── News.php
│   │   ├── Comment.php
│   │   ├── Category.php
│   │   ├── Tag.php
│   │   ├── CartItem.php
│   │   ├── Order.php
│   │   └── Banner.php
│   └── Services/
│       ├── CartService.php
│       └── OrderService.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php
│   │   │   └── admin.blade.php
│   │   ├── components/
│   │   │   ├── product-card.blade.php
│   │   │   ├── service-card.blade.php
│   │   │   └── cart-counter.blade.php
│   │   ├── auth/
│   │   │   ├── login.blade.php
│   │   │   └── register.blade.php
│   │   ├── products/
│   │   │   ├── index.blade.php
│   │   │   └── show.blade.php
│   │   ├── services/
│   │   │   ├── index.blade.php
│   │   │   └── show.blade.php
│   │   ├── news/
│   │   │   ├── index.blade.php
│   │   │   └── show.blade.php
│   │   ├── cart/
│   │   │   └── index.blade.php
│   │   ├── orders/
│   │   │   ├── create.blade.php
│   │   │   └── show.blade.php
│   │   ├── profile/
│   │   │   └── index.blade.php
│   │   ├── admin/
│   │   │   ├── products/
│   │   │   ├── services/
│   │   │   ├── news/
│   │   │   ├── comments/
│   │   │   └── users/
│   │   └── home.blade.php
│   └── css/
│       └── app.css
└── routes/
    ├── web.php
    └── admin.php
```

---

## Этапы разработки

### Этап 0: Локальная настройка (30-60 мин)

#### 0.1 Установка зависимостей
- [ ] `composer install`
- [ ] `npm install` и `npm run build`
- [ ] Копировать `.env.example` в `.env`, сгенерировать `APP_KEY`

#### 0.2 Запуск
- [ ] `php artisan serve` — http://localhost:8000

**Результат:** Рабочее локальное окружение

---

### Этап 1: Базовая настройка проекта (1-2 дня)

#### 1.1 Установка и настройка Laravel
- [ ] Laravel 11 установлен
- [ ] Настройка `.env` (MySQL или SQLite)
- [ ] Настройка TailwindCSS через Vite
- [ ] Подключение Alpine.js (через CDN или npm)
- [ ] Настройка файлового кэша (`CACHE_DRIVER=file`)
- [ ] Настройка database очередей (`QUEUE_CONNECTION=database`)

#### 1.2 Базовая аутентификация
- [ ] Миграция users (id, email, password, name, phone, is_admin, is_blocked, created_at, updated_at)
- [ ] Кастомная авторизация (без Breeze)
- [ ] Регистрация (email, password, name, phone)
- [ ] Вход/выход
- [ ] Восстановление пароля
- [ ] Middleware для проверки авторизации
- [ ] Middleware для админки (проверка is_admin)

#### 1.3 Базовый layout
- [ ] Blade layout (`resources/views/layouts/app.blade.php`)
- [ ] Header с навигацией (адаптивное меню с Alpine.js)
- [ ] Footer
- [ ] Базовые стили TailwindCSS
- [ ] Подключение Alpine.js в layout

**Результат:** Регистрация/авторизация + Blade layout

---

### Этап 2: Каталог товаров (4-5 дней)

#### 2.1 Модели и миграции
- [ ] Миграция categories (id, name, slug, parent_id, created_at, updated_at)
- [ ] Миграция tags (id, name, slug, created_at, updated_at)
- [ ] Миграция products (id, title, slug, description, price, category_id, image_path, in_stock, created_at, updated_at)
- [ ] Миграция product_tag (product_id, tag_id) - связь многие-ко-многим
- [ ] Модели Category, Tag, Product с отношениями
- [ ] Seeder для категорий (PlayStation, Xbox и т.д.)

#### 2.2 Загрузка изображений (упрощенная)
- [ ] Простая загрузка через `Storage::disk('public')`
- [ ] Сохранение пути в БД (`image_path`)
- [ ] Создание симлинка `storage/app/public` → `public/storage`

#### 2.3 Контроллеры и роуты
- [ ] ProductController (index, show) с пагинацией
- [ ] CategoryController (index, show)
- [ ] Роуты для каталога

#### 2.4 Frontend страницы (Blade)
- [ ] Blade шаблон каталога товаров (`products/index.blade.php`)
- [ ] Blade шаблон категории
- [ ] Blade шаблон товара (`products/show.blade.php`)
- [ ] Компонент карточки товара (`components/product-card.blade.php`)
- [ ] Простой поиск товаров (форма с GET запросом)
- [ ] Пагинация (Laravel pagination)

**Результат:** Работающий каталог товаров на Blade

---

### Этап 3: Корзина (2-3 дня)

#### 3.1 Backend
- [ ] Миграция cart_items (id, session_id, user_id nullable, product_id nullable, service_id nullable, item_type: 'product'|'service', quantity, created_at, updated_at)
- [ ] Модель CartItem (полиморфная связь с products/services)
- [ ] CartService (добавление, обновление, удаление)
- [ ] CartController (index, add, update, remove)

#### 3.2 Frontend (Blade)
- [ ] Blade шаблон корзины (`cart/index.blade.php`)
- [ ] Компонент корзины в header (`components/cart-counter.blade.php`) - счетчик через AJAX или сессию
- [ ] Добавление в корзину через AJAX (fetch API или Alpine.js)
- [ ] Изменение количества (AJAX запрос)
- [ ] Удаление из корзины (AJAX запрос)

**Результат:** Работающая корзина для товаров и услуг на Blade

---

### Этап 4: Услуги (2-3 дня)

#### 4.1 Backend
- [ ] Миграция services (id, title, slug, description, price, category_id, image_path, type: 'repair'|'buy', in_stock, created_at, updated_at)
- [ ] Миграция service_tag (service_id, tag_id) - связь многие-ко-многим
- [ ] Модель Service с отношениями (Category, Tag)
- [ ] ServiceController (index, show)
- [ ] Интеграция с корзиной (через CartService)

#### 4.2 Frontend (Blade)
- [ ] Blade шаблон услуг (`services/index.blade.php`)
- [ ] Компонент карточки услуги (`components/service-card.blade.php`)
- [ ] Blade шаблон услуги (`services/show.blade.php`)
- [ ] Добавление в корзину (AJAX)

**Результат:** Услуги отображаются и добавляются в корзину на Blade

---

### Этап 5: Новости и комментарии (3-4 дня)

#### 5.1 Backend
- [ ] Миграция news (id, title, slug, description, content, image_path, category_id, author_id, published_at, created_at, updated_at)
- [ ] Миграция news_tag (news_id, tag_id) - связь многие-ко-многим
- [ ] Миграция comments (id, news_id, user_id, content, parent_id nullable, created_at, updated_at)
- [ ] Модели News, Comment с отношениями (User, Category, Tag)
- [ ] NewsController (index, show) с пагинацией
- [ ] CommentController (store, destroy)

#### 5.2 Frontend (Blade)
- [ ] Blade шаблон ленты новостей (`news/index.blade.php`)
- [ ] Blade шаблон новости (`news/show.blade.php`)
- [ ] Форма добавления комментария (Blade форма)
- [ ] Компонент комментариев (простой список, без дерева ответов)
- [ ] Пагинация новостей (Laravel pagination)

**Результат:** Работающая лента новостей с комментариями на Blade

---

### Этап 6: Оформление заказа (3-4 дня)

#### 6.1 Backend
- [ ] Миграция orders (id, order_number, user_id nullable, status, total, delivery_info JSON, payment_info JSON, contact_info JSON, created_at, updated_at)
- [ ] Миграция order_items (id, order_id, item_id, item_type: 'product'|'service', quantity, price, created_at)
- [ ] Модели Order, OrderItem (полиморфная связь)
- [ ] OrderService (создание заказа, расчет доставки - бесплатно от 3000₽)
- [ ] OrderController (store, index для истории)

#### 6.2 Frontend (Blade)
- [ ] Blade шаблон оформления заказа (`orders/create.blade.php`)
- [ ] Форма контактов (имя, телефон, email, адрес) - Blade форма
- [ ] Выбор доставки (бесплатно от 3000₽) - расчет через Alpine.js или сервер
- [ ] Выбор оплаты (наличные/карта) - радио кнопки
- [ ] Подтверждение заказа
- [ ] Blade шаблон успешного заказа

**Результат:** Полный цикл оформления заказа на Blade

---

### Этап 7: Личный кабинет (2 дня)

#### 7.1 Backend
- [ ] ProfileController (show, update)
- [ ] Методы для истории заказов (через OrderController)

#### 7.2 Frontend (Blade)
- [ ] Blade шаблон профиля (`profile/index.blade.php`)
- [ ] Редактирование данных (имя, телефон, email) - Blade форма
- [ ] История заказов (список) - Blade таблица
- [ ] Детали заказа - Blade шаблон

**Результат:** Базовый личный кабинет на Blade

---

### Этап 8: Административная панель (6-7 дней)

#### 8.1 Роли и права
- [ ] Middleware для админки (проверка is_admin поля в users)
- [ ] Роуты админки с префиксом `/admin`

#### 8.2 CRUD товаров
- [ ] Admin/ProductController (index, create, store, edit, update, destroy)
- [ ] Blade шаблоны админки для товаров:
  - Список товаров (`admin/products/index.blade.php`) - таблица
  - Форма создания (`admin/products/create.blade.php`)
  - Форма редактирования (`admin/products/edit.blade.php`)
  - Поля: title, description, price, category_id, image_path, tags, in_stock
- [ ] Простая загрузка изображений через Storage
- [ ] Валидация форм (Laravel FormRequest)

#### 8.3 CRUD услуг
- [ ] Admin/ServiceController (index, create, store, edit, update, destroy)
- [ ] Blade шаблоны админки для услуг:
  - Список услуг (`admin/services/index.blade.php`)
  - Форма создания/редактирования (`admin/services/create.blade.php`, `edit.blade.php`)
  - Поля: title, description, price, category_id, image_path, type, tags, in_stock

#### 8.4 CRUD новостей
- [ ] Admin/NewsController (index, create, store, edit, update, destroy)
- [ ] Blade шаблоны админки для новостей:
  - Список новостей (`admin/news/index.blade.php`)
  - Форма создания/редактирования (`admin/news/create.blade.php`, `edit.blade.php`)
  - Поля: title, description, content, category_id, image_path, tags, published_at
- [ ] Простой текстовый редактор (textarea или базовый WYSIWYG через CDN)

#### 8.5 Управление пользователями
- [ ] Admin/UserController (index, show, block, unblock)
- [ ] Blade шаблон списка пользователей (`admin/users/index.blade.php`)
- [ ] Блокировка/разблокировка (AJAX запрос или форма)

#### 8.6 Управление комментариями
- [ ] Admin/CommentController (index, destroy)
- [ ] Blade шаблон списка комментариев (`admin/comments/index.blade.php`)
- [ ] Удаление комментариев (AJAX или форма)

#### 8.7 Layout админки
- [ ] Админский Blade layout (`layouts/admin.blade.php`) - простой сайдбар, навигация
- [ ] Базовый дашборд (опционально)

**Результат:** Упрощенная админка со всеми CRUD операциями

---

### Этап 9: Главная страница (2-3 дня)

#### 9.1 Backend
- [ ] Миграция banners (id, title, image_path, link, order, active, created_at, updated_at)
- [ ] Модель Banner
- [ ] HomeController (index)
- [ ] Логика выборки товаров (новые, популярные)
- [ ] Логика выборки услуг
- [ ] Логика выборки новостей (последние)

#### 9.2 Frontend (Blade)
- [ ] Blade шаблон главной страницы (`home.blade.php`)
- [ ] Слайдер баннеров (Swiper.js через CDN или Alpine.js)
- [ ] Блок новых товаров (использование компонента product-card)
- [ ] Блок услуг (использование компонента service-card)
- [ ] Блок последних новостей
- [ ] Адаптивный дизайн (TailwindCSS)

**Результат:** Главная страница со всеми блоками на Blade

---

### Этап 10: Полировка и оптимизация (3-4 дня)

#### 10.1 SEO
- [ ] Meta теги для страниц
- [ ] Sitemap.xml (генерируется автоматически)
- [ ] Robots.txt
- [ ] Open Graph теги

#### 10.2 Оптимизация
- [ ] Файловый кэш для запросов
- [ ] Базовая оптимизация изображений
- [ ] Lazy loading для изображений
- [ ] Минификация CSS/JS через Vite

#### 10.3 UX улучшения
- [ ] Toast уведомления (легкая библиотека)
- [ ] Загрузчики (loading states)
- [ ] Обработка ошибок
- [ ] Валидация форм

#### 10.4 Тестирование
- [ ] Тестирование основных сценариев
- [ ] Проверка на разных устройствах
- [ ] Исправление багов

**Результат:** Оптимизированный сайт

---

## Дополнительные функции (опционально)

### Email уведомления
- [ ] Уведомление о регистрации
- [ ] Уведомление о заказе
- [ ] Уведомление об изменении статуса заказа

### Интеграции
- [ ] Платежные системы (ЮKassa, Сбербанк)
- [ ] SMS уведомления
- [ ] Экспорт заказов в Excel

### Аналитика
- [ ] Google Analytics
- [ ] Яндекс.Метрика

---

## Оценка времени и стоимости

### Время разработки (Blade план)

| Этап | Время | Описание |
|------|-------|----------|
| Этап 0: Локальная настройка | 30-60 мин | Composer, npm, .env |
| Этап 1: Базовая настройка | 1-2 дня | Laravel + Blade + TailwindCSS + Auth |
| Этап 2: Каталог товаров | 3-4 дня | Товары, категории, теги (Blade) |
| Этап 3: Корзина | 1-2 дня | Корзина для товаров и услуг (Blade + AJAX) |
| Этап 4: Услуги | 1-2 дня | Услуги в корзину (Blade) |
| Этап 5: Новости и комментарии | 2-3 дня | Лента новостей, комментарии (Blade) |
| Этап 6: Оформление заказа | 2-3 дня | Заказы, доставка, оплата (Blade) |
| Этап 7: Личный кабинет | 1-2 дня | Профиль, история заказов (Blade) |
| Этап 8: Админка | 4-5 дней | CRUD всех сущностей (Blade) |
| Этап 9: Главная страница | 1-2 дня | Баннеры, блоки (Blade) |
| Этап 10: Полировка | 2-3 дня | SEO, оптимизация, тесты |
| **ИТОГО** | **19-28 дней** | **~1 месяц** |

### Стоимость разработки (Blade вариант)

#### Бюджетный вариант (рекомендуемый)
- **Время:** 19-28 дней
- **Стоимость:** 50,000 - 70,000 ₽
- **Что включено:** 
  - Все основные функции (товары, услуги, новости, комментарии, корзина, заказы)
  - Полный CRUD для товаров, услуг, новостей в админке
  - Управление пользователями и комментариями
  - Базовая оптимизация и SEO
  - Современный дизайн на TailwindCSS
  - Простая интерактивность на Alpine.js

### Разбивка по компонентам

| Компонент | Стоимость |
|-----------|-----------|
| Backend (Laravel) | 25,000₽ |
| Frontend (Blade + TailwindCSS) | 15,000₽ |
| Админка (CRUD) | 10,000₽ |
| Полировка и тесты | 5,000₽ |
| **ИТОГО** | **~60,000₽** |

---

## Рекомендации

### Приоритет разработки
1. **Высокий:** Этапы 0-3, 6, 8 (базовая функциональность, админка)
2. **Средний:** Этапы 4, 5, 7, 9 (услуги, новости, ЛК, главная)
3. **Низкий:** Этап 10 (полировка)

### Упрощения для экономии
- Использование `is_admin` поля вместо Spatie Permissions
- Простая загрузка файлов через Storage вместо Spatie Media Library
- Файловый кэш вместо Redis
- Database очереди вместо Horizon
- Blade шаблоны вместо Vue/Inertia (проще и быстрее)
- TailwindCSS для стилизации (готовые классы)
- Alpine.js для простой интерактивности (легкий, без сборки)
- Простой дизайн с готовыми TailwindCSS компонентами

---

## Технические детали

### База данных (основные таблицы)

#### Сущности (бизнес-логика)
- `users` - пользователи (id, email, password, name, phone, is_admin, is_blocked, created_at, updated_at)
- `products` - товары (id, title, slug, description, price, category_id, image_path, in_stock, created_at, updated_at)
- `services` - услуги (id, title, slug, description, price, category_id, image_path, type: 'repair'|'buy', in_stock, created_at, updated_at)
- `news` - новости (id, title, slug, description, content, image_path, category_id, author_id, published_at, created_at, updated_at)
- `comments` - комментарии (id, news_id, user_id, content, parent_id nullable, created_at, updated_at)
- `orders` - заказы (id, order_number, user_id nullable, status, total, delivery_info JSON, payment_info JSON, contact_info JSON, created_at, updated_at)

#### Вспомогательные таблицы
- `categories` - категории (id, name, slug, parent_id nullable, created_at, updated_at)
- `tags` - теги (id, name, slug, created_at, updated_at)
- `product_tag` - связь товаров и тегов (product_id, tag_id)
- `service_tag` - связь услуг и тегов (service_id, tag_id)
- `news_tag` - связь новостей и тегов (news_id, tag_id)
- `cart_items` - корзина (id, session_id, user_id nullable, item_id, item_type: 'product'|'service', quantity, created_at, updated_at)
- `order_items` - позиции заказов (id, order_id, item_id, item_type: 'product'|'service', quantity, price, created_at)
- `banners` - баннеры (id, title, image_path, link nullable, order, active, created_at, updated_at)

### Роли пользователей
- **user** - обычный пользователь (is_admin = false)
- **admin** - администратор (is_admin = true, доступ к админке)

### Основные фичи
- Сессионная корзина для неавторизованных (через session_id)
- Автоматический расчет бесплатной доставки от 3000₽
- Удаление комментариев админом
- Блокировка пользователей админом (is_blocked)

---

## Риски и ограничения

### Технические риски
- Загрузка и обработка изображений требует оптимизации
- Большое количество товаров может потребовать пагинации и кэширования
- AJAX запросы для корзины требуют обработки ошибок

### Временные риски
- Изменение требований в процессе разработки
- Необходимость дополнительных согласований дизайна
- Интеграции с внешними сервисами могут занять дополнительное время

### Рекомендации по снижению рисков
- Использовать простые решения (кастомная auth без Breeze)
- Применять компонентный подход для переиспользования кода
- Регулярные демо для заказчика (каждую неделю)

---

## Команды для локальной разработки

```bash
# Установка
composer install
npm install
npm run build

# Запуск
php artisan serve   # http://localhost:8000

# Миграции
php artisan migrate

# Симлинк для storage
php artisan storage:link

# Разработка с hot reload
npm run dev
```

## Следующие шаги

1. **Утверждение плана** - согласование упрощенного плана
2. **Локальная настройка** - composer, npm (Этап 0)
3. **Начало разработки** - старт с Этапа 1 (базовая настройка)
4. **Регулярные демо** - показ прогресса каждую неделю

---

**Дата создания плана:** 2024  
**Версия:** 3.1 (Blade + TailwindCSS + Alpine.js, локальная разработка)
