@extends('layouts.app')

@section('content')
    <section class="section shell">
        <div class="content">
            <span class="eyebrow">Grow your local business</span>
            <h1>Join as a service provider.</h1>
            <p class="muted">Create a profile and choose the services and cities you cover. Accounts are reviewed before they can unlock leads.</p>
            @if ($errors->any())<div class="notice" role="alert">Please review the highlighted fields and try again.</div>@endif
            <div class="form-card">
                <form class="lead-form" method="post" action="{{ route('provider.register.store') }}">
                    @csrf
                    <div class="form-grid">
                        <div class="field"><label for="name">Your name</label><input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required>@error('name')<span class="error">{{ $message }}</span>@enderror</div>
                        <div class="field"><label for="email">Business email</label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>@error('email')<span class="error">{{ $message }}</span>@enderror</div>
                        <div class="field"><label for="business_name">Business name</label><input id="business_name" name="business_name" value="{{ old('business_name') }}" required>@error('business_name')<span class="error">{{ $message }}</span>@enderror</div>
                        <div class="field"><label for="phone">Business phone</label><input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" required>@error('phone')<span class="error">{{ $message }}</span>@enderror</div>
                        <div class="field"><label for="password">Password (12+ characters)</label><input id="password" type="password" name="password" autocomplete="new-password" required>@error('password')<span class="error">{{ $message }}</span>@enderror</div>
                        <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required></div>
                        <div class="field"><label for="website">Website (optional)</label><input id="website" type="url" name="website" value="{{ old('website') }}"></div>
                    </div>
                    <div class="field"><label for="description">About your business</label><textarea id="description" name="description" maxlength="3000">{{ old('description') }}</textarea></div>
                    <fieldset class="field" style="border:0;padding:0">
                        <legend><strong>Services you offer</strong></legend>
                        <div class="grid">
                            @foreach ($categories as $category)
                                <label><input type="checkbox" name="category_ids[]" value="{{ $category->id }}" @checked(in_array($category->id, old('category_ids', [])))> {{ $category->name }}</label>
                            @endforeach
                        </div>
                        @error('category_ids')<span class="error">{{ $message }}</span>@enderror
                    </fieldset>
                    <fieldset class="field" style="border:0;padding:0">
                        <legend><strong>Cities you cover</strong></legend>
                        <div class="grid">
                            @foreach ($cities as $city)
                                <label><input type="checkbox" name="city_ids[]" value="{{ $city->id }}" @checked(in_array($city->id, old('city_ids', [])))> {{ $city->name }}, {{ $city->country->code }}</label>
                            @endforeach
                        </div>
                        @error('city_ids')<span class="error">{{ $message }}</span>@enderror
                    </fieldset>
                    <div class="field">
                        <label><input type="checkbox" name="terms" value="1" @checked(old('terms')) required> I agree to the <a href="{{ route('landing.page', 'terms') }}">Terms</a> and <a href="{{ route('landing.page', 'privacy') }}">Privacy Policy</a>.</label>
                        @error('terms')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <button class="button" type="submit">Create provider account</button>
                </form>
            </div>
            <p>Already registered? <a href="{{ route('provider.login') }}">Sign in</a></p>
        </div>
    </section>
@endsection
