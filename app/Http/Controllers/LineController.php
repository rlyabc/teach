<?php

namespace App\Http\Controllers;


use App\Jobs\Email;
use App\LineMessageUser;
use App\MessageNotify;
use App\School;
use App\Student;
use App\User;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Lcobucci\JWT\Signer\Key;
use mysql_xdevapi\Exception;

class LineController extends Controller
{
    use AuthenticatesUsers;


    protected  $lineWebLoginState = "lineWebLoginState";

    protected  $accessToken = "accessToken";

    protected  $nonce = "nonce";

    protected  $authorizationCode='authorization_code';
    protected  $refreshToken='refresh_token';

//    protected  $channelId='1656610327';
//    protected  $channelSecret='aec810d7d878bf2638d1a9bc7f710df3';

//    protected  $callbackUrl='https://myteachceshi.herokuapp.com/auth';

    protected $lineBaseUrl='https://api.line.me/oauth2/v2.1/token';

    public function gotoAuthPage(){
//        session_start();
        $state = time().mt_rand(0,9999);
        $nonce =  time().mt_rand(0,9999);
//        $_SESSION[$this->lineWebLoginState]=$state;
//        $_SESSION[$this->nonce]=$nonce;
        Cache::forever($this->lineWebLoginState,$state);
        Cache::forever($this->nonce,$nonce);
        $scope="profile%20openid";
        $url="https://access.line.me/oauth2/v2.1/authorize?response_type=code"
            ."&client_id=" .config('services.LineChannelId')
            ."&redirect_uri=".config('services.LineLoginCallbackUrl')
            ."&state=".$state
            ."&scope=".$scope
            ."&nonce=".$nonce
            ."&bot_prompt=normal";
        return redirect($url);
    }


    public function auth(Request $request){
        try{
            //session_start();
            $inputs=$request->input();
            $code=$inputs['code'];
            $state=$inputs['state'];
            Log::info('code:'.$code);
            Log::info('state:'.$state);

            if(!empty($inputs['scope'])){
                Log::info('scope:'.$inputs['scope']);
            }
            if(!empty($inputs['error'])){
                Log::info('error:'.$inputs['error']);
                return redirect('/loginCancel');
            }
            if(!empty($inputs['errorCode'])){
                Log::info('errorCode:'.$inputs['errorCode']);
                return redirect('/loginCancel');
            }
            if(!empty($inputs['errorMessage'])){
                Log::info('errorMessage:'.$inputs['errorMessage']);
                return redirect('/loginCancel');
            }
//            if (isset($_SESSION[$this->lineWebLoginState])&&$state!=$_SESSION[$this->lineWebLoginState]){
//                return redirect('/sessionError');
//            }
            $lineWebLoginState=Cache::get($this->lineWebLoginState);
            if ($state!=$lineWebLoginState){
                Log::info('$lineWebLoginState:'.$lineWebLoginState);
                return redirect('/sessionError');
            }
//            unset($_SESSION[$this->lineWebLoginState]);
            Cache::forget($this->lineWebLoginState);
            $curlRes=$this->getAccessToken($code);
            if(!empty($curlRes['code'])){
                return $curlRes['msg'];
            }
            $token=$curlRes;
            if(!$token){
                throw new \Exception('获取token失败');
            }
            Log::info('tokennnnn:'.$token);
            $_SESSION[$this->accessToken]=$token;
            return redirect('/line');
        }catch (\Exception $exception){
            return array(
                'code'=>1001,
                'msg'=>$exception->getMessage()
            );
        }
    }

    public function getLogin(){
        return view('line/login');
    }


    public function getLoginCancel(){
        return view('line/login_cancel');
    }

    public function getSessionError(){
        return view('line/session_error');
    }

    public function getSuccess(){

//        session_start();
        $accessToken=Cache::get($this->accessToken);
        if(empty($accessToken)){
            return redirect('/gotoAuthPage');
        }
//        $accesstoken=$_SESSION[$this->accessToken];
        $token=json_decode($accessToken,true);

        JWT::$leeway = 60; // $leeway in seconds
        $key=config('services.LineChannelSecret');
        $idToken = JWT::decode($token['id_token'], $key, array('HS256'));
        $idToken =json_decode(json_encode($idToken),true);
        $line_user_id=$idToken['sub'];
        $teacherUser=$this->getTeacherByLineUserId($line_user_id);
        $studentUser=$this->getStudentByLineUserId($line_user_id);
        $viewRes=[
            'idToken'=>$idToken,
            'teacherUser'=>$teacherUser,
            'studentUser'=>$studentUser
        ];

        return view('line/success',$viewRes);
    }

    protected function getTeacherByLineUserId($line_user_id){
        return User::where('line_user_id',$line_user_id)->where('email_verify',1)->first();
    }

    protected function getStudentByLineUserId($line_user_id){
        return Student::where('line_user_id',$line_user_id)->get();
    }

    protected function getAccessToken($code){

        $params=[
            'grant_type'=>$this->authorizationCode,
            'code'=>$code,
            'redirect_uri'=>config('services.LineLoginCallbackUrl'),
            'client_id'=>config('services.LineChannelId'),
            'client_secret'=>config('services.LineChannelSecret')
        ];
        $params=http_build_query($params);
       return $curlRes=curl($this->lineBaseUrl,$params,1,1);



    }

    public function lineBind(Request $request){
        $lineUserId=$request->input('line_user_id');
        $password=$request->input('password');
        $type=$request->input('type');
        if($type=='teacher'){
            $email=$request->input('email');
            if($lineUserId&&$email){
                $exists=User::where('line_user_id',$lineUserId)->where('email_verify',1)->first();
                if($exists){
                    return  array(
                        'code'=>1001,
                        'msg'=>'教师只能绑定一个'
                    );
                }

                $user=User::where('email',$email)->where('email_verify',1)->first();
                if($user&&Hash::check($password,$user->password)){
                    if($user['line_user_id']==$lineUserId){
                        return  array(
                            'code'=>1001,
                            'msg'=>'你已经绑定过了'
                        );
                    }
                    User::where('email',$email)
                        ->where('email_verify',1)
                        ->update(array(
                            'line_user_id'=>$lineUserId
                        ));
                    return  array(
                        'code'=>200,
                        'msg'=>'绑定成功'
                    );
                }
            }

        }

        if($type=='student'){
            $name=$request->input('name');
            if($lineUserId&&$name){
                $user=Student::where('name',$name)->first();
                if($user&&Hash::check($password,$user->password)){
                    if($user['line_user_id']==$lineUserId){
                        return  array(
                            'code'=>1001,
                            'msg'=>'你已经绑定过了'
                        );
                    }
                    Student::where('name',$name)
                        ->update(array(
                            'line_user_id'=>$lineUserId
                        ));
                    return  array(
                        'code'=>200,
                        'msg'=>'绑定成功'
                    );
                }
            }
        }
        return  array(
            'code'=>1001,
            'msg'=>'参数错误'
        );
    }



    public function messageCallback(Request $request){
        $inputs=$request->input();
        Log::info('messageCallback:'.json_encode($inputs));
        //获得message-user-id
        $userId=$inputs['events'][0]['source']['userId'];
        Log::info('userId:'.$userId);
        $exist=LineMessageUser::where('message_user_id',$userId)->first();
//        4haMb+fjavg5PA+9fBHOxqrEFVLTzhKEL6bX3BxdyPPvH/lVUuNP3KAkDQDF70LECwjRwgeQHpB4vl/W7i9YiC92idVKSxmQJm/rVGYm6qz24OQIK5qvsS+k3VlrFdTXgqKDlRQWGzAuLbwqfrlvmAdB04t89/1O/w1cDnyilFU=
        $messageAccessToken=config('services.LineMessageAccessToken');
        if(!$exist){

             $header=[
                'Content-Type:application/json',
                'Authorization: Bearer '.$messageAccessToken
            ];
            $getProfileUrl="https://api.line.me/v2/bot/profile/".$userId;
            $profileRes=curl($getProfileUrl,false,0,1,$header);
            Log::info('profileRes:'.$profileRes);
            $profileRes=json_decode($profileRes,true);
            LineMessageUser::create(array(
                'message_user_id'=>$userId,
                'name'=>$profileRes['displayName']
            ));
        }
    }




}
