<?php

namespace Lia\Media;

use Lia\Admin;
use Lia\Auth\Database\Menu;
use Lia\Extension;
use Lia\Media\Form\LFM;
use Lia\Form;

class ExtensionLia extends Extension
{
    /**
     * Bootstrap this package.
     *
     * @return void
     */

    public static function boot()
    {
        static::registerRoutes();

        Admin::extend('media', __CLASS__);
        Form::extend('lfm', LFM::class);
    }

    /**
     * Register routes for laravel-admin.
     *
     * @return void
     */
    public static function registerRoutes()
    {
        parent::routes(function ($router) {
            /* @var \Illuminate\Routing\Router $router */

            $router->group([
                'namespace'     => 'Lia\Media\Controllers'
            ], function($router){
                $router->resource('lia_media', \MediaController::class);
            });
        });
    }

    public static function import()
    {
        $lastOrder = Menu::max('order');
        Menu::create([
            'parent_id' => 0,
            'order'     => $lastOrder++,
            'title'     => 'Media',
            'icon'      => 'fa-folder',
            'uri'       => 'lia_media',
        ]);

        parent::createPermission('MediaController', 'ext.lia_media', 'lia_media/*');
    }

}