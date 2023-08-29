<?php

namespace Actionjs\Actionjs;

use Actionjs\Actionjs\Console\Commands\BuildConfigCommand;
use Actionjs\Actionjs\Support\ApiRoutes;
use Actionjs\Actionjs\Support\Actions;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Str;

class ActionjsServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this
            ->loadAssets()
            ->loadViews()
            ->loadCommands()
            ->loadRoutes();
    }

    private function loadAssets(): self
    {
        $assets = [
            $this->path('dist/actionjs.umd.js') => public_path('vendor/actionjs'),
        ];

        $this->publishes($assets, 'public');

        return $this;
    }

    private function loadCommands(): self
    {
        if (! $this->app->runningInConsole()) {
            return $this;
        }

        $this->commands([
            BuildConfigCommand::class,
        ]);

        return $this;
    }

    private function loadViews(): self
    {
        $this->loadViewsFrom(
            static::path('resources/views'),
            'actionjs'
        );

        return $this;
    }

    private function loadRoutes(): self
    {
        ApiRoutes::register();

        return $this;
    }

    public static function path(string ...$path): string
    {
        return Actions::path(...$path);
    }
}
