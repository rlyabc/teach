<?php

namespace App\Http\Controllers;


use App\Events\MessageSent;
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
use Pusher\Pusher;

class MessagePusherController extends Controller
{
    use AuthenticatesUsers;





    public function getMessageData(Request $request){
        try{
            $user_id=Auth::id();
            $type=$request->input('type');
            $res=MessageNotify::with('send_user')
                ->with('receive_user')
                ->with('send_student_user')
                ->with('receive_student_user')
                ->where('pid',0)
               ->where(function ($query)use($user_id,$type){
                   if($type=='student'){
                       $query->where(function ($query)use($user_id,$type){
                           $query->where('send_student_id',$user_id)
                           ;
                       })->orWhere(function ($query)use($user_id,$type){
                           $query->where('receive_student_id',$user_id)
                           ;
                       });
                   }else{
                       $query->where(function ($query)use($user_id,$type){
                           $query->where('send_user_id',$user_id)
//                               ->where('send_user_type',$type)
                           ;
                       })->orWhere(function ($query)use($user_id,$type){
                           $query->where('receive_user_id',$user_id)
                           ;
                       });
                   }

               })
                ->paginate(10)
                ->toArray();
             $ids=array_column($res['data'],'id');
            $treeDatas=array();
            foreach ($ids as $id) {
                $path='-'.$id.'-';
                $treeData=MessageNotify::with('send_user')
                    ->with('receive_user')
                    ->with('send_student_user')
                    ->with('receive_student_user')
                    ->where(function ($query) use($id,$path){
                        $query->where('id',$id)
                            ->orWhere('path','like','%'.$path.'%');
                    })->get();
                if($treeData){
                    $treeData=$treeData->toArray();
                    $treeDatas=array_merge($treeData,$treeDatas);
                }

            }


            $treeRes=$res=$this->toTree($treeDatas, 0);
            $res['data']=$treeRes;

            return array(
                'code'=>200,
                'data'=>$res
            );
        }catch (\Exception $exception){
            return array(
                'code'=>1001,
                'msg'=>$exception->getMessage()
            );
        }
    }

    public function updateMessageStatus(Request $request){
        try{
            $user_id=Auth::id();
            $id=$request->input('id');
            $type=$request->input('type');
             MessageNotify::where('id',$id)
                ->update(array(
                    'status'=>1
                ));
             $this->updateMessageNumByReceiveUserId($type,$user_id);

            return array(
                'code'=>200,
                'msg'=>'操作成功'
            );
        }catch (\Exception $exception){
            return array(
                'code'=>1001,
                'msg'=>$exception->getMessage()
            );
        }
    }




    public function addMessage(Request $request){
        try{

            $receive_user_id=$request->input('user_id');
            $content=$request->input('content');
            $send_type=$request->input('send_type');
            $receive_type=$request->input('receive_type');
            $send_user_id=$user_id=Auth::id();
            $params=[
                'status'=>0,
                'send_user_type'=>$send_type,
                'receive_user_type'=>$receive_type,
                'content'=>$content,
                'pid'=>0
            ];
            if($send_type=='teacher'){
                $params['send_user_id']=$send_user_id;
            }
            if($receive_type=='teacher'){
                $params['receive_user_id']=$receive_user_id;
            }
            if($send_type=='student'){
                $params['send_student_id']=$send_user_id;
            }
            if($receive_type=='student'){
                $params['receive_student_id']=$receive_user_id;
            }
            MessageNotify::create($params);

            $this->updateMessageNumByReceiveUserId($receive_type,$receive_user_id);

            return array(
                'code'=>200,
                'msg'=>'操作成功'
            );
        }catch (\Exception $exception){
            return array(
                'code'=>1001,
                'msg'=>$exception->getMessage()
            );
        }
    }

//    public function updateMessageNumBySendUserId($send_type,$send_user_id){
//        $messageNum=0;
//        if($send_type=='teacher'){
//            $messageNum=MessageNotify::where('receive_user_id',$send_user_id)
//                ->where('send_user_type',$send_type)
//                ->where('status',0)
//                ->count();
//        }
//        if($send_type=='student'){
//            $messageNum=MessageNotify::where('receive_student_id',$send_user_id)
//                ->where('receive_user_type',$send_type)
//                ->where('status',0)
//                ->count();
//        }
//        $this->pushMessage($send_user_id,$messageNum);
//        return ;
//    }

    public function updateMessageNumByReceiveUserId($receive_type,$receive_user_id){
        $messageNum=0;
        if($receive_type=='teacher'){
            $messageNum=MessageNotify::where('receive_user_id',$receive_user_id)
                ->where('receive_user_type',$receive_type)
                ->where('status',0)
                ->count();
        }
        if($receive_type=='student'){
            $messageNum=MessageNotify::where('receive_student_id',$receive_user_id)
                ->where('receive_user_type',$receive_type)
                ->where('status',0)
                ->count();
        }
        $this->pushMessage($receive_user_id,$messageNum,$receive_type);
        return ;
    }

    public function replyMessage(Request $request){
        try{
            $rowData=$request->input('row_data');
            $receive_user_id=$rowData['send_user_id'];
            $receive_user_type=$rowData['send_user_type'];
            $content=$request->input('content');
            $user_type=$request->input('user_type');
            $send_user_id=Auth::id();


            $params=array(
                'status'=>0,
                'send_user_type'=>$user_type,
                'receive_user_type'=>$receive_user_type,
                'content'=>$content,
                'pid'=>$rowData['id'],
                'path'=>$rowData['path'].'-'.$rowData['id'].'-',
            );
            if($user_type=='teacher'){
                $params['send_user_id']=$send_user_id;
            }
            if($receive_user_type=='teacher'){
                $params['receive_user_id']=$receive_user_id;
            }
            if($user_type=='student'){
                $params['send_student_id']=$send_user_id;
            }
            if($receive_user_type=='student'){
                $params['receive_student_id']=$receive_user_id;
            }
            MessageNotify::where('id',$rowData['id'])->update(array(
               'status'=>1
            ));
            MessageNotify::create($params);
            $this->updateMessageNumByReceiveUserId($receive_user_type,$receive_user_id);
            $this->updateMessageNumByReceiveUserId($user_type,$send_user_id);
            return array(
                'code'=>200,
                'message'=>'操作成功'
            );
        }catch (\Exception $exception){
            return array(
                'code'=>1001,
                'msg'=>$exception->getMessage()
            );
        }
    }



    public function pushMessage($user_id,$messageNotifyCount,$type='teacher'){
        // 建立socket连接到内部推送端口
        try{
            $data = array( 'type'=>$type,'show_num'=>$messageNotifyCount,'is_broadcast'=>0,'uid'=>$user_id);
            broadcast(new MessageSent($user_id, $data,$type));
        }catch (\Exception $exception){
            return 'fail';
        }


    }


    public function getNotifySumByUserId(Request $request){
        $type=$request->input('type');
        $id=$request->input('uid');
        if($type=='teacher'){
            $count=MessageNotify::where('receive_user_type',$type)->where('receive_user_id',$id)->where('status',0)->count();
        }else{
            $count=MessageNotify::where('receive_user_type',$type)->where('receive_student_id',$id)->where('status',0)->count();
        }

        return array(
            'code'=>200,
            'msg'=>'操作成功',
            'data'=>$count
        );
    }

    public function auth(Request $request){
        $channel=$request->channel_name;
        $socket_id=$request->socket_id;
        $app_key='07a4dd252d3d733a0c26';
        $app_sec='379c64bfe31b75beb56e';
        $app_id='1290170';
        $pusher=new Pusher($app_key, $app_sec, $app_id, [
            'cluster' => 'ap1',
            'encrypted' => true,
            'useTLS' => false
        ]);
        return $res=$pusher->socket_auth($channel,$socket_id);
    }


}
