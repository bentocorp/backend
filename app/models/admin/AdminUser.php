<?php

namespace Bento\Admin\Model;

#use Illuminate\Auth\UserTrait;


class AdminUser extends \Eloquent {
     
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin_User';
    protected $primaryKey = 'pk_admin_User';
            
}
