<?php

namespace App\Services;

use App\Models\City;
use App\Models\Category;
use App\Models\ContentPage;
use App\Models\Country;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class CityCsvImporter
{
    private const REQUIRED_HEADERS = ['country_code', 'name', 'region', 'postal_prefix', 'population'];

    public function import(string $path): int
    {
        $stream = Storage::disk('local')->readStream($path);

        if (! is_resource($stream)) {
            throw new RuntimeException('The uploaded CSV file could not be opened.');
        }

        try {
            return DB::transaction(function () use ($stream) {
                $headers = fgetcsv($stream, 0, ',', '"', '\\');

                if (! is_array($headers)) {
                    throw new RuntimeException('The CSV file is empty.');
                }

                $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
                $headers = array_map(fn ($header) => strtolower(trim((string) $header)), $headers);

                if (array_diff(self::REQUIRED_HEADERS, $headers) || count($headers) !== count(array_unique($headers)) || in_array('', $headers, true)) {
                    throw new RuntimeException('The CSV header row must contain each required column exactly once: '.implode(', ', self::REQUIRED_HEADERS).'.');
                }

                $count = 0;
                $rowNumber = 1;

                while (($row = fgetcsv($stream, 0, ',', '"', '\\')) !== false) {
                    $rowNumber++;

                    if ($row === [null] || $row === []) {
                        continue;
                    }

                    if (count($row) !== count($headers)) {
                        throw new RuntimeException("CSV row {$rowNumber} has a different number of values than the header.");
                    }

                    $data = array_combine($headers, array_map(fn ($value) => trim((string) $value), $row));
                    $countryCode = strtoupper($data['country_code']);
                    $name = $data['name'];
                    $region = $data['region'] ?: null;
                    $postalPrefix = $data['postal_prefix'] ?: null;
                    $population = $data['population'] === '' ? null : filter_var($data['population'], FILTER_VALIDATE_INT);

                    if (! preg_match('/^[A-Z]{2}$/', $countryCode) || $name === '' || mb_strlen($name) > 120 || Str::slug($name) === '') {
                        throw new RuntimeException("CSV row {$rowNumber} has an invalid country code or city name.");
                    }

                    if (($region !== null && mb_strlen($region) > 100)
                        || ($postalPrefix !== null && mb_strlen($postalPrefix) > 16)
                        || ($data['population'] !== '' && ($population === false || $population < 0 || $population > 4_294_967_295))) {
                        throw new RuntimeException("CSV row {$rowNumber} has an invalid region, postal prefix, or population.");
                    }

                    $country = Country::query()->where('code', $countryCode)->where('is_active', true)->first();

                    if (! $country) {
                        throw new RuntimeException("CSV row {$rowNumber} references an unknown or inactive country.");
                    }

                    $slug = Str::slug($name);

                    if (City::query()->where('slug', $slug)->where('country_id', '!=', $country->id)->exists()
                        || Category::query()->where('slug', $slug)->exists()
                        || ContentPage::query()->where('slug', $slug)->exists()) {
                        $slug .= '-'.strtolower($countryCode);
                    }

                    City::updateOrCreate([
                        'country_id' => $country->id,
                        'slug' => $slug,
                    ], [
                        'name' => $name,
                        'region' => $region,
                        'postal_prefix' => $postalPrefix,
                        'population' => $population,
                        'is_active' => true,
                    ]);
                    $count++;
                }

                if ($count === 0) {
                    throw new RuntimeException('The CSV file did not contain any city rows.');
                }

                return $count;
            });
        } finally {
            fclose($stream);
        }
    }
}
