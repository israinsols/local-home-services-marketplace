@extends('layouts.app')

@section('content')
    <article class="section shell content">
        <nav class="crumbs" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a> / <span aria-current="page">{{ $page->title }}</span>
        </nav>
        <h1>{{ $page->title }}</h1>
        <div class="content">{!! $renderedBody !!}</div>
    </article>
@endsection
