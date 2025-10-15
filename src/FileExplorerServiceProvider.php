<?php

namespace Webazin\FileExplorer;

use Illuminate\Support\ServiceProvider;

class FileExplorerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/file-explorer.php',
            'file-explorer'
        );
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'file-explorer');
        
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'file-explorer');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/file-explorer.php' => config_path('file-explorer.php'),
            ], 'file-explorer-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/file-explorer'),
            ], 'file-explorer-views');

            $this->publishes([
                __DIR__.'/../resources/lang' => $this->app->langPath('vendor/file-explorer'),
            ], 'file-explorer-translations');
        }
    }
}