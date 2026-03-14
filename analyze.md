# Анализ сайта ulplay.com

## Общая информация
**URL:** https://ulplay.com/  
**Тип:** Интернет-магазин игровых консолей, аксессуаров и услуг ремонта  
**Регион:** Ульяновск, Россия

---

## Структура навигации и роуты

### Основные разделы

#### 1. Главная страница
- **URL:** `/` или `/index.php`
- **Функционал:**
  - Слайдер/баннеры
  - Новые поступления товаров
  - Рекомендуемые товары
  - Категории товаров

#### 2. Каталог товаров

##### Категория: PlayStation
- **URL:** `/playstation/` или `/category/playstation/`
- **Подкатегории:**
  - PlayStation 2: `/playstation/ps2/` или `/category/playstation-2/`
  - PlayStation 3: `/playstation/ps3/` или `/category/playstation-3/`
  - PlayStation 4: `/playstation/ps4/` или `/category/playstation-4/`
  - PlayStation Vita: `/playstation/vita/` или `/category/playstation-vita/`
  - PlayStation Аксессуары: `/playstation/accessories/` или `/category/playstation-accessories/`
  - PSP: `/playstation/psp/` или `/category/psp/`
  - Игры для PlayStation 2: `/playstation/games/ps2/`
  - Игры для PlayStation 3: `/playstation/games/ps3/`
  - Игры для PlayStation 4: `/playstation/games/ps4/`

##### Категория: Microsoft Xbox
- **URL:** `/xbox/` или `/category/xbox/`
- **Подкатегории:**
  - Xbox 360: `/xbox/360/` или `/category/xbox-360/`
  - Xbox 360 лицензионные игры: `/xbox/360/games/` или `/category/xbox-360-games/`
  - Xbox ONE: `/xbox/one/` или `/category/xbox-one/`
  - Xbox ONE лицензионные игры: `/xbox/one/games/` или `/category/xbox-one-games/`
  - Аксессуары: `/xbox/accessories/` или `/category/xbox-accessories/`

##### Категория: Ремонт
- **URL:** `/repair/` или `/services/repair/`
- **Функционал:**
  - Прайс-лист на ремонт
  - Форма заявки на ремонт
  - Описание услуг

##### Категория: Купим ваше
- **URL:** `/buy/` или `/services/buy/`
- **Функционал:**
  - Форма оценки товара
  - Условия покупки

#### 3. Страница товара
- **URL паттерн:** `/product/{id}/` или `/product/{slug}/` или `/catalog/{id}/`
- **Примеры:**
  - `/product/dobe-charging-station-ps4/`
  - `/product/white-dualshock-3-ps3/`
- **Функционал:**
  - Изображения товара (галерея)
  - Название и описание
  - Цена
  - Кнопка "Купить" (добавление в корзину)
  - Характеристики товара
  - Похожие товары

#### 4. Корзина
- **URL:** `/cart/` или `/basket/` или `/checkout/cart/`
- **Функционал:**
  - Список товаров в корзине
  - Изменение количества
  - Удаление товаров
  - Итоговая сумма
  - Переход к оформлению заказа

#### 5. Оформление заказа
- **URL:** `/checkout/` или `/order/` или `/checkout/order/`
- **Функционал:**
  - Форма контактных данных
  - Выбор способа доставки
  - Выбор способа оплаты
  - Подтверждение заказа

#### 6. Личный кабинет

##### Регистрация
- **URL:** `/register/` или `/account/register/` или `/user/register/`
- **Функционал:**
  - Форма регистрации (email, пароль, ФИО, телефон)
  - Подтверждение email

##### Авторизация
- **URL:** `/login/` или `/account/login/` или `/user/login/`
- **Функционал:**
  - Форма входа (email/телефон, пароль)
  - Восстановление пароля
  - Запомнить меня

##### Профиль пользователя
- **URL:** `/account/` или `/profile/` или `/user/profile/`
- **Функционал:**
  - Редактирование данных
  - История заказов
  - Адреса доставки
  - Изменение пароля

#### 7. Закладки
- **URL:** `/wishlist/` или `/favorites/` или `/bookmarks/`
- **Функционал:**
  - Список избранных товаров
  - Добавление/удаление из закладок

#### 8. Информационные страницы

##### О нас
- **URL:** `/about/` или `/info/about/`

##### Доставка и оплата
- **URL:** `/delivery/` или `/payment/` или `/info/delivery/`
- **Информация:**
  - Бесплатная доставка от 3000₽ по городу Ульяновск
  - Способы оплаты
  - Условия доставки

##### Контакты
- **URL:** `/contacts/` или `/contact/`
- **Данные:**
  - Телефон: +7 (927) 988-88-70
  - Адрес (если указан)
  - Форма обратной связи

---

## Предполагаемые API endpoints

### Авторизация и пользователи

#### POST `/api/auth/register`
**Описание:** Регистрация нового пользователя  
**Тело запроса:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "name": "Иван Иванов",
  "phone": "+79279888870"
}
```
**Ответ:**
```json
{
  "success": true,
  "message": "Регистрация успешна",
  "user": { "id": 1, "email": "user@example.com" }
}
```

#### POST `/api/auth/login`
**Описание:** Авторизация пользователя  
**Тело запроса:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "remember": true
}
```
**Ответ:**
```json
{
  "success": true,
  "token": "jwt_token_here",
  "user": { "id": 1, "email": "user@example.com", "name": "Иван Иванов" }
}
```

#### POST `/api/auth/logout`
**Описание:** Выход из системы  
**Заголовки:** `Authorization: Bearer {token}`

#### GET `/api/user/profile`
**Описание:** Получение профиля пользователя  
**Заголовки:** `Authorization: Bearer {token}`  
**Ответ:**
```json
{
  "id": 1,
  "email": "user@example.com",
  "name": "Иван Иванов",
  "phone": "+79279888870",
  "addresses": []
}
```

#### PUT `/api/user/profile`
**Описание:** Обновление профиля пользователя  
**Заголовки:** `Authorization: Bearer {token}`  
**Тело запроса:**
```json
{
  "name": "Иван Иванов",
  "phone": "+79279888870"
}
```

### Каталог товаров

#### GET `/api/products`
**Описание:** Список товаров с фильтрацией и пагинацией  
**Параметры:**
- `category` (string, optional) - ID или slug категории
- `page` (int, default: 1)
- `limit` (int, default: 20)
- `sort` (string, optional) - `price_asc`, `price_desc`, `newest`, `popular`
- `search` (string, optional) - поисковый запрос

**Ответ:**
```json
{
  "items": [
    {
      "id": 1,
      "name": "Dobe Зарядная станция для геймпадов PS4 Dualshock 4",
      "slug": "dobe-charging-station-ps4",
      "price": 899,
      "image": "/images/products/dobe-charging-station.jpg",
      "category": { "id": 5, "name": "PlayStation Аксессуары" },
      "in_stock": true
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 50,
    "totalPages": 3
  }
}
```

#### GET `/api/products/{id}`
**Описание:** Детальная информация о товаре  
**Ответ:**
```json
{
  "id": 1,
  "name": "Dobe Зарядная станция для геймпадов PS4 Dualshock 4",
  "slug": "dobe-charging-station-ps4",
  "description": "Магнитные коннекторы сберегут разъёмы Ваших геймпадов...",
  "price": 899,
  "images": [
    "/images/products/dobe-charging-station-1.jpg",
    "/images/products/dobe-charging-station-2.jpg"
  ],
  "category": { "id": 5, "name": "PlayStation Аксессуары", "slug": "playstation-accessories" },
  "attributes": {
    "brand": "Dobe",
    "compatibility": "PS4 Dualshock 4"
  },
  "in_stock": true,
  "related_products": [2, 3, 4]
}
```

#### GET `/api/categories`
**Описание:** Список категорий товаров  
**Ответ:**
```json
{
  "categories": [
    {
      "id": 1,
      "name": "PlayStation",
      "slug": "playstation",
      "children": [
        { "id": 2, "name": "PlayStation 2", "slug": "playstation-2", "product_count": 0 },
        { "id": 3, "name": "PlayStation 3", "slug": "playstation-3", "product_count": 0 },
        { "id": 4, "name": "PlayStation 4", "slug": "playstation-4", "product_count": 0 }
      ]
    }
  ]
}
```

### Корзина

#### GET `/api/cart`
**Описание:** Получение содержимого корзины  
**Заголовки:** `Authorization: Bearer {token}` (опционально, для авторизованных) или Cookie с session_id  
**Ответ:**
```json
{
  "items": [
    {
      "product_id": 1,
      "product_name": "Dobe Зарядная станция для геймпадов PS4",
      "price": 899,
      "quantity": 2,
      "total": 1798
    }
  ],
  "total": 1798,
  "total_items": 2
}
```

#### POST `/api/cart/add`
**Описание:** Добавление товара в корзину  
**Тело запроса:**
```json
{
  "product_id": 1,
  "quantity": 1
}
```
**Ответ:**
```json
{
  "success": true,
  "cart": {
    "items": [...],
    "total": 899,
    "total_items": 1
  }
}
```

#### PUT `/api/cart/update`
**Описание:** Обновление количества товара в корзине  
**Тело запроса:**
```json
{
  "product_id": 1,
  "quantity": 2
}
```

#### DELETE `/api/cart/remove`
**Описание:** Удаление товара из корзины  
**Тело запроса:**
```json
{
  "product_id": 1
}
```

#### POST `/api/cart/clear`
**Описание:** Очистка корзины

### Закладки (Wishlist)

#### GET `/api/wishlist`
**Описание:** Получение списка закладок  
**Заголовки:** `Authorization: Bearer {token}`

#### POST `/api/wishlist/add`
**Описание:** Добавление товара в закладки  
**Заголовки:** `Authorization: Bearer {token}`  
**Тело запроса:**
```json
{
  "product_id": 1
}
```

#### DELETE `/api/wishlist/remove`
**Описание:** Удаление товара из закладок  
**Заголовки:** `Authorization: Bearer {token}`  
**Тело запроса:**
```json
{
  "product_id": 1
}
```

### Заказы

#### POST `/api/orders/create`
**Описание:** Создание заказа  
**Заголовки:** `Authorization: Bearer {token}`  
**Тело запроса:**
```json
{
  "items": [
    { "product_id": 1, "quantity": 2 }
  ],
  "delivery": {
    "type": "city", // "city" | "pickup" | "courier"
    "address": "ул. Примерная, д. 1",
    "free_delivery": true
  },
  "payment": {
    "type": "cash" // "cash" | "card" | "online"
  },
  "contact": {
    "name": "Иван Иванов",
    "phone": "+79279888870",
    "email": "user@example.com"
  }
}
```
**Ответ:**
```json
{
  "success": true,
  "order_id": 123,
  "order_number": "ORD-2024-001",
  "total": 1798
}
```

#### GET `/api/orders`
**Описание:** Список заказов пользователя  
**Заголовки:** `Authorization: Bearer {token}`  
**Параметры:**
- `page` (int, default: 1)
- `limit` (int, default: 10)

#### GET `/api/orders/{id}`
**Описание:** Детали заказа  
**Заголовки:** `Authorization: Bearer {token}`

### Ремонт и услуги

#### GET `/api/repair/price-list`
**Описание:** Получение прайс-листа на ремонт  
**Ответ:**
```json
{
  "categories": [
    {
      "name": "XBOX SERIES X|S",
      "services": [
        {
          "name": "Устройство включается и на экране телевизора расс...",
          "price": 0,
          "description": "..."
        }
      ]
    }
  ]
}
```

#### POST `/api/repair/request`
**Описание:** Создание заявки на ремонт  
**Тело запроса:**
```json
{
  "device_type": "xbox-series-x",
  "problem": "Описание проблемы",
  "contact": {
    "name": "Иван Иванов",
    "phone": "+79279888870",
    "email": "user@example.com"
  }
}
```

#### POST `/api/buy/estimate`
**Описание:** Оценка товара для покупки  
**Тело запроса:**
```json
{
  "device_type": "ps4",
  "condition": "good",
  "description": "Описание состояния",
  "contact": {
    "name": "Иван Иванов",
    "phone": "+79279888870"
  }
}
```

### Поиск

#### GET `/api/search`
**Описание:** Поиск товаров  
**Параметры:**
- `q` (string, required) - поисковый запрос
- `page` (int, default: 1)
- `limit` (int, default: 20)

---

## Функциональные требования для нового сайта

### Обязательные функции

1. **Каталог товаров**
   - Категории и подкатегории
   - Фильтрация по категориям
   - Поиск товаров
   - Сортировка (цена, новизна, популярность)
   - Пагинация
   - Детальные страницы товаров

2. **Корзина и заказы**
   - Добавление товаров в корзину
   - Редактирование корзины
   - Оформление заказа
   - История заказов (для авторизованных)

3. **Авторизация**
   - Регистрация
   - Вход/выход
   - Восстановление пароля
   - Личный кабинет

4. **Закладки**
   - Добавление товаров в избранное
   - Просмотр списка закладок

5. **Услуги**
   - Прайс-лист на ремонт
   - Форма заявки на ремонт
   - Форма оценки товара для покупки

6. **Информационные страницы**
   - О нас
   - Доставка и оплата
   - Контакты

### Дополнительные функции

1. **Уведомления**
   - Email-уведомления о заказах
   - Уведомления о статусе заказа

2. **Административная панель**
   - Управление товарами
   - Управление заказами
   - Управление категориями
   - Управление пользователями

3. **Интеграции**
   - Платежные системы
   - Службы доставки
   - Email-рассылки

---

## Технические детали

### Структура данных

#### Товар (Product)
- id
- name
- slug
- description
- price
- images (массив)
- category_id
- attributes (JSON)
- in_stock (boolean)
- created_at
- updated_at

#### Категория (Category)
- id
- name
- slug
- parent_id (для вложенных категорий)
- product_count

#### Заказ (Order)
- id
- order_number
- user_id (nullable, для гостевых заказов)
- status
- total
- items (JSON или связь)
- delivery_info (JSON)
- payment_info (JSON)
- contact_info (JSON)
- created_at

#### Корзина (Cart)
- session_id или user_id
- items (JSON или связь)
- updated_at

---

## Рекомендации по разработке

1. **Backend (Symfony 7.2)**
   - Модульная DDD-архитектура
   - CQRS для сложных операций
   - RESTful API
   - JWT авторизация
   - Пагинация через `PaginationDto` и `PaginationModel`

2. **Admin Frontend (Vue 3 + TypeScript)**
   - Модульная структура
   - Element Plus + TailwindCSS
   - Pinia для state management
   - Роутинг с lazy-loading

3. **Public Frontend (Vue 3)**
   - Адаптивный дизайн
   - Оптимизация для мобильных устройств
   - SEO-оптимизация

4. **Интеграции**
   - Платежные системы (ЮKassa, Сбербанк Эквайринг)
   - Email (Symfony Mailer)
   - SMS (для уведомлений)

---

## Примечания

- Сайт использует бесплатную доставку от 3000₽ по городу Ульяновск
- Контактный телефон: +7 (927) 988-88-70
- Сайт связан с console-repair.ru (возможно, партнерский проект)
- Товары включают как новые, так и б/у позиции
- Есть раздел услуг (ремонт и покупка товаров)
