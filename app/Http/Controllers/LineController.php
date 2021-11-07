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
    protected  $channelId='1656575554';
    protected  $channelSecret='4685b128c20fc655b392f09b0413442f';
    protected  $callbackUrl='https://myteachceshi.herokuapp.com/auth';

    protected $lineBaseUrl='https://api.line.me/oauth2/v2.1/token';

    public function gotoauthpage(){
        $state = time().'xxx';
        $nonce =  time().'sss';
        session($this->lineWebLoginState,$state);
        session($this->nonce,$nonce);
        $scope="profile%20openid";
        $url="https://access.line.me/oauth2/v2.1/authorize?response_type=code"
            ."&client_id=" .$this->channelId
            ."&redirect_uri=".$this->callbackUrl
            ."&state=".$state
            ."&scope=".$scope
            ."&nonce=".$nonce;
        return redirect($url);
    }


    public function auth(Request $request){
        try{
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
            $lineWebLoginState=session($this->lineWebLoginState);
            Log::info('$lineWebLoginState:'.$lineWebLoginState);
            if ($state!=$lineWebLoginState){
                return redirect('/sessionError');
            }
            session($this->lineWebLoginState,null);

            $curlRes=$this->getAccessToken($code);
            if(!empty($curlRes['code'])){
                return $curlRes['msg'];
            }
            $token=$curlRes;
            if(!$token){
                throw new \Exception('获取token失败');
            }
            Log::info('tokennnnn:'.$token);
            session($this->accessToken,$token);
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
        $accessToken=session($this->accessToken);
        if(empty($accessToken)){
            return redirect('/gotoauthpage');
        }
        $token=json_decode($accessToken,true);
        $key=$this->channelSecret;
        $idToken = JWT::decode($token['id_token'], $key, array('HS256'));
        Log::info('id_token1:'.$idToken);
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
            'redirect_uri'=>$this->callbackUrl,
            'client_id'=>$this->channelId,
            'client_secret'=>$this->channelSecret
        ];
//        $client=new Client();
//      return  $client->request('POST',$this->lineBaseUrl,$params);
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
        if(!$exist){

             $header=[
                'Content-Type:application/json',
                'Authorization: Bearer 4haMb+fjavg5PA+9fBHOxqrEFVLTzhKEL6bX3BxdyPPvH/lVUuNP3KAkDQDF70LECwjRwgeQHpB4vl/W7i9YiC92idVKSxmQJm/rVGYm6qz24OQIK5qvsS+k3VlrFdTXgqKDlRQWGzAuLbwqfrlvmAdB04t89/1O/w1cDnyilFU='
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

    //获得audienceGroupid
    public function getAudienceGroupId(){

        $key = "1636301370sss";
        $payload = array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "iat" => 1356999524,
            "nbf" => 1357000000,
            'dd'=>'sfdsda'
        );
        $jwt = JWT::encode($payload, $key, 'HS256');
        $jwt='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL2FjY2Vzcy5saW5lLm1lIiwic3ViIjoiVTVhNWZlNjU1MTk5MTNiN2YwMWUyOTQ5N2Y2MTRhN2JjIiwiYXVkIjoiMTY1NjU3NTU1NCIsImV4cCI6MTYzNjMwNDk3NywiaWF0IjoxNjM2MzAxMzc3LCJub25jZSI6IjE2MzYzMDEzNzBzc3MiLCJhbXIiOlsibGluZXNzbyJdLCJuYW1lIjoi5Lu75YeM5LqRIn0.SHLl0dyNrduXlSnehbNe6QwNbwDwcolyE3o401PP2to';
//        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        $key=$this->channelSecret;

        $decoded = JWT::decode($jwt, $key,array('HS256'));
        print_r($decoded);

//        $url='https://api.line.me/v2/bot/audienceGroup/click';
//        $header=[
//            'Content-Type:application/json',
//            'Authorization: Bearer 4haMb+fjavg5PA+9fBHOxqrEFVLTzhKEL6bX3BxdyPPvH/lVUuNP3KAkDQDF70LECwjRwgeQHpB4vl/W7i9YiC92idVKSxmQJm/rVGYm6qz24OQIK5qvsS+k3VlrFdTXgqKDlRQWGzAuLbwqfrlvmAdB04t89/1O/w1cDnyilFU='
//        ];
//        $res=$this->curl($url, $params = false, $ispost = 0, 1,$header);
//        Log::info('getAudienceGroupId:'.json_encode($res));
    }



}
