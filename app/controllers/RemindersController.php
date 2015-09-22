<?php

namespace Bento\Ctrl;

use User;
use View;
use Password;
use Hash;
use Redirect;
use Lang;
use App;
use Input;


class RemindersController extends \Controller {

	/**
	 * Display the password reminder view.
	 *
	 * @return Response
	 */
	public function getRemind()
	{
                return View::make('password.remind');
	}

	/**
	 * Handle a POST request to remind a user of their password.
	 *
	 * @return Response
	 */
	public function postRemind()
	{
                // A FB user can't reset their password.
                $user = User::findByEmail(Input::get('email'));
                
                if ($user !== NULL && $user->reg_type != 'auth')
                    return Redirect::back()->with('error', Lang::get('reminders.facebook_user'));
            
                // Otherwise, let's try
                switch ($response = Password::remind(Input::only('email'), function($message)
                    {
                        $message->subject(Lang::get('reminders.subject'));
                        $message->from('help@bentonow.com', 'Bento');
                    }))
		{
			case Password::INVALID_USER:
				return Redirect::back()->with('error', Lang::get($response));

			case Password::REMINDER_SENT:
				return Redirect::back()->with('status', Lang::get($response));
		}
	}

	/**
	 * Display the password reset view for the given token.
	 *
	 * @param  string  $token
	 * @return Response
	 */
	public function getReset($token = null)
	{
		if (is_null($token)) App::abort(404);

		return View::make('password.reset')->with('token', $token);
	}

	/**
	 * Handle a POST request to reset a user's password.
	 *
	 * @return Response
	 */
	public function postReset()
	{
		$credentials = Input::only(
			'email', 'password', 'password_confirmation', 'token'
		);

		$response = Password::reset($credentials, function($user, $password)
		{
			$user->password = Hash::make($password);

			$user->save();
		});

		switch ($response)
		{
			case Password::INVALID_PASSWORD:
			case Password::INVALID_TOKEN:
			case Password::INVALID_USER:
				return Redirect::back()->with('error', Lang::get($response));

			case Password::PASSWORD_RESET:
				#return Redirect::to('/');
                                return Redirect::back()->with('success', Lang::get($response));
		}
	}

}
