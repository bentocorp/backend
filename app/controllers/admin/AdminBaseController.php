<?php

namespace Bento\Admin\Ctrl;

class AdminBaseController extends \BaseController {

    
	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{ 
            if ( ! is_null($this->layout))
            {
                    $this->layout = View::make($this->layout);
            }
	}

}
