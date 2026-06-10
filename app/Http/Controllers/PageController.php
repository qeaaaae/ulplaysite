<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AboutPageSetting;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        $about = AboutPageSetting::current();
        $about->load('images');

        return view('pages.about', [
            'metaTitle' => 'О нас',
            'about' => $about,
        ]);
    }

    public function delivery(): View
    {
        return view('pages.delivery', ['metaTitle' => 'Доставка и оплата']);
    }

    public function contacts(): View
    {
        return view('pages.contacts', ['metaTitle' => 'Контакты']);
    }
}
