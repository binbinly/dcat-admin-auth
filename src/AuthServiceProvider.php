<?php

namespace AdminExtAuth;

use AdminExtAuth\Http\Middleware\AuthCheck;
use Dcat\Admin\Extend\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $js = [
//        'js/index.js',
    ];
    protected $css = [
//        'css/index.css',
    ];

    public function register()
    {
        //
    }

    public function init()
    {
        parent::init();

        //

    }

    protected $middleware = [
        'middle' => [
            AuthCheck::class,
        ],
    ];

    public function settingForm()
    {
        return new Setting($this);
    }
}
