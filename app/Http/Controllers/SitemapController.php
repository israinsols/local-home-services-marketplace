<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    private const MAX_URLS = 50000;

    public function index(): Response
    {
        $entries = Cache::remember('sitemap.index', 3600, function (): array {
            $sections = ['categories', 'providers', 'blog', 'pages', 'gb-cities', 'gb-services', 'us-cities', 'us-services'];
            $entries = [[
                'url' => rtrim(config('app.url'), '/').'/sitemap-home.xml',
                'lastmod' => now()->toAtomString(),
            ]];

            foreach ($sections as $section) {
                $count = $this->queryFor($section)->count();

                if ($this->isGrouped($section)) {
                    $count = DB::query()->fromSub($this->queryFor($section), 'sitemap_rows')->count();
                }

                for ($page = 1; $page <= max(1, (int) ceil($count / self::MAX_URLS)); $page++) {
                    if ($count === 0) {
                        continue;
                    }

                    $entries[] = [
                        'url' => rtrim(config('app.url'), '/').'/sitemaps/'.$section.'-'.$page.'.xml',
                        'lastmod' => now()->toAtomString(),
                    ];
                }
            }

            return $entries;
        });

        $xml = $this->xmlHeader().'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($entries as $entry) {
            $xml .= '<sitemap><loc>'.$this->escape($entry['url']).'</loc><lastmod>'.$this->escape($entry['lastmod']).'</lastmod></sitemap>';
        }

        return $this->xmlResponse($xml.'</sitemapindex>');
    }

    public function home(): Response
    {
        return $this->xmlResponse(
            $this->xmlHeader().
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.
            '<url><loc>'.$this->escape(rtrim(config('app.url'), '/').'/').'</loc></url>'.
            '</urlset>'
        );
    }

    public function section(string $section): Response
    {
        abort_unless(preg_match('/^(categories|providers|blog|pages|gb-cities|gb-services|us-cities|us-services)-[1-9][0-9]*$/', $section), 404);
        [$type, $page] = $this->parseSection($section);

        $xml = Cache::remember('sitemap.section.'.$section, 3600, function () use ($type, $page): string {
            $rows = $this->queryFor($type)->offset(($page - 1) * self::MAX_URLS)->limit(self::MAX_URLS)->get();
            $baseUrl = rtrim(config('app.url'), '/');
            $xml = $this->xmlHeader().'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            foreach ($rows as $row) {
                $path = match ($type) {
                    'categories' => '/'.$row->slug,
                    'providers' => '/pros/'.$row->slug,
                    'blog' => '/blog/'.$row->slug,
                    'pages' => '/'.$row->slug,
                    'gb-cities', 'us-cities' => '/'.$row->slug,
                    'gb-services', 'us-services' => '/'.$row->category_slug.'/'.$row->city_slug,
                };
                $xml .= '<url><loc>'.$this->escape($baseUrl.$path).'</loc>';

                if ($row->updated_at) {
                    $xml .= '<lastmod>'.$this->escape($row->updated_at).'</lastmod>';
                }

                $xml .= '</url>';
            }

            return $xml.'</urlset>';
        });

        return $this->xmlResponse($xml);
    }

    public function robots(): Response
    {
        return response(
            "User-agent: *\nDisallow: /admin\nDisallow: /provider\nDisallow: /find\nSitemap: ".rtrim(config('app.url'), '/')."/sitemap.xml\n",
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'public, max-age=3600'],
        );
    }

    private function queryFor(string $section): Builder
    {
        return match ($section) {
            'categories' => DB::table('categories')
                ->where('is_active', true)
                ->whereExists(function (Builder $query): void {
                    $query->selectRaw('1')->from('provider_category')
                        ->join('provider_profiles', 'provider_profiles.id', '=', 'provider_category.provider_profile_id')
                        ->whereColumn('provider_category.category_id', 'categories.id')
                        ->where('provider_profiles.status', 'approved');
                })
                ->select('categories.slug', 'categories.updated_at')
                ->orderBy('categories.id'),
            'providers' => DB::table('provider_profiles')
                ->where('status', 'approved')
                ->whereNotNull('description')->where('description', '!=', '')
                ->whereExists(function (Builder $query): void {
                    $query->selectRaw('1')->from('provider_category')
                        ->join('categories', 'categories.id', '=', 'provider_category.category_id')
                        ->whereColumn('provider_category.provider_profile_id', 'provider_profiles.id')
                        ->where('categories.is_active', true);
                })
                ->whereExists(function (Builder $query): void {
                    $query->selectRaw('1')->from('provider_service_areas')
                        ->join('cities', 'cities.id', '=', 'provider_service_areas.city_id')
                        ->whereColumn('provider_service_areas.provider_profile_id', 'provider_profiles.id')
                        ->where('cities.is_active', true);
                })
                ->select('slug', 'updated_at')
                ->orderBy('id'),
            'blog' => DB::table('blog_posts')
                ->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now())->where('body', '!=', '')
                ->select('slug', 'updated_at')->orderBy('id'),
            'pages' => DB::table('content_pages')
                ->where('status', 'published')->where('body', '!=', '')
                ->select('slug', 'updated_at')->orderBy('id'),
            'gb-cities', 'us-cities' => DB::table('cities')
                ->join('countries', 'countries.id', '=', 'cities.country_id')
                ->where('countries.code', str_starts_with($section, 'gb-') ? 'GB' : 'US')
                ->where('cities.is_active', true)
                ->whereExists(function (Builder $query): void {
                    $query->selectRaw('1')->from('provider_service_areas')
                        ->join('provider_profiles', 'provider_profiles.id', '=', 'provider_service_areas.provider_profile_id')
                        ->whereColumn('provider_service_areas.city_id', 'cities.id')
                        ->where('provider_profiles.status', 'approved');
                })
                ->select('cities.slug', 'cities.updated_at')
                ->orderBy('cities.id'),
            'gb-services', 'us-services' => DB::table('provider_category')
                ->join('provider_profiles', 'provider_profiles.id', '=', 'provider_category.provider_profile_id')
                ->join('categories', 'categories.id', '=', 'provider_category.category_id')
                ->join('provider_service_areas', 'provider_service_areas.provider_profile_id', '=', 'provider_profiles.id')
                ->join('cities', 'cities.id', '=', 'provider_service_areas.city_id')
                ->join('countries', 'countries.id', '=', 'cities.country_id')
                ->where('provider_profiles.status', 'approved')
                ->where('categories.is_active', true)
                ->where('cities.is_active', true)
                ->where('countries.code', str_starts_with($section, 'gb-') ? 'GB' : 'US')
                ->select('categories.slug as category_slug', 'cities.slug as city_slug')
                ->selectRaw('MAX(cities.updated_at) as updated_at')
                ->groupBy('categories.id', 'categories.slug', 'cities.id', 'cities.slug')
                ->orderBy('categories.id')->orderBy('cities.id'),
            default => abort(404),
        };
    }

    private function isGrouped(string $section): bool
    {
        return in_array($section, ['gb-services', 'us-services'], true);
    }

    private function parseSection(string $section): array
    {
        if (preg_match('/^(gb|us)-(cities|services)-([1-9][0-9]*)$/', $section, $matches)) {
            return [$matches[1].'-'.$matches[2], (int) $matches[3]];
        }

        preg_match('/^(categories|providers|blog|pages)-([1-9][0-9]*)$/', $section, $matches);

        return [$matches[1], (int) $matches[2]];
    }

    private function xmlHeader(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function xmlResponse(string $xml): Response
    {
        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
