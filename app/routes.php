<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

// Index
Route::get('/', array('as' => 'index', function() {
    #return View::make('homepage.index');
    return Redirect::away('https://bentonow.com');
}));

// AWS Health check
Route::get('/healthcheck', function() {
    return Response::make('ok', 200);
});

// Bootstrapping (!!! COMMENT OUT WHEN DONE !!!)
#Route::group(array('namespace' => 'Bento\Ctrl'), function() {
#    Route::get('bs/do1', 'BootstrapCtrl@do1');
#});



/****************************************************************************
 * REDIRECT Routes
 ****************************************************************************
 */

// Service Map
Route::get('/service', function() {
    return Redirect::away('http://cdn.bentonow.com/images/bento-error-invalid-address-map.png');
});

// Lyft/Uber Driver Signup
Route::get('/driversignup', function() {
    return Redirect::away('https://docs.google.com/a/bentonow.com/forms/d/1B_zunAaTdTE8MrynmkHclyQ9EJNRIyQFpaXMB_-8avQ/viewform');
});



/****************************************************************************
 * API: [PUBLIC] Routes
 ****************************************************************************
 */
Route::group(array('namespace' => 'Bento\Ctrl'), function() {

    ## PUBLIC /init routes
    Route::get('init/{date?}', 'InitCtrl@getIndex'); #v1
    Route::get('init2', 'Init2Ctrl@getIndex'); #v2

    ## PUBLIC /gatekeeper routes
    Route::controller('gatekeeper', 'GatekeeperCtrl');
    
    ## PUBLIC /menu routes
    Route::get('menu/{date}', 'MenuCtrl@show');
    Route::get('menu/next/{date}', 'MenuCtrl@next');

    ## PUBLIC /status routes
    Route::controller('status', 'StatusCtrl');

    ## PUBLIC /user routes
    Route::post('user/signup', 'UserCtrl@postSignup');
    Route::post('user/fbsignup', 'UserCtrl@postFbsignup');
    Route::post('user/login', 'UserCtrl@postLogin');
    Route::post('user/fblogin', 'UserCtrl@postFblogin');

    ## PUBLIC /coupon routes
    Route::post('coupon/request', 'CouponCtrl@postRequest');

    ## PUBLIC /misc routes
    Route::get('/ioscopy', 'MiscCtrl@getIoscopy');
    Route::get('/servicearea', 'MiscCtrl@getServicearea');

    ## PUBLIC /password routes
    Route::controller('password', 'RemindersController');
});



/****************************************************************************
 * API: [PRIVATE] Authenticated Users Only Routes
 ****************************************************************************
 */
Route::group(array('before' => 'api_auth', 'namespace' => 'Bento\Ctrl'), function() {

    ## /order routes
    #Route::post('order/phase1', 'OrderCtrl@phase1');
    #Route::post('order/phase2', 'OrderCtrl@phase2');
    Route::post('order/', 'OrderCtrl@postIndex');

    ## /user auth routes
    Route::get('user/logout', 'UserCtrl@getLogout');
    Route::get('user/info', 'UserCtrl@getInfo');
    Route::post('user/phone', 'UserCtrl@postPhone');
    Route::post('user/orderhistory', 'UserCtrl@getOrderhistory');

    ## /coupon routes
    Route::controller('coupon', 'CouponCtrl');
});



/****************************************************************************
 * ADMIN: Admins Only Routes
 ****************************************************************************
 *
 * prefix => admin: All routes accessed through /admin/{...}
 * before => admin: Calling the admin filter on all routes.
 */
Route::group(array('prefix' => 'admin', 'before' => 'admin'), function() {
#Route::group(array('prefix' => 'admin'), function() {

    View::share('adminUser', Session::get('adminUser'));

    // Admin index
    Route::get('/', function() {
        return Redirect::to('admin/dashboard');
    });

    Route::controller('dashboard', 'Bento\Admin\Ctrl\DashboardCtrl');

    Route::controller('status', 'Bento\Admin\Ctrl\StatusCtrl');

    Route::controller('driver', 'Bento\Admin\Ctrl\DriverCtrl');

    Route::controller('order', 'Bento\Admin\Ctrl\OrderCtrl');

    Route::controller('pendingorder', 'Bento\Admin\Ctrl\PendingOrderCtrl');

    Route::controller('inventory', 'Bento\Admin\Ctrl\InventoryCtrl');

    Route::controller('menu', 'Bento\Admin\Ctrl\MenuCtrl');

    Route::controller('dish', 'Bento\Admin\Ctrl\DishCtrl');

    Route::controller('user', 'Bento\Admin\Ctrl\UserCtrl');

    Route::controller('apitest', 'Bento\Admin\Ctrl\ApiTestCtrl');

    Route::controller('misc', 'Bento\Admin\Ctrl\MiscCtrl');

    Route::controller('settings', 'Bento\Admin\Ctrl\SettingsCtrl');

    Route::controller('reports', 'Bento\Admin\Ctrl\ReportsCtrl');

}); // /End protected admin rotes

// These need to be able to be called without being logged in (duh)
Route::get('admin/login', function() {
    return View::make('admin.login');
});

Route::controller('admin', 'Bento\Admin\Ctrl\AdminUserCtrl');
/** /End Admin Routes */



/****************************************************************************
 * ADMIN API: [PRIVATE] A secured API corresponding to some /admin functions
 ****************************************************************************
 *
 * prefix => adminapi: All routes accessed through /adminapi/{...}
 * before => admin_api: Calling the Admin API filter on all routes.
 */
Route::group(array('prefix' => 'adminapi', 'before' => 'admin_api', 'namespace' => 'Bento\AdminApi\Ctrl'), function() {

    Route::controller('order', 'OrderCtrl');

}); // /End protected adminapi rotes



/****************************************************************************
 * EXTERNAL API: [PRIVATE] A secured API for vendors, etc.
 ****************************************************************************
 *
 * prefix => extapi: All routes accessed through /extapi/{...}
 * before => ext_api: Calling the external API filter on all routes.
 */
Route::group(array('prefix' => 'extapi', 'before' => 'ext_api', 'namespace' => 'Bento\ExtApi\Ctrl'), function() {

    Route::get('dish/{id}', 'DishCtrl@getIndex');

    Route::controller('dish', 'DishCtrl');

    Route::controller('reports/survey', 'Reports\SurveyCtrl');

}); // /End protected extapi rotes