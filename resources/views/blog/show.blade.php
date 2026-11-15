@extends('layouts.app')

@section('content')
    <article class="section shell content">
        <nav class="crumbs" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a> / <a href="{{ route('blog.index') }}">Guides</a> / {{ $post->title }}
        </nav>
        <span class="eyebrow">Home improvement guide</span>
        <h1>{{ $post->title }}</h1>
        <time class="muted" datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j, Y') }}</time>
        @if ($post->excerpt)<p class="muted">{{ $post->excerpt }}</p>@endif
        <div class="content">{!! $renderedBody !!}</div>
    </article>
@endsection
