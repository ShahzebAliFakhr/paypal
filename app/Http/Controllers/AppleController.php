<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use App\Services\AppleToken;

class AppleController extends Controller
{
    public function redirectToApple(){
        return Socialite::driver('apple')->redirect();
    }

    public function handleAppleCallback(AppleToken $appleToken){
        try {
            config()->set('services.apple.client_secret', $appleToken->generate());
            $user = Socialite::driver('apple')->stateless()->user();
            dd($user);
        }catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
