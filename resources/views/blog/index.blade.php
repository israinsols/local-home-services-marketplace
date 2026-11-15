@extends('layouts.app')

@section('content')
    <section class="section shell">
        <span class="eyebrow">Advice for your home</span>
        <h1>Guides and ideas</h1>
        @if ($posts->count())
            <div class="grid">
                @foreach ($posts as $post)
                    <a class="card" href="{{ route('blog.show', $post) }}">
                        <h2>{{ $post->title }}</h2>
                        <p class="muted">{{ $post->excerpt }}</p>
                        <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j, Y') }}</time>
                    </a>
                @endforeach
            </div>
            <div class="section">{{ $posts->links() }}</div>
        @else
            <p class="muted">Our practical home guides are on the way.</p>
        @endif
    </section>
@endsection
