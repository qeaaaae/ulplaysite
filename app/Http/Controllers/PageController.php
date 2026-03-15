<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about');
    }

    public function delivery(): View
    {
        return view('pages.delivery');
    }

    public function contacts(): View
    {
        return view('pages.contacts');
    }
}
