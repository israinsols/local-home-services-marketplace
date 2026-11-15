@extends('layouts.app')

@section('content')
    <section class="section shell">
        <div class="content">
            <span class="eyebrow">Thank you</span>
            <h1>Your request has been received.</h1>
            <p class="muted">We will share it with matching service professionals. If they are interested, they will contact you using the details you provided.</p>
            <a class="button" href="{{ route('home') }}">Back to home</a>
        </div>
    </section>
@endsection
