<?php
namespace Saeid\UploadManager;

use Illuminate\Support\ServiceProvider;

class UploadManagerServiceProvider extends ServiceProvider {
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/UploadManager.php' => config_path('UploadManager.php'),
        ], 'config');
    }
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/UploadManager.php', 'UploadManager');

    }
}
