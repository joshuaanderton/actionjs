<?php

namespace Actionjs\Actionjs\Actions\Config;

use Actionjs\Actionjs\Action;
use Actionjs\Actionjs\Support\Actions;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use ReflectionMethod;
use Tightenco\Ziggy\Ziggy;

class Routes extends Config
{
    public function generate(): array
    {
        $config = (new Ziggy)->toArray();
        $config = $this->setRouteActions($config);
        $config = $this->setRouteParams($config);

        return $config;
    }

    private function setRouteActions(array $config): array
    {
        $routes = $config['routes'];
        $actions = $this->getRouteActions();

        collect($routes)
            ->filter(fn ($route) => isset($actions[$route['uri']]))
            ->map(function ($route, $name) use ($actions) {

                $action = $actions[$route['uri']] ?? null;

                $action = $action ? Actions::actionKey($action) : $action;

                $routes[$name]['action'] = $action;

                return [$name => $route];

            })
            ->collapse()
            ->all();

        $config['routes'] = $routes;

        return $config;
    }

    private function getRouteActions(): Collection
    {
        $routeActions = (array) Route::getRoutes();

        $routeActions = collect(
            collect($routeActions)
                ->filter(fn ($list, $key) => Str::contains($key, 'actionList'))
                ->first() ?: []
        );

        return
            $routeActions
                ->filter(fn ($route, $routeAction) => is_subclass_of(explode('@', $routeAction)[0], Action::class))
                ->map(fn ($route, $routeAction) => [$route->uri => $routeAction])
                ->collapse();
    }

    private function setRouteParams(array $config): array
    {
        $routes = collect($config['routes']);
        $actions = $this->getRouteActions();

        $routes
            ->filter(fn ($route) => isset($actions[$route['uri']]))
            ->each(function ($route, $name) use ($actions) {
                $action = $actions[$route['uri']];

                if (Str::contains($action, '@')) {

                    [$class, $method] = explode('@', $action);

                } else {

                    $class = $action;
                    $method = '__invoke';

                }

                $routes[$name]['parameters'] = $this->getRouteParams($class, $method);
            });

        $config['routes'] = $routes;

        return $config;
    }

    private function getRouteParams(string $class, string $method)
    {
        if (! method_exists($class, $method)) {
            return [];
        }

        $reflect = new ReflectionMethod($class, $method);

        return
            collect($reflect->getParameters())
                ->map(fn ($p) => [$p->getName() => ($t = $p->getType()) ? $t->getName() : 'mixed'])
                ->collapse()
                ->filter(fn ($type, $name) => $type !== Request::class)
                ->collapse();
    }
}
