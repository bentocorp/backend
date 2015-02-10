<?php

namespace Bento\Auth;


use Illuminate\Support\ServiceProvider;


class FacebookAuthServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton('FacebookAuth', function()
        {
            return new FacebookAuth;
        });
    }

}

