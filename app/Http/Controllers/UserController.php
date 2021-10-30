<?php

namespace App\Http\Controllers;


use App\Jobs\Email;
use App\MessageNotify;
use App\School;
use App\Student;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use mysql_xdevapi\Exception;

class UserController extends Controller
{
    public function getUserInfoById(Request $request){
        $uid=$request->input('uid');
        $type=$request->input('type');
        if($type=='teacher'){
            $res=User::where('id',$uid)->first();
        }else{
            $res=Student::where('id',$uid)->first();
        }
        return $res;
    }


}
