<?php

namespace Bento\Auth;


use Illuminate\Support\Facades\Facade;


class MainAuth extends Facade {

    protected static function getFacadeAccessor() { return 'MainAuth'; }

}
