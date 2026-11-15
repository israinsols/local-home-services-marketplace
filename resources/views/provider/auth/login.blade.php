@extends('layouts.app')

@section('content')
    <section class="section shell">
        <div class="content">
            <h1>Provider sign in</h1>
            @if ($errors->any())<p class="error" role="alert">{{ $errors->first() }}</p>@endif
            <div class="form-card">
                <form class="lead-form" method="post" action="{{ route('provider.login.store') }}">
                    @csrf
                    <div class="field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required></div>
                    <div class="field"><label for="password">Password</label><input id="password" type="password" name="password" autocomplete="current-password" required></div>
                    <label><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
                    <button class="button" type="submit">Sign in</button>
                </form>
            </div>
            <p>New to {{ $siteName }}? <a href="{{ route('provider.register') }}">Create a provider account</a></p>
        </div>
    </section>
@endsection
