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

class MessageController extends Controller
{
    use AuthenticatesUsers;





    public function getMessageData(Request $request){
        try{
            $user_id=Auth::id();
            $type=$request->input('type');
            $res=MessageNotify::with('send_user')
                ->with('receive_user')
                ->where('pid',0)
               ->where(function ($query)use($user_id,$type){
                   $query->where(function ($query)use($user_id,$type){
                       $query
                           ->where('receive_user_type',$type)
                           ->where('receive_user_id',$user_id);
                   })->Orwhere(function ($query)use($user_id,$type){
                       $query->where('send_user_id',$user_id)
                           ->where('send_user_type',$type)
                       ;
                   });
               })
                ->paginate(10)
                ->toArray();
             $ids=array_column($res['data'],'id');
            $treeDatas=array();
            foreach ($ids as $id) {
                $path='-'.$id.'-';
                $treeData=MessageNotify::with('send_user')
                    ->with('receive_user')
                    ->where(function ($query) use($id,$path){
                        $query->where('id',$id)
                            ->Orwhere('path','like','%'.$path.'%');
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
            $messageNum=MessageNotify::where('receive_user_id',$user_id)
                ->where('receive_user_type',$type)
                ->where('status',0)
                ->count();
            $this->socket($user_id,$messageNum);
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




    public function addMessage(Request $request){
        try{

            $receive_user_id=$request->input('user_id');
            $content=$request->input('content');
            $send_type=$request->input('send_type');
            $receive_type=$request->input('receive_type');
            $send_user_id=$user_id=Auth::id();
            MessageNotify::create(array(
                    'status'=>0,
                    'send_user_id'=>$send_user_id,
                    'send_user_type'=>$send_type,
                    'receive_user_id'=>$receive_user_id,
                    'receive_user_type'=>$receive_type,
                    'content'=>$content,
                    'pid'=>0
                ));

            $messageNum=MessageNotify::where('receive_user_id',$receive_user_id)
                ->where('receive_user_type',$receive_type)
                ->where('status',0)
                ->count();

            $this->socket($receive_user_id,$messageNum);
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

    public function replyMessage(Request $request){
        try{
            $rowData=$request->input('row_data');
            $receive_user_id=$rowData['send_user_id'];
            $receive_user_type=$rowData['send_user_type'];
            $content=$request->input('content');
            $user_type=$request->input('user_type');
            $send_user_id=Auth::id();

            MessageNotify::create(array(
                'status'=>0,
                'send_user_id'=>$send_user_id,
                'send_user_type'=>$user_type,
                'receive_user_id'=>$receive_user_id,
                'receive_user_type'=>$receive_user_type,
                'content'=>$content,
                'pid'=>$rowData['id'],
                'path'=>$rowData['path'].'-'.$rowData['id'].'-',
            ));
            $messageNum=MessageNotify::where('receive_user_id',$receive_user_id)
                ->where('receive_user_type',$receive_user_type)
                ->where('status',0)
                ->count();

            $this->socket($receive_user_id,$messageNum);
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



    public function socket($user_id,$messageNotifyCount){
        // 建立socket连接到内部推送端口
        try{
            $type='message';
            //$user_id=Auth::id();

//            $messageNotifyRes=MessageNotify::where(function ($query)use($user_id){
//                $query->where('receive_user_id',$user_id)
//                    ->Orwhere('send_user_id',$user_id);
//            })
//                ->where('status',0)
//                ->get();
//            $messageNotifyCount=count($messageNotifyRes);

            $client = stream_socket_client('tcp://127.0.0.1:5678', $errno, $errmsg, 1);

            // 推送的数据，包含uid字段，表示是给这个uid推送

            $data = array( 'type'=>$type,'show_num'=>$messageNotifyCount,'is_broadcast'=>0,'uid'=>$user_id);

            // 发送数据，注意5678端口是Text协议的端口，Text协议需要在数据末尾加上换行符

            fwrite($client, json_encode($data)."\n");

            // 读取推送结果

            $res=fread($client, 8192);
            if(trim($res)=='success'){
                return 'success';
            }
            return 'fail';
        }catch (\Exception $exception){
            return 'fail';
        }


    }


    public function getMessageNotifyByReceiveId(Request $request){
        $type=$request->input('type');
        $id=$request->input('id');
        return MessageNotify::where('receive_user_type',$type)->where('receive_user_id',$id)->where('status',0)->get();
    }


}
