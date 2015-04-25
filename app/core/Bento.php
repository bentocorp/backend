<?php

namespace Bento\app;


use Illuminate\Support\Facades\Facade;


class Bento extends Facade {

    protected static function getFacadeAccessor() { return 'Bento'; }

}
