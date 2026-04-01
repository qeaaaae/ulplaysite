<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $orders = $user->orders()->latest()->take(5)->get();
        $reviews = $user->reviews()
            ->with(['reviewable', 'imagesRelation'])
            ->paginate(10)
            ->fragment('my-reviews');

        return view('profile.index', [
            'metaTitle' => 'Профиль',
            'user' => $user,
            'orders' => $orders,
            'reviews' => $reviews,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return redirect()->route('profile')->with('message', 'Профиль обновлён');
    }
}
