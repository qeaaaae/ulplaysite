<?php

return [
    'footer' => [
        'company' => [
            'name' => 'UlPlay',
            'description' => 'Интернет-магазин игровых консолей и аксессуаров в Ульяновске',
            'address' => 'г. Ульяновск, ул. Игошина, д. 3, подъезд 4, этаж 3, кв. 116',
            'phone' => '+7(927)988-88-70',
            'email' => 'info@ulplay.com',
            'schedule' => 'Пн–Сб 10:00–18:00, Вс–Пн выходной',
            'schedule_full' => 'с 10:00 до 18:00, обеденный перерыв с 13:00 до 14:00. Выходные: воскресенье, понедельник.',
            'visit_notice' => 'Просим вас заблаговременно уведомить о планируемом визите в связи с возможными изменениями в графике работы.',
        ],
        'categories' => [
            ['name' => 'PlayStation', 'url' => '/products'],
            ['name' => 'Xbox', 'url' => '/products'],
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
            ['name' => 'Авито', 'url' => 'https://www.avito.ru/user/7729b55d469973ddcb30209aedcf361b3eb7c348ab1d5d0a12ff7804a1bfa7b3/profile', 'icon' => 'avito'],
        ],
    ],
];
