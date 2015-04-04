<?php

namespace Bento\ExtApi\Ctrl;

use Bento\Admin\Model\Dish;
use Response;



class DishCtrl extends \BaseController {

    public function getIndex($id) {
                        
        // Get dish
        $dish = Dish::find($id);
           
        return Response::json($dish);
    }    
}
