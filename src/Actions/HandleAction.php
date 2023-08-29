<?php

namespace Actionjs\Actionjs\Actions;

use Actionjs\Actionjs\Support\Actions;
use Illuminate\Http\Request;
use ReflectionMethod;

class HandleAction
{
    public static function run(Request $request, string $action)
    {
        return (new self)->__invoke($request, $action);
    }

    public function __invoke(Request $request, string $action)
    {
        if ($namespace = $request->namespace) {
            if ($namespace === 'actionjs') {
                $namespace = 'actionjs-actionjs';
            }
            $action = "{$namespace}-actions-{$action}";
        }

        $actionClass = Actions::keyAction($action);

        $params = array_merge(
            //Actions::urlParams($url, $method), // Get params from current url
            $request->except(['namespace', 'action']),
            compact('request')
        );

        $reflect = new ReflectionMethod($actionClass, 'handle');

        $paramTypes = (
            collect($reflect->getParameters())
                ->filter(fn ($p) => ! $p->isOptional())
                ->map(fn ($p) => [$p->getName() => $p->getType()->getName()])
                ->collapse()
        );

        $params = collect($params)
            ->filter(fn ($val, $key) => $paramTypes->has($key))
            ->all();

        return (new $actionClass)->__invoke(...$params);
    }
}
