<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class EmailVerificationController extends Controller
{
    public function notice(): View|RedirectResponse
    {
        if (request()->user()->hasVerifiedEmail()) {
            return redirect()->route('provider.dashboard');
        }

        return view('provider.auth.verify-email', [
            'title' => 'Verify your email | '.config('marketplace.name'),
            'description' => 'Verify your email address to access your provider account.',
            'indexable' => false,
        ]);
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('provider.dashboard');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->route('provider.dashboard')->with('status', 'Your email address has been verified.');
    }

    public function resend(): RedirectResponse
    {
        if (request()->user()->hasVerifiedEmail()) {
            return redirect()->route('provider.dashboard');
        }

        request()->user()->sendEmailVerificationNotification();

        return back()->with('status', 'A new verification link has been sent.');
    }
}
