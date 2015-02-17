<?php

namespace Bento\Tracking;


use Illuminate\Support\ServiceProvider;


class TrackingServiceProviders extends ServiceProvider {

    public function register()
    {
        $this->app->singleton('Trak', function()
        {
            return new TrakSvc;
        });
    }

}

