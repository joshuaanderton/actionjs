<?php

namespace Actionjs\Actionjs\Support;

use Actionjs\Actionjs\Actions;
use Illuminate\Support\Facades\Route;

class ApiRoutes
{
    private string $endpointPrefix = '/api/actionjs';

    public static function register(): void
    {
        $endpoint = (new self)->endpointPrefix;

        Route::middleware('api')->group(fn () => (
            Route::prefix($endpoint)->group(function () {
                Route::post('actions/{action}', Actions\HandleAction::class);
                Route::post('batch-requests', Actions\HandleBatchRequests::class);
                Route::post('batch-actions', Actions\HandleBatchActions::class);
            })
        ));
    }
}
