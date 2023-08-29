<?php

namespace Actionjs\Actionjs\Actions\Config;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang as BaseLang;
use Illuminate\Support\Str;

class Translations extends Config
{
    public function generate(): array
    {
        return [
            'locale' => app()->getLocale(),
            'translations' => static::translations(),
        ];
    }

    protected static function packageTranslations(string $path, string $prefix = null): array
    {
        $files = File::allFiles($path);
        $files = new Collection($files);

        return $files
            ->map(function ($file) use ($prefix) {
                $fileName = Str::remove(".{$file->getExtension()}", $file->getFileName());
                $key = $prefix ? "{$prefix}::{$fileName}" : $fileName;

                return [$key => BaseLang::get($key)];
            })
            ->collapse()
            ->all();
    }

    public static function translations(): array
    {
        $translations = static::packageTranslations(
            path: lang_path(),
            prefix: null
        );

        $packageTranslations = collect([
            'actionjs' => 'actionjs/actionjs',
        ])
            ->map(function ($package, $packagePrefix) {
                if (! File::exists($packageLangPath = base_path("vendor/{$package}/lang"))) {
                    return null;
                }

                return static::packageTranslations(
                    path: $packageLangPath,
                    prefix: $packagePrefix
                );
            })
            ->collapse()
            ->whereNotNull();

        return $packageTranslations
            ->merge($translations)
            ->all();
    }
}
