# План реализации (безопасность и производительность)

---

## 1. Security Headers — middleware

**Цель:** Добавить X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Content-Security-Policy.

**Шаги:**
1. Создать `app/Http/Middleware/SecurityHeadersMiddleware.php`
2. В `bootstrap/app.php` зарегистрировать middleware глобально (или в группу web)

**Результат:** Все ответы получают заголовки:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `X-XSS-Protection: 1; mode=block`
- `Content-Security-Policy: default-src 'self'` (базовый, при необходимости расширить для inline scripts, CDN)

---

## 2. CORS

**Цель:** Явная настройка CORS (если есть API или внешние запросы).

**Шаги:**
1. Проверить: есть ли API-роуты, запросы с других доменов?
2. Если нет — пропустить.
3. Если да — настроить `config/cors.php` (Laravel по умолчанию возвращает 403 на cross-origin, можно оставить так или настроить allowed origins).

**Результат:** Документированное решение: CORS не нужен / настроен.

---

## 3. XSS в `nl2br(e(...))`

**Действие:** Ничего не делать — `e()` экранирует, безопасно.

---

## 4. SQL Injection в `orderByRaw`

**Действие:** Ничего не делать — параметры биндятся, injection нет.

---

## 5. CSRF — проверка форм

**Цель:** Убедиться, что все POST/PATCH/DELETE формы используют `@csrf`.

**Шаги:**
1. Найти все формы: `grep -r "<form" resources/views --include="*.blade.php"`
2. Проверить наличие `@csrf` или `{{ csrf_field() }}` внутри каждой

**Результат:** Список форм и отметка — всё ли в порядке.

---

## 6. N+1 в `User::getPurchasedWithoutReview`

**Цель:** Уменьшить число запросов — join/подзапросы или кэш.

**Шаги:**
1. Открыть `app/Models/User.php`, метод `getPurchasedWithoutReview`
2. Переписать логику: один запрос с подзапросами/join вместо нескольких
3. Или: `Cache::remember("user.{$this->id}.purchased_no_review", 300, fn() => ...)` на 5 минут

**Результат:** 1–2 запроса вместо множества.

---

## 7. `getRecommendedProducts` — замена `inRandomOrder()`

**Цель:** Избежать тяжёлого `ORDER BY RAND()` на больших таблицах.

**Варианты:**
- **A:** `ORDER BY RAND() LIMIT N` — один запрос, но RAND() всё ещё дорог
- **B:** Сортировка по `id DESC` или по `updated_at` + `LIMIT 20` → `->random(min(5, $count))` в коллекции
- **C:** Кэш списка ID рекомендуемых товаров (обновлять раз в час) + выборка по ним

**Шаги:**
1. Выбрать вариант B (простой и без кэша) или C (если товаров много)
2. Изменить `HomeController::getRecommendedProducts`

**Результат:** Быстрее на больших каталогах.

---

## 8. Products index — `with` дублирование

**Действие:** Оставить как есть. Пагинация уже есть, при росте — смотреть позже.

---

## 9. БД индексы

**Цель:** Добавить индексы для частых фильтров.

**Шаги:**
1. Создать миграцию `add_performance_indexes`
2. Добавить:
   - `products`: индекс на `(category_id)`, композитный `(in_stock, is_new, is_recommended)` при необходимости
   - `orders`: `(status)`, `(created_at)` если ещё нет
   - `reviews`: `(reviewable_type, reviewable_id)`
   - `images`: `(imageable_type, imageable_id)` если ещё нет
3. Проверить существующие миграции — не дублировать индексы

**Результат:** Миграция с индексами, `php artisan migrate`.

---

## 10. Eager loading — проверка N+1

**Цель:** Убедиться, что в views нет N+1 (обращения к `product->category`, `order->items` в циклах без preload).

**Шаги:**
1. Пройтись по view с циклами: `products.index`, `orders.index`, `home`, `products.show` и т.д.
2. Сверить: для каждого отношения в цикле (`$item->relation`) есть ли `with('relation')` в контроллере
3. Исправить: добавить недостающие `with()` в запрос

**Результат:** Таблица: view → отношение → контроллер → статус (ok/fixed).

---

## Порядок выполнения

| # | Задача | Время | Зависимости |
|---|--------|-------|-------------|
| 1 | Security headers middleware | 15 мин | — |
| 2 | CORS (проверка/настройка) | 10 мин | — |
| 5 | CSRF проверка форм | 15 мин | — |
| 6 | User::getPurchasedWithoutReview | 30 мин | — |
| 7 | getRecommendedProducts | 20 мин | — |
| 9 | БД индексы (миграция) | 20 мин | — |
| 10 | Eager loading аудит | 30 мин | — |

**Оценка:** ~2.5 часа чистого времени.
