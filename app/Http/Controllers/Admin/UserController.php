<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->input('q', '');
        $users = User::withTrashed()
            ->when($q !== '', fn ($query) => $query->where(fn ($q2) => $q2->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")))
            ->latest()
            ->paginate(10)
            ->withQueryString();
        return view('admin.users.index', ['users' => $users, 'search' => $q]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User()]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = $validated['email_verified'] ? now() : null;
        unset($validated['email_verified']);
        User::create($validated);
        return redirect()->route('admin.users.index')->with('message', 'Пользователь создан');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        $validated['email_verified_at'] = $validated['email_verified'] ? now() : null;
        unset($validated['email_verified']);
        $user->update($validated);
        return redirect()->route('admin.users.index')->with('message', 'Пользователь обновлён');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->getKey() === auth()->id()) {
            return back()->with('error', 'Нельзя удалить себя');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('message', 'Пользователь удалён');
    }

    public function restore(User $user): RedirectResponse
    {
        if ($user->getKey() === auth()->id()) {
            return back()->with('error', 'Нельзя восстановить себя');
        }
        $user->restore();
        return redirect()->route('admin.users.index')->with('message', 'Пользователь восстановлен');
    }

    public function block(User $user): RedirectResponse
    {
        if ($user->getKey() === auth()->id()) {
            return back()->with('error', 'Нельзя заблокировать себя');
        }
        $user->update(['is_blocked' => true]);
        return back()->with('message', 'Пользователь заблокирован');
    }

    public function unblock(User $user): RedirectResponse
    {
        $user->update(['is_blocked' => false]);
        return back()->with('message', 'Пользователь разблокирован');
    }
}
