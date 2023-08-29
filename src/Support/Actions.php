<?php

namespace Actionjs\Actionjs\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Actions
{
    public static function dir(): string
    {
        return 'app/Actions/Actionjs';
    }

    public static function path(string ...$path): string
    {
        return implode('/', [
            Str::remove('src/Support', __DIR__),
            ...$path,
        ]);
    }

    public static function namespace(): string
    {
        return (new Collection(explode('/', static::dir())))
            ->map(fn ($slug) => Str::ucfirst(Str::camel($slug)))
            ->join('\\');
    }

    public static function urlRoute(string $url, string $method = 'GET')
    {
        return
            app('router')
                ->getRoutes()
                ->match(
                    app('request')->create($url, $method)
                );
    }

    public static function urlParams(string $url, string $method = 'GET'): array
    {
        $route = static::urlRoute($url, $method);

        return $route->parameters;
    }

    public static function urlAction(string $url, string $method = 'GET'): string
    {
        $route = static::urlRoute($url, $method);
        $actionClass = $route->action['controller'];

        return static::actionKey($actionClass);
    }

    public static function actionComponent(string $action): string
    {
        $component = $action;
        $component = Str::replace('-', '/', $component);
        $component = "actionjs/{$component}";

        return $component;
    }

    public static function keyAction(string $actionKey): string
    {
        // Support actionjs package actions
        if (Str::startsWith($actionKey, 'actionjs-')) {
            $actionsNamespace = '';
        } else {
            $actionsNamespace = static::dir();
            $actionsNamespace = explode('/', $actionsNamespace);
            $actionsNamespace = collect($actionsNamespace)->map(fn ($an) => Str::ucfirst(Str::camel($an)))->join('\\');
            $actionsNamespace = "\\{$actionsNamespace}";
        }

        $actionClass = explode('-', $actionKey);
        $actionClass = collect($actionClass)->map(fn ($ac) => Str::ucfirst(Str::camel($ac)))->join('\\');
        $actionClass = "{$actionsNamespace}\\{$actionClass}";

        return $actionClass;
    }

    public static function actionKey(string $action): string
    {
        $key = Str::remove(Actions::namespace().'\\', $action);
        $key = explode('\\', $key);
        $key = collect($key)->map(fn ($key) => Str::camel($key));

        return $key->join('-');
    }

    public static function classes(): Collection
    {
        $actionsDir = static::dir();
        $actionsNamespace = static::namespace();
        $classNames = [];
        $files = (new Filesystem)->allFiles(base_path($actionsDir));

        foreach ($files as $file) {
            $path = $file->getPathName();
            $className = explode("{$actionsDir}/", $path)[1];
            $className = Str::remove('.php', $className);
            $className = Str::replace('/', '\\', $className);
            $className = "{$actionsNamespace}\\{$className}";

            $classNames[$className] = $path;
        }

        return collect($classNames);
    }
}
