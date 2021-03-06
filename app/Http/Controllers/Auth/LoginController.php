<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Student;
use App\User;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }



    public function login(Request $request)
    {
        try {
           $type=$request->input('type');
            $lineUserId=$request->input('line_user_id');
           if($type=='teacher'){
               $request= $this->teacherLogin($request);
           }elseif($type=='student'){
                $request= $this->studentLogin($request);
            }elseif($type=='line'){
               $user_type=$request->input('user_type');
               if($user_type=='teacher'){
                   $user = User::where('line_user_id',$lineUserId)->where('email_verify',1)->first();
                   $token= $user->createToken('teach')->accessToken;
               }else{
                   $student_id=$request->input('student_id');
                   $user = Student::where('line_user_id',$lineUserId)
                       ->where('id',$student_id)
                       ->first();
                   $token= $user->createToken('student')->accessToken;
               }

               return array(
                   'code'=>200,
                   'data'=>array('access_token'=>$token),
                   'msg' =>'????????????'
               );
           }else{
               return array(
                   'code'=>1001,
                   'msg' =>'????????????'
               );
           }

        } catch (\Exception $e) {
            return array(
                'code'=>1001,
                'msg' =>'??????????????????1'
            );
        }

        if ($request->getStatusCode() == 401) {
            return array(
                'code'=>1001,
                'msg' =>'??????????????????2'
            );
        }
        $data=$request->getBody()->getContents();


        return array(
            'code'=>200,
            'data'=>json_decode($data,true),
            'msg' =>'????????????'
        );
    }

    protected function teacherLogin($request){
        $client = new Client();
        $request = $client->request('POST', request()->root() . '/oauth/token', [
            'form_params' =>[
                'grant_type' => 'password',
                'client_id' => config('services.api.appid'),
                'client_secret' => config('services.api.secret'),
                'username' => $request->input('email'),
                'password' => $request->input('password'),
                'scope' => '*',
                'guard' => 'api'
            ]
        ]);
        return $request;
    }


    protected function studentLogin(Request $request)
    {
        $client = new Client();
        $request = $client->request('POST', request()->root() . '/oauth/token', [
            'form_params' =>[
                'grant_type' => 'password',
                'client_id' => config('services.student_api.appid'),
                'client_secret' => config('services.student_api.secret'),
                'username' => $request->input('name'),
                'password' => $request->input('password'),
                'scope' => '*',
                'guard' => 'student_api'
            ]
        ]);
        return $request;
    }


    /**
     * ????????????
     */
    public function logout()
    {
        if (Auth::guard('api')->check()) {
            Auth::guard('api')->user()->token()->delete();
        }
        if (Auth::guard('student_api')->check()) {

            Auth::guard('student_api')->user()->token()->delete();
        }
        return response()->json(['msg' => '????????????', 'code' => 200, 'data' => null]);
    }

}
