<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function send(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                return response()->json(['result' => true, 'message' => 'Email уже подтверждён.']);
            }
            return back();
        }

        $request->user()->sendEmailVerificationNotification();

        if ($request->expectsJson()) {
            return response()->json([
                'result' => true,
                'message' => 'Ссылка для подтверждения email отправлена повторно.',
            ]);
        }

        return back()->with('message', 'Ссылка для подтверждения email отправлена повторно.');
    }
}
