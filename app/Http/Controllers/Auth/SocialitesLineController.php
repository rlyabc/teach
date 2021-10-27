<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Socialite;

class SocialitesLineController extends Controller
{
    //跳转到Line授权页面
    public function line()
    {
        return Socialite::with('line')->redirect();
    }

    //用户授权后，跳转回来
    public function callback()
    {
        $info = Socialite::driver('line')->user();
        dump($info);
    }
}
