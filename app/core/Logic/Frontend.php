<?php namespace Bento\core\Logic;


use Bento\core\Status;
use Bento\Model\Menu as OdMenu;
use Bento\core\Logic\MaitreD;
use Bento\Model\MealType;
use Bento\Model\AppCopy;
use Bento\Timestamp\Clock;
use Carbon\Carbon;
use Request;
use Route;


/**
 * Front-end app logic
 */
class Frontend {

    // Was thinking of appneing this when OD isn't available, but haven't.
    private static $odEncourageOa = 'Schedule a delivery below.';
    
    /*
     * See also: https://github.com/bentocorp/backend/wiki/Logic:-appState-Decision-Tree
     * 
        AppState Decision Tree:

        + Out of zone?
            + Yes: map,no_service_wall
            + No: HasServices?
                + Yes: HasOAService?
                    + Yes: Build
                    + No: Use OD state
                + No: OD open and has a menu set?
                    + Yes: Give existing map,no_service_wall
                    + No: closed_wall

     * 
     * return strings:
        # map,no_service_wall: Nothing is available. Execute the existing map <-> bummer wall logic.
        # closed_wall: No OA available, and we are closed
        # soldout_wall: No OA available, and we are sold out
        # build: Open to order something! Show the order flow
     */
    public static function getState($hasOrderAhead, $isInZone, $hasService)
    {
        $status = Status::get();
        $odMenuCountToday = OdMenu::getCountToday();
        
        // OD open?
        
        // Yes: Give normal map,no_service_wall
        #if ($status == 'open' && $odMenuCountToday > 0)
            #return 'map,no_service_wall';
        // No: 
        #else 
        #{
            // Out of any zone? 

            // Yes: We're done.
            if (!$isInZone)
                return 'map,no_service_wall';
            // No: HasServices?
            else
            {
                // Yes:
                if ($hasService)
                {
                    // HasOAService?

                    // Yes: If there's OA available, let them order!
                    if ($hasOrderAhead)
                        return 'build';
                    // No: Use OD state
                    else 
                    {
                        if ($status == 'open' && $odMenuCountToday > 0)
                            return 'build';
                        else if ($status == 'closed')
                            return 'closed_wall';
                        else if ($status == 'sold out')
                            return 'soldout_wall';
                        else
                            return 'closed_wall1'; # catch-all case
                    }
                }
                // No:
                // Otherwise there isn't, and just show walls.
                // (This is primarily an edge case, for perhaps around the holidays when
                // we might not have OA menus within n days for you to order ahead from.
                else 
                {
                    // Make an exception if OD is open
                    if ($status != 'closed' && $odMenuCountToday > 0)
                        return 'map,no_service_wall';
                    else
                        return 'closed_wall2';
                }
            #}
        }
    }
    
    
    /*
     * See also: https://github.com/bentocorp/backend/wiki/Logic:-App-On-Demand-Widget
     */
    public static function getOnDemandWidget($myZones)
    {
        // Vars
        $md = MaitreD::get();
        $mealTypes = MealType::getList();
        $nowAvail = 'Now available for on-demand service.';
        $today = Clock::getLocalTimestamp();

        // Setup widget
        $widget = new \stdClass();
        
        $widget->selected = NULL;
        $widget->title = '';
        $widget->text = '';
        $widget->state = Status::get();
        
        // Get Current Meal State
        $cmt = $md->determineCurrentMealType();
        $cmtName = $mealTypes->hash->$cmt->name;
        $cmtNameCap = ucfirst($cmtName);

        # Are we in OD?
        # If we aren't in OD zone, we're done
        if ( !isset($myZones['OnDemand']) )
            return NULL;
        
        # Are we open?
        
        // Yes:
        if (Status::isOpen()) 
        {
            $widget->selected = true;
            $widget->title = "Today's $cmtNameCap";
            $widget->text = $nowAvail;
            $widget->menuPreview = NULL;
            $widget->mealMode = $cmtName;
        }
        // No: 
        else
        {
            $widget->selected = false;
            
            // IF soldout: "{Sold out text}"
            if (Status::isSoldout())
            {
                $widget->title = "Today's $cmtNameCap (sold out)";
                $widget->text = AppCopy::getValue('sold-out-text');
                
                // Include the menu to preview back to the frontend
                // /menu/{date}
                $widget->menuPreview = NULL;
                try {
                    $request = Request::create("/menu/$today", 'GET');
                    $instance = json_decode(Route::dispatch($request)->getContent());
                    $widget->menuPreview = $instance->menus->$cmtName;
                }
                catch (\Exception $e) {}
            }
            // Otherwise, we're closed
            else
            {
                // Is there an ULM for today? (account for buffer time)
                $ulm = OdMenu::getUpcomingLateMenu();
                #var_dump($ulm); die();
                
                // Yes: title="Today's {LM MealType}", text="Opening at timenow()+5mins for on-demand service"
                if (count($ulm) > 0)
                {
                    $upcomingToday = $ulm[0];
                    
                    // Include the menu to preview back to the frontend
                    // /menu/{date}
                    $request = Request::create("/menu/$today", 'GET');
                    $instance = json_decode(Route::dispatch($request)->getContent());
                    $mealName = $upcomingToday->mealName;
                    #var_dump($instance); die(); #0
                    
                    // Show local opening time
                    $openingAt = Carbon::parse($upcomingToday->displayStartTime, Clock::getTimezone())->format('g:ia');
                    
                    // Are we late?
                    if ( strtotime(Clock::getLocalCarbon()->toTimeString()) > strtotime($upcomingToday->displayStartTime) ) {
                        // Get the next 5 minute mark
                        $now = time();
                        #var_dump($now); die(); #0
                        $nextFive = ceil($now/300)*300;
                        #$carbon = new Carbon($nextFive, 'UTC');
                        #die($now); #0
                        $openingAt = Carbon::createFromFormat('U', $nextFive, 'UTC')->setTimezone(Clock::getTimezone())->format('g:ia');
                    }
                     
                    $widget->title = "Today's $cmtNameCap (opening soon)";
                    $widget->text ="Opening at $openingAt for on-demand service.";
                    $widget->menuPreview = $instance->menus->$mealName;
                }
                // No: Is there a /menu/next menu?
                else
                {
                    // /menu/next/{date}
                    $request = Request::create("/menu/next/$today", 'GET');
                    $instance = json_decode(Route::dispatch($request)->getContent());
                    
                    // Yes: title="{Tomorrow/Day's} {Meal}", text="Opening tomorrow/{day} at {startTime}"
                    if ($instance != NULL)
                    {
                        $nextMenu = $md->findNextMenuFromApi($instance);
                        $nextDate = $nextMenu->Menu->for_date;
                        $openingAt = Carbon::parse($nextMenu->Menu->displayStartTime, Clock::getTimezone())->format('g:ia');
                        $meal = ucfirst($nextMenu->Menu->meal_name);
                        
                        // Tomorrow/{day} text logic
                        if (Clock::isTomorrow($nextDate)) {
                            $dayTitle = 'Tomorrow\'s';
                            $dayText = 'tomorrow';
                        }
                        else {
                            // e.g.: Monday's
                            $dayTitle = Carbon::parse($nextDate, Clock::getTimezone())->format('l') . "'s";
                            // e.g.: Mon Feb 5th
                            $dayText = Carbon::parse($nextDate, Clock::getTimezone())->format('D M jS');
                        }
                        
                        $widget->title = "$dayTitle $meal";
                        $widget->text = "Opening $dayText at $openingAt.";
                        $widget->menuPreview = $nextMenu;
                    }
                    // No: OD is not available at all, and the OD widget is NULL.
                    else
                    {
                        $widget = NULL;
                    }
                }
                
            }
        }
        
        
        // Return
        if ($widget !== NULL && $widget->selected !== NULL)
            return $widget;
        else
            return NULL;
    }
    
        
}
