<?php

namespace App\Http\Controllers\Auth;

use App\Jobs\Email;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
//    protected function create(array $data)
//    {
//        return User::create([
//            'name' => $data['name'],
//            'email' => $data['email'],
//            'password' => bcrypt($data['password']),
//        ]);
//    }


    public function index(Request $request){
        try{
            DB::beginTransaction();//开启事务
            $inputs=$request->input();
            $password=trim($inputs['password']);
            $name=trim($inputs['name']);
            $email=trim($inputs['email']);
            $users=User::where('email',$email)
                ->where('email_verify',1)
                ->get();
            if(count($users)){
                throw new \Exception('邮箱已经存在');
            }
            $res=User::create(array(
                'name'=>$name,
                'email'=>$email,
                'password'=>Hash::make($password),
                'role'=>'teacher_admin'
            ));
            $user_id=$res->id;
            dispatch(new Email($email,'https://myteachceshi.herokuapp.com/emailVerify?user_id='.$user_id,array('sfsfsf')));
            DB::commit();
            return array(
                'code'=>200,
                'msg'=>'已发送验证邮箱'
            );
        }catch (\Exception $exception){
            DB::rollBack();
            return array(
                'code'=>1001,
                'msg'=>$exception->getMessage()
            );
        }


    }
}
