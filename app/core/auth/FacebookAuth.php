<?php

namespace Bento\Auth;


use Illuminate\Support\Facades\Facade;


class FacebookAuth extends Facade {

    protected static function getFacadeAccessor() { return 'FacebookAuth'; }

}