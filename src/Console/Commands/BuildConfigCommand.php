<?php

namespace Actionjs\Actionjs\Console\Commands;

use Actionjs\Actionjs\Actions\Config\Actions;
use Actionjs\Actionjs\Actions\Config\Controllers;
use Actionjs\Actionjs\Actions\Config\Jobs;
use Actionjs\Actionjs\Actions\Config\Models;
use Actionjs\Actionjs\Actions\Config\Notifications;
use Actionjs\Actionjs\Actions\Config\Routes;
use Actionjs\Actionjs\Actions\Config\Translations;
use Actionjs\Actionjs\Support\Actions as ActionHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BuildConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'actionjs:build-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate json config/schema files for ActionJS';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function __invoke()
    {
        $directory = ActionHelper::path('dist/config');

        File::ensureDirectoryExists($directory);

        collect([
            Actions::class,
            Controllers::class,
            Routes::class,
            // Translations::class,
            // Models::class,
            // Notifications::class,
            // Jobs::class,
        ])->each(fn ($helper) => (
            file_put_contents(
                "{$directory}/{$helper::outputFileName()}",
                $helper::outputFileContents()
            )
        ));

        $this->comment('Actionjs json config files built for translations, routes, models, notifications, actions, and jobs');

        return 0;
    }
}
