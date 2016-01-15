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

    /*
     * See also: https://github.com/bentocorp/backend/wiki/Logic:-appState-Decision-Tree
     * 
        AppState Decision Tree:

        Out of zone?
        Yes: map,no_service_wall
        No: -> HasServices

        HasServices?
        Yes: -> HasOAService?
        No: closed_wall

        HasOAService?
        Yes: Build
        No: Use OD state
     * 
     * return strings:
        # map,no_service_wall: Nothing is available. Execute the existing map <-> bummer wall logic.
        # closed_wall: No OA available, and we are closed
        # soldout_wall: No OA available, and we are sold out
        # build: Open to order something! Show the order flow
     */
    public static function getState($hasOrderAhead, $isInZone, $hasService)
    {
        // Out of any zone? We're done.
        if (!$isInZone)
            return 'map,no_service_wall';

        // HasServices?
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
                $status = Status::get();
                $odMenuCountToday = OdMenu::getCountToday();

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
            return 'closed_wall2';
    }
    
    
    /*
     * See also: https://github.com/bentocorp/backend/wiki/Logic:-App-On-Demand-Widget
     */
    public static function getOnDemandWidget()
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

        # Are we open?
        
        // Yes:
        if (Status::isOpen()) 
        {
            $widget->selected = true;
            $widget->title = "Today's ".ucfirst($cmtName);
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
                $widget->title = "Today's $cmtName";
                $widget->text = AppCopy::getValue('sold-out-text');
            }
            // Otherwise, we're closed
            else
            {
                // Is there an ULM for today? (account for buffer time)
                $ulm = OdMenu::getUpcomingLateMenu();
                
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
                    $openingAt = Carbon::parse($upcomingToday->displayStartTime, Clock::getTimezone())->format('h:ia');
                    
                    // Are we late?
                    if ( strtotime(Clock::getLocalCarbon()->toTimeString()) > strtotime($upcomingToday->displayStartTime) ) {
                        // Get the next 5 minute mark
                        $now = time();
                        #var_dump($now); die(); #0
                        $nextFive = ceil($now/300)*300;
                        #$carbon = new Carbon($nextFive, 'UTC');
                        #die($now); #0
                        $openingAt = Carbon::createFromFormat('U', $nextFive, 'UTC')->setTimezone(Clock::getTimezone())->format('h:ia');
                    }
                     
                    $widget->title = "Today's $cmtName";
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
                        $openingAt = Carbon::parse($nextMenu->Menu->displayStartTime, Clock::getTimezone())->format('h:ia');
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
