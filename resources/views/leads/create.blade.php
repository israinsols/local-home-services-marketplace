@extends('layouts.app')

@section('content')
    <section class="section shell">
        <div class="content">
            <span class="eyebrow">Free, with no obligation</span>
            <h1>Tell us about your home project.</h1>
            <p class="muted">We will match your request with relevant local professionals. Your contact details are only shared with matched providers.</p>
            <div class="form-card">
                @include('partials.lead-form')
            </div>
        </div>
    </section>
@endsection
