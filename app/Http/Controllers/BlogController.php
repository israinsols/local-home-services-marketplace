<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('body', '!=', '')
            ->latest('published_at')
            ->paginate(10);

        return view('blog.index', [
            'posts' => $posts,
            'title' => 'Home improvement guides | '.config('marketplace.name'),
            'description' => 'Practical advice and guides for planning home repairs and improvements.',
            'indexable' => $posts->total() > 0,
        ]);
    }

    public function show(BlogPost $post): View
    {
        abort_unless($post->status === 'published' && $post->published_at?->isPast() && filled($post->body), 404);

        return view('blog.show', [
            'post' => $post,
            'renderedBody' => Str::markdown($post->body, ['html_input' => 'strip']),
            'title' => $post->meta_title ?: $post->title,
            'description' => $post->meta_description ?: ($post->excerpt ?: str($post->body)->stripTags()->limit(155)->toString()),
            'indexable' => true,
        ]);
    }
}
