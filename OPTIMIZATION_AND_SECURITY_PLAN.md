# План оптимизации и безопасности проекта UlPlay

Документ создан на основе анализа кодовой базы. Приоритеты: критичные уязвимости → производительность → улучшения.

**Ограничение:** сервер без sudo — нельзя устанавливать Redis, Memcached и т.п. Все рекомендации учитывают только PHP/Laravel, файловую систему и БД.

---

## 1. Безопасность

### 1.1 Критичные / высокий приоритет

| № | Проблема | Где | Решение |
|---|----------|-----|---------|
| 1 | **delete_images без проверки принадлежности** | ProductController, ServiceController, NewsController | В `$product->images()->whereIn('id', $deleteIds)` админ может передать ID изображений другого продукта. Нужно явно фильтровать: `$product->images()->whereIn('id', $deleteIds)` уже ограничивает по relation, но `$deleteIds` не валидируются (могут быть нечисловые). Добавить валидацию: `'delete_images' => ['nullable', 'array'], 'delete_images.*' => ['integer', Rule::exists('images', 'id')->where('imageable_id', $product->id)->where('imageable_type', Product::class)]` |
| 2 | **Production: APP_DEBUG, LOG_LEVEL** | .env.example, docker | В .env.example по умолчанию `APP_DEBUG=true` и `LOG_LEVEL=debug`. Для production обязательно `APP_DEBUG=false`, `LOG_LEVEL=warning` или `error`. Добавить в документацию/README предупреждение. |
| 3 | **Session/Cookie в production** | config/session.php | Убедиться, что в production: `SESSION_SECURE_COOKIE=true`, `SESSION_SAME_SITE=lax` (или strict). Зафиксировать в .env.example. |
| 4 | **Rate limit: admin 120/min** | throttle.php | 120 запросов/мин на админа — много. Рассмотреть снижение до 60 или использование отдельного лимита для опасных действий. |

### 1.2 Средний приоритет

| № | Проблема | Где | Решение |
|---|----------|-----|---------|
| 5 | **Потенциальный path traversal в Image** | Image::getUrlAttribute | Если path когда-либо приходит извне — риск. Сейчас path из `store()` — безопасно. Рекомендация: явно запретить пути с `..` при сохранении. |
| 6 | **Загрузка файлов** | ProductController, SupportTicketController | Нет проверки MIME (только extension через `image`). Рекомендация: `'images.*' => ['image', 'mimes:jpeg,png,gif,webp', 'max:4096']`. |
| 7 | **Хедеры безопасности** | - | Добавить middleware или nginx: X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Content-Security-Policy (базовый). |
| 8 | **CORS** | - | Если есть API/внешние запросы — настроить CORS явно. |

### 1.3 Низкий приоритет

| № | Проблема | Где | Решение |
|---|----------|-----|---------|
| 9 | **XSS в nl2br(e(...))** | news/show.blade.php | `{!! nl2br(e($news->content)) !!}` — безопасно, e() экранирует. Оставить как есть. |
| 10 | **SQL Injection** | ProductController::orderByRaw | `orderByRaw('...', ["%{$q}%", "%{$q}%"])` — параметры биндятся, injection нет. |
| 11 | **CSRF** | - | Laravel по умолчанию защищает формы. Проверить, что все формы используют @csrf. |

---

## 2. Производительность

### 2.1 Высокий приоритет

| № | Проблема | Где | Решение |
|---|----------|-----|---------|
| 1 | **Отсутствие кэширования** | Весь проект | Нет вызовов `Cache::` или `cache()`. Рекомендуется кэшировать: категории для сайдбара (`Category::orderBy('name')->get()`), footer config, статистику (например, на 5–15 мин). |
| 2 | **View Composer выполняет запросы на каждый рендер** | AppServiceProvider | CartService::getItems(), UserNotification::count() вызываются при каждом рендере layouts.app, home, products.*. Решение: кэшировать в request (singleton в рамках запроса) или уменьшить число представлений, использующих composer. |
| 3 | **N+1 в User::getPurchasedWithoutReview** | User.php | Много запросов: OrderItem, Product, Service, Review. Переписать с использованием join/подзапросов или кэшировать результат на уровне пользователя. |
| 4 | **Category::orderBy('name')->get() в каждом create/edit** | ProductController, CategoryController | Кэшировать список категорий на 5–10 мин или хранить в config. |
| 5 | **StatisticsController — тяжёлые запросы** | StatisticsController | Много raw-запросов и итераций. Рассмотреть: кэш на 15–30 мин, вынос в Job с отложенным обновлением, индексы БД. |

### 2.2 Средний приоритет

| № | Проблема | Где | Решение |
|---|----------|-----|---------|
| 6 | **HomeController::getRecommendedProducts** | HomeController | `inRandomOrder()` — дорого на больших таблицах. Альтернативы: RAND() с LIMIT, предвычисленный список, или кэш. |
| 7 | **Products index — дублирование with** | ProductController | `with(['category','images'])` + `withAvg` + `withCount` — нормально. При росте данных рассмотреть пагинацию/ленивую загрузку. |
| 8 | **БД индексы** | migrations | Проверить индексы для частых фильтров: products (category_id, in_stock, is_new, is_recommended), orders (status, created_at), reviews (reviewable_type, reviewable_id), images (imageable_type, imageable_id). |
| 9 | **Session/Cache driver** | config | Без sudo: остаёмся на `database` или `file`. `file` для cache часто быстрее database — можно попробовать `CACHE_STORE=file`. Redis недоступен. |
| 10 | **Queue** | config/queue | По умолчанию sync/database. WebPush выполняется синхронно. Без доп. установок — оставить как есть или использовать database queue + cron `queue:work` (если доступен). |

### 2.3 Низкий приоритет

| № | Проблема | Где | Решение |
|---|----------|-----|---------|
| 11 | **Vite build** | vite.config | Проверить code splitting, lazy loading для тяжёлых страниц (Chart.js на статистике). |
| 12 | **Image optimization** | - | Рассмотреть Intervention Image или Laravel Media Library для ресайза/оптимизации загружаемых изображений. |
| 13 | **Eager loading** | Различные контроллеры | В целом используется with(). Проверить отсутствие N+1 в views (например, product->category в циклах). |

---

## 3. Инфраструктура и конфигурация (без установки ПО)

| № | Рекомендация | Описание |
|---|--------------|----------|
| 1 | **Cache driver** | Без Redis: `CACHE_STORE=file` или `database`. File обычно быстрее database для cache. |
| 2 | **OPcache** | Если уже включён хостингом — ничего не делать. Без sudo настройка недоступна. |
| 3 | **Queue worker** | Если cron доступен: добавить `* * * * * php /path/artisan schedule:run`. Для queue: нужен `queue:work` в фоне — без supervisor только через cron `queue:work --stop-when-empty` каждую минуту. |
| 4 | **Schedule** | В cron: `php artisan schedule:run` раз в минуту (если хостинг даёт cron). |
| 5 | **Asset optimization** | `php artisan config:cache`, `route:cache`, `view:cache` — только команды Laravel, без доп. установок. |
| 6 | **Log rotation** | Laravel `'days' => 14` в config/logging.php — ротация через встроенные daily channels. |
| 7 | **Database backups** | Ручной export или скрипт через `mysqldump`/`sqlite3 .dump` если есть в PATH. |
| 8 | **Health check** | Роут `/up` уже есть. |

---

## 4. Качество кода и поддерживаемость

| № | Рекомендация | Описание |
|---|--------------|----------|
| 1 | **PHPStan / Larastan** | Добавить статический анализ (level 5+). |
| 2 | **Pint** | Уже есть — использовать в CI для форматирования. |
| 3 | **Тесты** | Есть UserControllerTest, StatisticsControllerTest. Расширить покрытие: OrderService, SupportTicket, auth flows. |
| 4 | **Документация .env** | В .env.example добавить комментарии для production (APP_DEBUG, LOG_LEVEL, SESSION_*, CACHE_*, QUEUE_*). |
| 5 | **Duplicate categories query** | ProductController::index вызывает Category::... дважды (для фильтра и для view). Объединить. |

---

## 5. Порядок внедрения (рекомендуемый)

### Фаза 1 — Быстрые победы (1–2 дня)
1. Валидация delete_images (существование + принадлежность).
2. .env.example: APP_DEBUG=false для production, комментарии.
3. Кэширование категорий (Cache::remember 10 min).
4. Кэширование статистики (15 min).

### Фаза 2 — Производительность (3–5 дней)
5. View Composer: оптимизация (уменьшить запросы / кэш cartProductIds в рамках запроса).
6. User::getPurchasedWithoutReview — оптимизация запросов.
7. Добавить недостающие индексы в миграциях.
8. CACHE_STORE=file (если сейчас database) — без установки чего-либо.

### Фаза 3 — Дополнительная безопасность (1–2 дня)
9. Security headers (middleware).
10. Снижение admin throttle или разделение лимитов.
11. MIME-валидация загрузок.

### Фаза 4 — Масштабирование (по необходимости)
12. Вынос WebPush в Job.
13. Оптимизация getRecommendedProducts.
14. Полный аудит N+1 (e.g. Laravel Debugbar в dev).

---

## 6. Чек-лист перед деплоем в production (без sudo)

- [ ] APP_DEBUG=false
- [ ] LOG_LEVEL=warning или error
- [ ] APP_KEY сгенерирован
- [ ] SESSION_SECURE_COOKIE=true (при HTTPS)
- [ ] CACHE_STORE=file или database (Redis недоступен без sudo)
- [ ] Schedule:run в cron (если хостинг даёт доступ к cron)
- [ ] Выполнены `config:cache`, `route:cache`, `view:cache`
- [ ] storage и bootstrap/cache доступны на запись
- [ ] Резервное копирование БД (ручной export или скрипт)
