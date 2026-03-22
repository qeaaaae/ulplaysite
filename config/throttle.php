<?php

return [

    'auth' => [
        'max_attempts' => 5,
        'decay_minutes' => 1,
    ],

    'password' => [
        'max_attempts' => 3,
        'decay_minutes' => 1,
    ],

    'cart' => [
        'max_attempts' => 30,
        'decay_minutes' => 1,
    ],

    'orders' => [
        'max_attempts' => 10,
        'decay_minutes' => 1,
    ],

    'support' => [
        'max_attempts' => 5,
        'decay_minutes' => 1,
    ],

    'profile' => [
        'max_attempts' => 10,
        'decay_minutes' => 1,
    ],

    'reviews' => [
        'max_attempts' => 10,
        'decay_minutes' => 1,
    ],

    'admin' => [
        'max_attempts' => 120,
        'decay_minutes' => 1,
    ],

];
