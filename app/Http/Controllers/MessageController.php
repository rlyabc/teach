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
                'message'=>'????????????'
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
                'message'=>'????????????'
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
//        $this->socket($send_user_id,$messageNum);
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
        $this->socket($receive_user_id,$messageNum,$receive_type);
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
                'message'=>'????????????'
            );
        }catch (\Exception $exception){
            return array(
                'code'=>1001,
                'msg'=>$exception->getMessage()
            );
        }
    }



    public function socket($user_id,$messageNotifyCount,$type='teacher'){
        // ??????socket???????????????????????????
        try{
            $client = stream_socket_client('tcp://127.0.0.1:5678', $errno, $errmsg, 1);

            // ????????????????????????uid???????????????????????????uid??????

            $data = array( 'type'=>$type,'show_num'=>$messageNotifyCount,'is_broadcast'=>0,'uid'=>$user_id);

            // ?????????????????????5678?????????Text??????????????????Text??????????????????????????????????????????

            fwrite($client, json_encode($data)."\n");

            // ??????????????????
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
        if($type=='teacher'){
            return MessageNotify::where('receive_user_type',$type)->where('receive_user_id',$id)->where('status',0)->get();
        }
        return MessageNotify::where('receive_user_type',$type)->where('receive_student_id',$id)->where('status',0)->get();
    }


}
