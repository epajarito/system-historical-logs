<?php

namespace Epajarito\SystemHistoricalLogs;

use Illuminate\Support\ServiceProvider;

class SystemHistoricalLogsServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->loadMigrationsFrom(
            $this->basePath('database/migrations')
        );
    }

    private function basePath($path)
    {
        return __DIR__ . '/../' . $path;
    }
}
