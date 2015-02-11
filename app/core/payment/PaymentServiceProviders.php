<?php

namespace Bento\Payment;


use Illuminate\Support\ServiceProvider;


class PaymentServiceProviders extends ServiceProvider {

    public function register()
    {
        $this->app->singleton('StripeMgr', function()
        {
            return new StripeMgr;
        });
    }

}

