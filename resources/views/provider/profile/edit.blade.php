@extends('layouts.app')

@section('content')
    <section class="section shell">
        <div class="content">
            <h1>Edit your business profile</h1>
            @if (session('status'))<p class="notice">{{ session('status') }}</p>@endif
            @if ($errors->any())<div class="notice" role="alert">Please correct the highlighted fields before saving.</div>@endif
            <div class="form-card">
                <form class="lead-form" method="post" action="{{ route('provider.profile.update') }}">
                    @csrf
                    @method('put')
                    <div class="form-grid">
                        <div class="field"><label for="business_name">Business name</label><input id="business_name" name="business_name" value="{{ old('business_name', $provider->business_name) }}" required>@error('business_name')<span class="error">{{ $message }}</span>@enderror</div>
                        <div class="field"><label for="phone">Business phone</label><input id="phone" type="tel" name="phone" value="{{ old('phone', $provider->phone) }}" required>@error('phone')<span class="error">{{ $message }}</span>@enderror</div>
                        <div class="field"><label for="website">Website</label><input id="website" type="url" name="website" value="{{ old('website', $provider->website) }}">@error('website')<span class="error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="field"><label for="description">About your business</label><textarea id="description" name="description" maxlength="3000">{{ old('description', $provider->description) }}</textarea></div>
                    <fieldset class="field" style="border:0;padding:0">
                        <legend><strong>Services</strong></legend>
                        <div class="grid">
                            @foreach ($categories as $category)
                                <label><input type="checkbox" name="category_ids[]" value="{{ $category->id }}" @checked(in_array($category->id, old('category_ids', $provider->categories->modelKeys())))> {{ $category->name }}</label>
                            @endforeach
                        </div>
                        @error('category_ids')<span class="error">{{ $message }}</span>@enderror
                    </fieldset>
                    <fieldset class="field" style="border:0;padding:0">
                        <legend><strong>Service areas</strong></legend>
                        <div class="grid">
                            @foreach ($cities as $city)
                                <label><input type="checkbox" name="city_ids[]" value="{{ $city->id }}" @checked(in_array($city->id, old('city_ids', $provider->serviceAreas->modelKeys())))> {{ $city->name }}, {{ $city->country->code }}</label>
                            @endforeach
                        </div>
                        @error('city_ids')<span class="error">{{ $message }}</span>@enderror
                    </fieldset>
                    <button class="button" type="submit">Save profile</button>
                </form>
            </div>
            <section class="section">
                <h2>Your data and account</h2>
                <p class="muted">Download a copy of your account data or permanently remove your listing and login. Financial records may be retained where required by law.</p>
                <p><a class="button light" href="{{ route('provider.data.export') }}">Download my data</a></p>
                <div class="form-card">
                    <h3>Remove your account</h3>
                    <form class="lead-form" method="post" action="{{ route('provider.account.erase') }}">
                        @csrf
                        @method('delete')
                        <div class="field"><label for="delete-password">Password</label><input id="delete-password" name="password" type="password" autocomplete="current-password" required></div>
                        <div class="field"><label for="delete-confirmation">Type DELETE to confirm</label><input id="delete-confirmation" name="confirmation" required></div>
                        <button class="button" type="submit">Remove account</button>
                    </form>
                </div>
            </section>
        </div>
    </section>
@endsection
