<?php

namespace Actionjs\Actionjs\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;
use Tests\CreatesApplication;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected Controller $controller;

    protected function mount(...$params): self
    {
        $controllerClass = get_called_class();
        $controllerClass = Str::replace('Tests\\Feature\\', 'App\\Http\\Actionjs\\', $controllerClass);

        $this->controller = new $controllerClass;

        return $this;
    }
}
