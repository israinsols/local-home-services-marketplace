<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? $siteName }}</title>
    <meta name="description" content="{{ $description ?? 'Find trusted local home service professionals.' }}">
    <meta name="robots" content="{{ ($indexable ?? true) ? 'index,follow' : 'noindex,follow' }}">
    <link rel="canonical" href="{{ $canonical ?? request()->url() }}">
    <link rel="alternate" hreflang="x-default" href="{{ $canonical ?? request()->url() }}">
    @isset($city)
        <link rel="alternate" hreflang="{{ $city->country->locale }}" href="{{ $canonical ?? request()->url() }}">
    @endisset
    @isset($schema)
        <script type="application/ld+json">@json($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    @endisset
    @isset($breadcrumbs)
        @php
            $breadcrumbSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => collect($breadcrumbs)->values()->map(function ($item, $index) {
                    return ['@type' => 'ListItem', 'position' => $index + 1, 'name' => $item['name'], 'item' => $item['url']];
                }),
            ];
        @endphp
        <script type="application/ld+json">@json($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    @endisset
    @stack('schema')
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title ?? $siteName }}">
    <meta property="og:description" content="{{ $description ?? 'Find trusted local home service professionals.' }}">
    <meta property="og:url" content="{{ $canonical ?? request()->url() }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $title ?? $siteName }}">
    <meta name="twitter:description" content="{{ $description ?? 'Find trusted local home service professionals.' }}">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            :root{font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#17241d;background:#fbfcfa;--green:#21664c;--muted:#64736a;--line:#e4eae5}
            *{box-sizing:border-box}body{margin:0}a{color:inherit}button,input,select,textarea{font:inherit}.shell{width:min(100% - 48px,1180px);margin:auto}.bar{background:#fff;border-bottom:1px solid var(--line)}.nav{min-height:76px;display:flex;align-items:center;justify-content:space-between;gap:24px}.brand{display:inline-flex;align-items:center;gap:10px;font-size:21px;font-weight:800;text-decoration:none}.brand-mark{width:34px;height:34px;display:grid;place-items:center;border-radius:11px;background:var(--green);color:#fff}.brand-mark svg{width:19px;height:19px}.nav-links{display:flex;align-items:center;gap:20px}.nav-links a{text-decoration:none;font-size:14px;font-weight:650}.button{min-height:48px;display:inline-flex;align-items:center;justify-content:center;padding:0 20px;border:1px solid var(--green);border-radius:10px;background:var(--green);color:#fff;text-decoration:none;font-weight:700;cursor:pointer}.button.light{border-color:#d9e8dc;background:#f0f6f1;color:#194f3b}.section{padding:60px 0}.content{max-width:800px}.content p,.muted{color:var(--muted);line-height:1.7}.eyebrow{color:var(--green);font-size:11px;font-weight:800;letter-spacing:.13em;text-transform:uppercase}.form-card,.card{padding:24px;border:1px solid var(--line);border-radius:15px;background:#fff}.lead-form{display:grid;gap:22px}.field{display:grid;gap:8px}.field label{font-size:13px;font-weight:700}.field input,.field select,.field textarea{width:100%;min-height:46px;padding:11px 12px;border:1px solid #d5dfd7;border-radius:8px;background:#fff}.field textarea{min-height:120px}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.lead-progress__count{margin:0 0 12px;color:var(--muted);font-size:13px;font-weight:700}.lead-progress ol{display:flex;margin:0;padding:0;list-style:none}.lead-progress li{flex:1;display:flex;align-items:center;gap:8px;color:#78857c;font-size:12px}.lead-progress li>span:first-child{width:32px;height:32px;display:grid;place-items:center;border:1px solid #dce5dd;border-radius:50%;background:#fff}.lead-progress li[aria-current=step]>span:first-child,.lead-progress li[data-complete=true]>span:first-child{background:var(--green);color:#fff}.lead-step h2{font-size:23px}.lead-step__intro{margin:6px 0 20px;color:var(--muted);font-size:14px;line-height:1.6}.lead-step__actions{margin-top:24px}.lead-stepper-controls{display:none}.lead-form.is-enhanced .lead-stepper-controls{display:flex;align-items:center;justify-content:space-between;gap:12px}.lead-form.is-enhanced .lead-submit-fallback{display:none}.lead-step .field+.field,.lead-step .form-grid+.field,.lead-step .field+.form-grid{margin-top:16px}.lead-review{margin:18px 0;border-top:1px solid var(--line)}.lead-review>div{display:grid;grid-template-columns:140px 1fr;gap:16px;padding:11px 0;border-bottom:1px solid var(--line)}.lead-review dt{color:var(--muted);font-size:13px}.lead-review dd{margin:0;font-size:13px;overflow-wrap:anywhere}.lead-review [data-review-value=details]{white-space:pre-wrap}.lead-privacy-note,.notice{padding:12px 14px;border-radius:9px;background:#f2f7f2;color:#40564a;font-size:12px;line-height:1.6}.lead-consent label{display:flex;align-items:flex-start;gap:9px;font-size:13px;line-height:1.6}.lead-consent input[type=checkbox]{width:17px;height:17px;flex:none;margin-top:2px;accent-color:var(--green)}.lead-error-summary{padding:14px 16px;border:1px solid #f1c7c3;border-radius:10px;background:#fff5f4;color:#8f2821;font-size:13px}.lead-error-summary ul{margin:8px 0 0;padding-left:20px}.error{color:#b42318;font-size:13px}.badge{display:inline-flex;padding:5px 9px;border-radius:99px;background:#e8f4ec;color:#176044;font-size:12px;font-weight:700}.badge-demo{margin:4px 0 10px;background:#fff3d8;color:#80530c}.footer{margin-top:60px;padding:36px 0;background:#17291f;color:#e3eee7}.footer a{color:#e3eee7}.footer nav{display:flex;flex-wrap:wrap;gap:12px 20px;margin-top:18px}.cookie-banner{position:fixed;z-index:20;right:18px;bottom:18px;width:min(390px,calc(100vw - 32px));padding:18px;border:1px solid var(--line);border-radius:14px;background:#fff;box-shadow:0 14px 42px #14251e2e}.cookie-banner form{display:flex;flex-wrap:wrap;gap:9px;margin-top:12px}
            .hero{padding:56px 0;background:#f3f7f2}.hero-grid{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(280px,.75fr);align-items:center;gap:40px}.hero h1{max-width:680px;margin:14px 0;font-size:clamp(38px,5vw,62px);line-height:1.04;letter-spacing:-.05em}.hero-copy>p{color:#53665c;line-height:1.65}.hero-panel{padding:26px;border:1px solid var(--line);border-radius:18px;background:#fff}.hero-panel-heading{display:flex;align-items:center;gap:12px;margin-bottom:20px}.hero-panel-icon{width:42px;height:42px;display:grid;place-items:center;border-radius:12px;background:#eaf3ed;color:var(--green)}.hero-panel-icon svg{width:22px;height:22px}.hero-panel-heading strong,.hero-panel-heading span{display:block}.hero-panel-heading span{margin-top:3px;color:var(--muted);font-size:12px}.steps{display:grid;gap:12px;padding:0;list-style:none}.steps li{display:flex;gap:12px}.step-number{width:32px;height:32px;display:grid;flex:none;place-items:center;border:1px solid #dce8df;border-radius:50%;color:var(--green)}.steps strong,.steps small{display:block}.steps small{margin-top:4px;color:var(--muted)}.hero-panel-note{margin-top:18px;padding:12px;border-radius:9px;background:#f2f7f2;color:#40564a;font-size:12px}.search-form{grid-template-columns:1fr 1fr auto;align-items:end;max-width:790px;margin-top:24px;padding:15px;border:1px solid var(--line);border-radius:13px;background:#fff}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px}.section-heading{display:flex;align-items:end;justify-content:space-between;gap:20px;margin-bottom:24px}.section-heading h2{margin:8px 0 0;font-size:30px}.section-heading p{max-width:460px;color:var(--muted);line-height:1.6}.card h3{margin:0 0 8px}.service-card__icon{width:38px;height:38px;display:grid;place-items:center;margin-bottom:14px;border-radius:11px;background:#eff5ef;color:var(--green);font-weight:800}.service-card p{color:var(--muted);font-size:13px;line-height:1.55}.service-card__arrow{float:right;color:#72947a}.section-tint{background:#f4f7f3}.city-list{display:flex;flex-wrap:wrap;gap:9px}.city-link{display:inline-flex;align-items:center;gap:7px;min-height:40px;padding:0 13px;border:1px solid var(--line);border-radius:99px;background:#fff;font-size:13px;text-decoration:none}.city-link svg{width:15px;height:15px}.cta-panel{display:flex;align-items:center;justify-content:space-between;gap:28px;padding:36px;border-radius:18px;background:#194f3b;color:#fff}.cta-panel h2{margin:11px 0;font-size:32px}.cta-panel p{max-width:600px;color:#d5e5d8}.cta-panel .eyebrow{color:#b6d6bd}.cta-panel .button{background:#fff;color:#194f3b}.content h1{margin:10px 0;font-size:36px}.notice{padding:12px 16px}
            @media(max-width:700px){.shell{width:min(100% - 36px,1180px)}.nav{flex-wrap:wrap;padding:12px 0}.nav-links{width:100%;justify-content:space-between;gap:8px;padding-top:10px}.nav-links a{font-size:12px}.form-grid,.hero-grid,.search-form{grid-template-columns:1fr}.form-card{padding:20px}.lead-review>div{grid-template-columns:95px 1fr;gap:10px}.section-heading,.cta-panel{align-items:flex-start;flex-direction:column}.cta-panel{padding:24px}.cta-panel h2{font-size:26px}.hero{padding:42px 0}.hero h1{font-size:40px}.search-form .button{width:100%}}
        </style>
    @endif
    @if (isset($providers) && method_exists($providers, 'previousPageUrl'))
        @if ($providers->previousPageUrl())<link rel="prev" href="{{ $providers->previousPageUrl() }}">@endif
        @if ($providers->nextPageUrl())<link rel="next" href="{{ $providers->nextPageUrl() }}">@endif
    @endif
    @if (isset($posts) && method_exists($posts, 'previousPageUrl'))
        @if ($posts->previousPageUrl())<link rel="prev" href="{{ $posts->previousPageUrl() }}">@endif
        @if ($posts->nextPageUrl())<link rel="next" href="{{ $posts->nextPageUrl() }}">@endif
    @endif
</head>
<body>
    <header class="bar">
        <div class="shell nav">
            <a class="brand" href="{{ route('home') }}">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-6v-7h-4v7H4a1 1 0 0 1-1-1V10Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                {{ $siteName }}
            </a>
            <nav class="nav-links" aria-label="Main navigation">
                <a href="{{ route('blog.index') }}">Guides</a>
                <a href="{{ route('provider.login') }}">Sign in</a>
                <a href="{{ route('provider.register') }}">For providers</a>
                <a href="{{ route('leads.create') }}" class="button">Free quotes</a>
            </nav>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
    <footer class="footer">
        <div class="shell">
            <p><strong>{{ $siteName }}</strong> — a simpler way to find local home service professionals.</p>
            <nav aria-label="Legal">
                <a href="{{ route('landing.page', 'about') }}">About</a>
                <a href="{{ route('landing.page', 'contact') }}">Contact</a>
                <a href="{{ route('landing.page', 'privacy') }}">Privacy</a>
                <a href="{{ route('landing.page', 'terms') }}">Terms</a>
                <a href="{{ route('landing.page', 'cookie-policy') }}">Cookie policy</a>
                <a href="{{ route('sitemap.index') }}">Sitemap</a>
            </nav>
            @if ($contactEmail)<p><a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></p>@endif
        </div>
    </footer>
    <aside class="cookie-banner" aria-label="Cookie information">
        <strong>Cookie choices</strong>
        <p>Essential cookies keep the service secure. Optional analytics cookies are not active. You can reject optional cookies and continue using the site.</p>
        <a href="{{ route('landing.page', 'cookie-policy') }}">Read our cookie policy</a>
        <form method="post" action="{{ route('cookie-consent.store') }}">
            @csrf
            <button class="button" type="submit" name="choice" value="accepted">Accept optional cookies</button>
            <button class="button light" type="submit" name="choice" value="rejected">Reject optional cookies</button>
        </form>
    </aside>
    <script>
        if (document.cookie.split('; ').some((cookie) => cookie.startsWith('cookie_consent='))) {
            document.querySelector('.cookie-banner')?.setAttribute('hidden', '');
        }
    </script>
</body>
</html>
