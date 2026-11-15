<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CookieConsentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['choice' => ['required', 'in:accepted,rejected']]);

        return back()->withCookie(cookie(
            'cookie_consent',
            $data['choice'],
            60 * 24 * 180,
            '/',
            null,
            $request->isSecure(),
            false,
            false,
            'lax',
        ));
    }
}
