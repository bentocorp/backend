<?php

namespace Bento\Bento;


use Illuminate\Support\Facades\Facade;


class Bento extends Facade {

    protected static function getFacadeAccessor() { return 'Bento'; }

}
