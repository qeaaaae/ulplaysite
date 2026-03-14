# Дизайн-система ulplay.com

## Концепция дизайна

**Тематика:** Современный интернет-магазин игровых консолей и аксессуаров  
**Стиль:** Минималистичный, сдержанный, элегантный  
**Настроение:** Спокойный, профессиональный, надежный

**Принципы:**
- Единый стиль во всех элементах
- Сдержанная, но современная цветовая палитра
- Минималистичные анимации (только необходимые, быстрые)
- Чистый и понятный интерфейс
- Фокус на контенте, а не на эффектах
- Профессиональный, современный вид
- Красиво, но без излишеств

**Чего избегать:**
- ❌ Ярких, пёстрых цветов
- ❌ Тяжелых анимаций и эффектов
- ❌ Излишних теней и градиентов
- ❌ Сложных переходов и трансформаций
- ❌ Элементов, которые выглядят "сделанными ИИ"
- ❌ Разнообразия стилей на одной странице

---

## Общие рекомендации по стилю

### Единообразие
- Все кнопки одного типа должны выглядеть одинаково
- Все карточки товаров должны иметь одинаковый стиль
- Использовать одинаковые отступы и размеры везде
- Единая система скруглений (только `rounded-lg`)

### Сдержанность
- Максимум 2-3 цвета на странице (основной, текст, акцент)
- Акцентный цвет используется минимально (только для важных действий)
- Без градиентов и сложных эффектов
- Простые, понятные формы

### Профессионализм
- Чистый, минималистичный дизайн
- Фокус на читаемости и удобстве
- Без "искусственных" элементов
- Естественный вид интерфейса

---

## Цветовая палитра

### Основные цвета

#### Accent (Основной акцентный цвет)
**Цвет:** `#2563EB` (Сдержанный синий)  
**TailwindCSS:** `blue-600`  
**Использование:**
- Кнопки основных действий (Купить, Добавить в корзину)
- Активные ссылки
- Важные акценты

**RGB:** `rgb(37, 99, 235)`  
**Hex:** `#2563EB`

#### Secondary (Вторичный акцентный цвет)
**Цвет:** `#475569` (Серо-синий, сдержанный)  
**TailwindCSS:** `slate-600`  
**Использование:**
- Кнопки второстепенных действий
- Бейджи и метки
- Второстепенные акценты

**RGB:** `rgb(71, 85, 105)`  
**Hex:** `#475569`

### Текст

#### Основной текст
**Цвет:** `#1E293B` (Темно-серый с синеватым оттенком)  
**TailwindCSS:** `slate-800`  
**Использование:**
- Основной текст на страницах
- Заголовки (H1, H2, H3)
- Описания товаров
- Контент статей

**RGB:** `rgb(30, 41, 59)`  
**Hex:** `#1E293B`

#### Вторичный текст
**Цвет:** `#64748B` (Средне-серый)  
**TailwindCSS:** `slate-500`  
**Использование:**
- Вспомогательный текст
- Подписи к изображениям
- Мета-информация (даты, категории)
- Placeholder текст

**RGB:** `rgb(100, 116, 139)`  
**Hex:** `#64748B`

#### Светлый текст
**Цвет:** `#94A3B8` (Светло-серый)  
**TailwindCSS:** `slate-400`  
**Использование:**
- Текст на темном фоне
- Неактивные элементы
- Разделители

**RGB:** `rgb(148, 163, 184)`  
**Hex:** `#94A3B8`

### Фон

#### Основной фон
**Цвет:** `#F8FAFC` (Очень светло-серый с голубоватым оттенком)  
**TailwindCSS:** `slate-50`  
**Использование:**
- Фон страниц
- Фон секций
- Фон карточек (опционально)

**RGB:** `rgb(248, 250, 252)`  
**Hex:** `#F8FAFC`

#### Фон карточек и блоков
**Цвет:** `#FFFFFF` (Белый)  
**TailwindCSS:** `white`  
**Использование:**
- Карточки товаров
- Модальные окна
- Формы
- Блоки контента

**RGB:** `rgb(255, 255, 255)`  
**Hex:** `#FFFFFF`

#### Темный фон (опционально)
**Цвет:** `#0F172A` (Очень темно-синий)  
**TailwindCSS:** `slate-900`  
**Использование:**
- Footer
- Темные секции
- Header (опционально)

**RGB:** `rgb(15, 23, 42)`  
**Hex:** `#0F172A`

### Дополнительные цвета

#### Успех
**Цвет:** `#059669` (Сдержанный зеленый)  
**TailwindCSS:** `emerald-600`  
**Использование:** Успешные действия, подтверждения (минимально)

#### Ошибка
**Цвет:** `#DC2626` (Сдержанный красный)  
**TailwindCSS:** `red-600`  
**Использование:** Ошибки (только когда необходимо)

#### Предупреждение
**Цвет:** `#D97706` (Сдержанный оранжевый)  
**TailwindCSS:** `amber-600`  
**Использование:** Предупреждения (минимально)

#### Информация
**Цвет:** `#2563EB` (Тот же accent цвет)  
**TailwindCSS:** `blue-600`  
**Использование:** Информационные сообщения

---

## Типографика

### Шрифты

**Основной шрифт:** Inter или System UI  
**Fallback:** `-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif`

**Моноширинный:** `ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace`

### Размеры шрифтов

- **H1 (Главный заголовок):** `text-4xl` (36px) - `font-bold`
- **H2 (Заголовок секции):** `text-3xl` (30px) - `font-bold`
- **H3 (Подзаголовок):** `text-2xl` (24px) - `font-semibold`
- **H4:** `text-xl` (20px) - `font-semibold`
- **Основной текст:** `text-base` (16px) - `font-normal`
- **Мелкий текст:** `text-sm` (14px) - `font-normal`
- **Очень мелкий:** `text-xs` (12px) - `font-normal`

### Высота строк

- **Заголовки:** `leading-tight` (1.25)
- **Основной текст:** `leading-relaxed` (1.625)
- **Компактный текст:** `leading-normal` (1.5)

---

## Компоненты

### Кнопки

#### Primary (Основная кнопка)
```html
<button class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg transition-colors duration-150">
  Купить
</button>
```

#### Secondary (Вторичная кнопка)
```html
<button class="bg-slate-600 hover:bg-slate-700 text-white font-medium px-6 py-2.5 rounded-lg transition-colors duration-150">
  Подробнее
</button>
```

#### Outline (Контурная кнопка)
```html
<button class="border border-slate-300 text-slate-800 hover:bg-slate-50 font-medium px-6 py-2.5 rounded-lg transition-colors duration-150">
  В корзину
</button>
```

#### Ghost (Прозрачная кнопка)
```html
<button class="text-slate-700 hover:text-slate-900 hover:bg-slate-100 font-medium px-4 py-2 rounded-lg transition-colors duration-150">
  Отмена
</button>
```

### Карточки товаров

```html
<div class="bg-white rounded-lg border border-slate-200 hover:border-slate-300 overflow-hidden transition-colors duration-150">
  <div class="relative">
    <img src="..." alt="..." class="w-full h-48 object-cover">
    <span class="absolute top-2 right-2 bg-blue-600 text-white text-xs font-medium px-2 py-0.5 rounded">Новинка</span>
  </div>
  <div class="p-4">
    <h3 class="text-base font-medium text-slate-800 mb-1.5 line-clamp-2">Название товара</h3>
    <p class="text-slate-500 text-sm mb-3 line-clamp-2">Описание товара...</p>
    <div class="flex justify-between items-center">
      <span class="text-xl font-semibold text-slate-900">899₽</span>
      <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-lg transition-colors duration-150">
        В корзину
      </button>
    </div>
  </div>
</div>
```

### Формы

#### Поле ввода
```html
<div class="mb-4">
  <label class="block text-slate-700 text-sm font-medium mb-1.5">Email</label>
  <input type="email" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 text-slate-800 text-sm">
</div>
```

#### Textarea
```html
<textarea class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 text-slate-800 text-sm" rows="4"></textarea>
```

#### Select
```html
<select class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 text-slate-800 bg-white text-sm">
  <option>Выберите...</option>
</select>
```

### Бейджи и метки

#### Новинка
```html
<span class="bg-blue-600 text-white text-xs font-medium px-2 py-0.5 rounded">Новинка</span>
```

#### Скидка
```html
<span class="bg-slate-700 text-white text-xs font-medium px-2 py-0.5 rounded">-20%</span>
```

#### В наличии
```html
<span class="bg-slate-200 text-slate-700 text-xs font-medium px-2 py-0.5 rounded">В наличии</span>
```

#### Нет в наличии
```html
<span class="bg-slate-100 text-slate-500 text-xs font-medium px-2 py-0.5 rounded">Нет в наличии</span>
```

### Навигация

#### Header
- Фон: `bg-white` или `bg-slate-50`
- Текст: `text-slate-800`
- Активная ссылка: `text-blue-600 font-semibold`
- Ховер: `hover:text-blue-600`

#### Footer
- Фон: `bg-slate-900`
- Текст: `text-slate-300`
- Ссылки: `hover:text-white`

### Модальные окна

```html
<div class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-lg max-w-md w-full mx-4 p-6 border border-slate-200">
    <h2 class="text-xl font-semibold text-slate-800 mb-3">Заголовок</h2>
    <p class="text-slate-600 mb-5 text-sm">Содержимое модального окна...</p>
    <div class="flex justify-end gap-2">
      <button class="px-4 py-2 text-slate-600 hover:bg-slate-50 rounded-lg text-sm transition-colors duration-150">Отмена</button>
      <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm transition-colors duration-150">Подтвердить</button>
    </div>
  </div>
</div>
```

### Уведомления (Toast)

#### Успех
```html
<div class="bg-white border border-emerald-200 text-slate-800 px-4 py-2.5 rounded-lg shadow-sm flex items-center gap-2 text-sm">
  <svg class="w-4 h-4 text-emerald-600">...</svg>
  <span>Товар добавлен в корзину</span>
</div>
```

#### Ошибка
```html
<div class="bg-white border border-red-200 text-slate-800 px-4 py-2.5 rounded-lg shadow-sm flex items-center gap-2 text-sm">
  <svg class="w-4 h-4 text-red-600">...</svg>
  <span>Произошла ошибка</span>
</div>
```

---

## Spacing (Отступы)

Использовать стандартную систему TailwindCSS:
- `p-2` (8px)
- `p-4` (16px)
- `p-6` (24px)
- `p-8` (32px)

Для контейнеров:
- Максимальная ширина контента: `max-w-7xl` (1280px)
- Отступы по бокам: `px-4` или `px-6`

---

## Тени

**Принцип:** Минимальное использование теней. Предпочтение границам.

- **Карточки:** `border border-slate-200` (без теней)
- **Кнопки:** без теней
- **Модальные окна:** `shadow-lg` (легкая тень)
- **Выпадающие меню:** `shadow-sm` или `border`
- **Без теней при ховере:** только изменение цвета границы

---

## Скругления

**Принцип:** Единые, сдержанные скругления.

- **Кнопки:** `rounded-lg` (8px)
- **Карточки:** `rounded-lg` (8px) - единообразно
- **Модальные окна:** `rounded-lg` (8px)
- **Бейджи:** `rounded` (4px) - не `rounded-full`
- **Поля ввода:** `rounded-lg` (8px)

---

## Переходы и анимации

**Принцип:** Минималистичные, легкие переходы. Без тяжелых анимаций и эффектов.

### Transition
- **Цвета:** `transition-colors duration-150` (быстрые, незаметные)
- **Все свойства:** `transition-colors duration-150` (только цвета, без теней и трансформаций)

### Hover эффекты
- **Кнопки:** только изменение цвета фона (`hover:bg-blue-700`)
- **Карточки:** изменение цвета границы (`hover:border-slate-300`)
- **Ссылки:** изменение цвета текста (`hover:text-blue-600`)
- **Без:** теней при ховере, трансформаций, масштабирования

### Запрещено
- ❌ `transform scale()` при ховере
- ❌ Сложные анимации (`@keyframes`)
- ❌ Тени при ховере (`hover:shadow-lg`)
- ❌ Плавное появление элементов (fade-in)
- ❌ Параллакс эффекты

---

## Адаптивность

### Breakpoints (TailwindCSS)
- **sm:** 640px
- **md:** 768px
- **lg:** 1024px
- **xl:** 1280px
- **2xl:** 1536px

### Примеры адаптивных классов
```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
  <!-- Карточки товаров -->
</div>
```

---

## Иконки

Рекомендуется использовать:
- **Heroicons** (через CDN или npm)
- **Lucide Icons** (альтернатива)
- SVG иконки для лучшей производительности

---

## Примеры использования цветов

### Главная страница
- Фон: `bg-slate-50`
- Заголовки: `text-slate-800`
- Акценты: `text-blue-600` (минимально)
- Кнопки: `bg-blue-600`
- Карточки: белый фон с границей `border-slate-200`

### Страница товара
- Фон: `bg-white`
- Цена: `text-slate-900 font-semibold` (не синий, сдержанно)
- Кнопка "Купить": `bg-blue-600`
- Кнопка "В корзину": `border border-slate-300 text-slate-800`

### Админка
- Фон: `bg-slate-50`
- Сайдбар: `bg-white` с границей `border-r border-slate-200`
- Активные элементы: `bg-blue-600` или `text-blue-600`
- Таблицы: `bg-white` с границами `border border-slate-200`

---

## TailwindCSS конфигурация

```javascript
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        accent: {
          DEFAULT: '#2563EB',
          50: '#EFF6FF',
          100: '#DBEAFE',
          500: '#2563EB',
          600: '#1D4ED8',
          700: '#1E40AF',
        },
        secondary: {
          DEFAULT: '#475569',
          50: '#F8FAFC',
          100: '#F1F5F9',
          500: '#64748B',
          600: '#475569',
          700: '#334155',
        },
      },
      transitionDuration: {
        '150': '150ms',
      },
    },
  },
}
```

---

## Чеклист дизайна

- [ ] Все цвета соответствуют сдержанной палитре (без ярких акцентов)
- [ ] Используются правильные размеры шрифтов
- [ ] Кнопки имеют единый стиль без излишних эффектов
- [ ] Карточки товаров единообразны (одинаковые скругления, границы)
- [ ] Формы имеют правильную валидацию и стили ошибок
- [ ] Адаптивность проверена на всех устройствах
- [ ] Переходы легкие и быстрые (150ms), без тяжелых анимаций
- [ ] Контрастность текста соответствует WCAG AA
- [ ] Нет пёстрых цветов и излишних эффектов
- [ ] Единый стиль во всех компонентах
- [ ] Минимальное использование теней (предпочтение границам)
- [ ] Дизайн выглядит профессионально и естественно

---

**Дата создания:** 2024  
**Версия:** 1.0
