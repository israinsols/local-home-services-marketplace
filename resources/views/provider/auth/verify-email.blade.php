@extends('layouts.app')

@section('content')
    <section class="section shell">
        <div class="content">
            <h1>Verify your email address</h1>
            @if (session('status'))<p class="notice">{{ session('status') }}</p>@endif
            <p class="muted">We sent a verification link to your business email. Open it to activate your provider login.</p>
            <form method="post" action="{{ route('verification.send') }}">
                @csrf
                <button class="button" type="submit">Send a new verification link</button>
            </form>
            <form method="post" action="{{ route('provider.logout') }}" style="margin-top:16px">
                @csrf
                <button class="button light" type="submit">Sign out</button>
            </form>
        </div>
    </section>
@endsection
