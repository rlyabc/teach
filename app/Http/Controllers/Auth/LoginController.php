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
//        try {
           $type=$request->input('type');
            $email=$request->input('email');
            $lineUserId=$request->input('line_user_id');
            $name=$request->input('name');
           if($type=='teacher'){
               $request= $this->teacherLogin($request);
           }elseif($type=='student'){
                $request= $this->studentLogin($request);
            }elseif($type=='line'){
               $request= $this->lineLogin($request);
           }else{
               return array(
                   'code'=>1001,
                   'msg' =>'参数错误'
               );
           }

//        } catch (\Exception $e) {
//            return array(
//                'code'=>1001,
//                'msg' =>'账号验证失败1'
//            );
//        }

        if ($request->getStatusCode() == 401) {
            return array(
                'code'=>1001,
                'msg' =>'账号验证失败2'
            );
        }
        $data=$request->getBody()->getContents();

        if(($type=='teacher')&&$lineUserId){
            User::where('email',$email)
                ->update(array(
                    'line_user_id'=>$lineUserId
                ));
        }elseif(($type=='student')&&$lineUserId){
             Student::where('name',$name)
            ->update(array(
                'line_user_id'=>$lineUserId
            ));
        }

        return array(
            'code'=>200,
            'data'=>json_decode($data,true),
            'msg' =>'登录成功'
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
                'client_id' => config('services.api.appid'),
                'client_secret' => config('services.api.secret'),
                'username' => $request->input('name'),
                'password' => $request->input('password'),
                'scope' => '*',
                'guard' => 'student_api'
            ]
        ]);
        return $request;
    }

    protected function lineLogin($request){
        $client = new Client();
        $request = $client->request('POST', request()->root() . '/oauth/token', [
            'form_params' =>[
                'grant_type' => 'authorization_code',
                'client_id' => config('services.line_api.appid'),
                'client_secret' => config('services.line_api.secret'),
                'line_user_id' => $request->input('line_user_id'),
                'scope' => '*',
                'guard' => 'line_api'
            ]
        ]);
        return $request;
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        if (Auth::guard('api')->check()) {
            Auth::guard('api')->user()->token()->delete();
        }
        if (Auth::guard('student_api')->check()) {

            Auth::guard('student_api')->user()->token()->delete();
        }
        return response()->json(['msg' => '登出成功', 'code' => 200, 'data' => null]);
    }

}
