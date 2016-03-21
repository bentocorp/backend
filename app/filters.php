<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{
	//
});


App::after(function($request, $response)
{
    $response->headers->set('Access-Control-Allow-Origin','*');
    $response->headers->set('Access-Control-Allow-Methods','POST, GET, OPTIONS');
    $response->headers->set('Access-Control-Allow-Headers','Content-Type');
    
    return $response;
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
	if (Auth::guest())
	{
		if (Request::ajax())
		{
			return Response::make('Unauthorized', 401);
		}
		#return Redirect::guest('login');
                return Response::make('Unauthorized', 401);
	}
});


Route::filter('auth.basic', function()
{
	return Auth::basic();
});


/*
|--------------------------------------------------------------------------
| Custom Filters
|--------------------------------------------------------------------------
|
| Our custom application filters.
|
*/

Route::filter('admin', 'Bento\Filter\AdminFilter'); // The admin panel
Route::filter('admin_api', 'Bento\Filter\AdminApiFilter'); // The admin panel, via API
Route::filter('api_auth', 'Bento\Filter\ApiAuthFilter'); // The consumer app API
Route::filter('ext_api', 'Bento\Filter\ExtApiFilter'); // An API for external vendors or other services


/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
	if (Auth::check()) return Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
	if (Session::token() !== Input::get('_token'))
	{
		throw new Illuminate\Session\TokenMismatchException;
	}
});
