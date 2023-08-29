<?php

namespace Actionjs\Actionjs\Actions\Config;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class Controllers extends Config
{
    public function generate(): array
    {
        $path = app_path('Http/Controllers');

        if (! File::exists($path)) {
            return [];
        }

        return [
            'controllers' => (
                collect(File::allFiles($path))
                    ->map(fn ($file) => (
                        Str::replace('/', '\\', Str::replace(app_path(), 'App', Str::remove('.php', $file->getPathname())))
                    ))
                    ->filter(fn ($class) => $class !== 'App\Http\Controllers\Controller')
                    ->filter(fn ($class) => is_subclass_of($class, Controller::class))
                    ->filter(fn ($class) => (new ReflectionClass($class))->isAbstract() === false)
                    ->map(fn ($class) => [$class => [
                        'key' => Str::remove('App.', Str::replace('\\', '.', $class)),
                        'methods' => $this->getControllerActions($class),
                    ]])
                    ->collapse()
                    ->all()
            ),
        ];
    }

    private function getControllerActions(string $class): array
    {
        $actions = (new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC);

        return
            collect($actions)
                ->where('class', $class)
                ->filter(fn ($m) => (
                    ($name = $m->getName()) === '__invoke' ||
                    ! Str::startsWith($name, '_')
                ))
                ->map(fn ($m) => [
                    $m->getName() => [
                        'returnType' => $this->getMethodReturnType($m),
                        'parameters' => (
                            collect($m->getParameters())
                                ->map(fn ($p) => [
                                    $p->getName() => [
                                        'type' => $p->getType(),
                                        'required' => ! $p->isOptional(),
                                    ],
                                ])
                                ->collapse()
                                ->all()
                        ),
                    ],
                ])
                ->collapse()
                ->all();
    }
}
