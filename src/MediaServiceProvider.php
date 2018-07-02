<?php

namespace Lia\Media;

use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom( __DIR__ . '/../config/lia-media.php', 'lia-media' );
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lia-media');
        $this->loadTranslationsFrom( __DIR__ . '/../resources/lang', 'lia-media' );

        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config' => config_path()], 'lia-media');
            $this->publishes([__DIR__.'/../resources/lang' => resource_path('lang')], 'lia-media');
            $this->publishes([__DIR__.'/../resources/assets' => public_path('vendor/lia-media')], 'lia-media');
            $this->publishes([__DIR__.'/../resources/laravel-filemanager' => public_path('vendor/laravel-filemanager')], 'lfa');
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        ExtensionLia::boot();
    }
}

//php artisan lia:import media
//php artisan vendor:publish --provider="Lia\Media\MediaServiceProvider"
//Edit lia-media.php from configure markers
//php artisan migrate
