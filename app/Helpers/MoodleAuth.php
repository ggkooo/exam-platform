<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class MoodleAuth
{
    /**
     * Check if the user is authenticated
     *
     * @return bool
     */
    public static function check()
    {
        return Session::has('logged_in') && Session::get('logged_in') === true;
    }

    /**
     * Get authenticated user information
     *
     * @return object|null
     */
    public static function user()
    {
        if (!self::check()) {
            return null;
        }

        return (object)[
            'id' => Session::get('moodle_user_id'),
            'username' => Session::get('moodle_username'),
            'name' => Session::get('moodle_name'),
            'email' => Session::get('moodle_email'),
        ];
    }

    /**
     * Ensure the user is authenticated
     * If not, redirect to login page
     *
     * @return \Illuminate\Http\RedirectResponse|null
     */
    public static function require()
    {
        if (!self::check()) {
            return Redirect::route('login');
        }

        return null;
    }
}