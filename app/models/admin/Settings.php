<?php

namespace Bento\Admin\Model;



class Settings extends \Eloquent {
     
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'settings';
    protected $primaryKey = 'key';
            
}
