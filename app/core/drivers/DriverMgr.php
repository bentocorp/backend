<?php

namespace Bento\Drivers;


use Illuminate\Support\Facades\Facade;


class DriverMgr extends Facade {

    protected static function getFacadeAccessor() { return 'DriverMgr'; }

}
