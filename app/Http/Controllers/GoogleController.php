<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function index()
    {
        return view('web.auth');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'google_id' => $googleUser->getId(),
                'name' => $googleUser->getName(),
                'password' => bcrypt(Str::random(24)),
                'email_verified_at' => now()
            ]
        );

        Auth::login($user);

        return redirect(route('tasks.index'));
    }
}
