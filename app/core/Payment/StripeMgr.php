<?php

namespace Bento\Payment;


use Illuminate\Support\Facades\Facade;


class StripeMgr extends Facade {

    protected static function getFacadeAccessor() { return 'StripeMgr'; }

}
