<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Exception;

class LoginWithGoogleController extends Controller
{
    public function handleGoogleCallback()
    {

        try {
      
            $user = Socialite::driver('google')->user();
       
            $finduser = User::where('google_id', $user->id)->first();
            if($finduser){
       
                
                Auth::login($finduser);
                 return response()->json([
                       "status"  => true,
                       "message"  => "Login success",
                    ]);
            }
            else{
               
                    return response()->json([
                       "status"  => false,
                       "message"  => "Login failed",
                    ]);
            }

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    } 

}
