@extends('layouts.app')

@section('content')
    <section class="section shell">
        <div class="content">
            <h1>Payment confirmation in progress</h1>
            <p class="notice">Your credits are added after Stripe confirms payment. This can take a short time. Refresh your provider dashboard to see the updated balance.</p>
            <a class="button" href="{{ route('provider.dashboard') }}">Return to dashboard</a>
        </div>
    </section>
@endsection
