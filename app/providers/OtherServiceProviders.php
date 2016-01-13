<?php

namespace Bento\Providers;


use Illuminate\Support\ServiceProvider;


class OtherServiceProviders extends ServiceProvider {

    public function register()
    {
        $this->app->singleton('DriverMgr', function()
        {
            return new \Bento\Drivers\DriverMgrSvc;
        });
              
        
        $this->app->singleton('Bento', function()
        {
            return new \Bento\app\BentoSvc;
        });
        
    }

}

