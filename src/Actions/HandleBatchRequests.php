<?php

namespace Actionjs\Actionjs\Actions;

use Actionjs\Actionjs\Support\Actions;
use Illuminate\Http\Request;

class HandleBatchRequests
{
    public function __invoke(Request $request)
    {
        // Example request:
        // /api/actionjs/batch?queue=[{%22key%22:%2240661014d060c64829b0b721da6a8818%22,%22url%22:%22%E2%80%A6thod%22:%22get%22,%22params%22:{%22namespace%22:%22actionjs%22}}}]
        $request->validate([
            'queue' => 'required|json',
        ]);

        $batch = (
            collect(json_decode($request->queue, true))
                ->map(fn ($query) => $this->handleQueueItem($request, $query))
                ->all()
        );

        return compact('batch');
    }

    protected function handleQueueItem(Request $request, array $query)
    {
        $newRequest = $request->replace($query['options']['params']);
        $params = Actions::urlParams($query['url'], 'POST');

        return [
            'key' => $query['key'],
            'data' => HandleAction::run($newRequest, $params['action']),
        ];
    }
}
