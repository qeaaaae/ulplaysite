# UlPlay — Интернет-магазин игровых консолей

Интернет-магазин игровых консолей, аксессуаров и услуг ремонта в Ульяновске.

## MVP

Текущая версия — MVP главной страницы с моковыми данными.

### Стек

- **Laravel 12** (PHP 8.2+)
- **Blade** + **TailwindCSS 4**
- **Alpine.js** (мобильное меню)
- **Swiper.js** (слайдер баннеров)

### Запуск локально

**Требования:** PHP 8.2+, Composer, Node.js 18+

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
npm run build
php artisan serve
```

**Откройте http://localhost:8000**

MVP использует моковые данные, БД не требуется (по умолчанию SQLite).

---

### Docker (production)

В репозитории настроен production-разворот через `docker-compose.yml`:

- **nginx + php-fpm**
- **mysql 8** (обязательная)
- **сборка внутрь образа** (Composer + `npm run build`), без `node_modules` на проде
- **HTTPS (Let's Encrypt) + защита от clickjacking** в Nginx (`Content-Security-Policy frame-ancestors`, `X-Frame-Options`, `HSTS`, `nosniff` и т.д.)
- **rate limiting** в Nginx (ограничение запросов/соединений на IP)

Запуск:

```bash
docker compose up -d --build
```

Открыть: `https://www.ulplay.com` (после выпуска сертификата)

Первичная выдача сертификата (один раз на сервере, DNS уже должен указывать на этот сервер):

```bash
docker compose up -d nginx
docker compose run --rm certbot certonly --webroot -w /var/www/certbot -d www.ulplay.com -d ulplay.com --email admin@ulplay.com --agree-tos --no-eff-email
docker compose up -d
```

Миграции/сидеры (по необходимости):

```bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
```

Переменные окружения:

- В `docker-compose.yml` сейчас заданы базовые `DB_*` для MySQL.
- Для реального продакшена **вынеси секреты** (APP_KEY, пароли) в окружение/секреты и не коммить их в репозиторий.

Тюнинг rate limit:

- Настройки зон лежат в `docker/nginx/ratelimit.conf`
- Применение лимитов — в `docker/nginx/prod-https.conf` (директивы `limit_req`/`limit_conn`)

---

### Структура MVP

- **Главная страница** (`/`) — слайдер, категории, новые товары, рекомендуемые, услуги, новости
- **Header** — логотип, навигация, корзина, вход
- **Footer** — информация о компании, каталог, услуги, контакты

### Дизайн

Следует дизайн-системе из `design.md`.

### Документация

- `development-plan.md` — полный план разработки
- `mvp.md` — спецификация MVP
- `design.md` — дизайн-система
