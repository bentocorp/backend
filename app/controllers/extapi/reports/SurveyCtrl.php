<?php

namespace Bento\ExtApi\Ctrl\Reports;

use Bento\Model\CustomerBentoBox;
use Response;



class SurveyCtrl extends \BaseController {

    public function getRange($start, $end) {
                        
        // Get range of CustomerBentoBoxes
        $boxes = CustomerBentoBox::getBoxesForSurvey($start, $end);
           
        return Response::json($boxes);
    }    
}
