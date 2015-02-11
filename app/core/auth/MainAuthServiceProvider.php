<?php

namespace Bento\Auth;


use Illuminate\Support\ServiceProvider;


class MainAuthServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton('MainAuth', function()
        {
            return new MainAuth;
        });
    }

}

