<?php

use Workerman\Worker;

require_once './Workerman/Autoloader.php';
require_once './Workerman/vendor/autoload.php';

$worker = new Worker('websocket://0.0.0.0:9000');//


$worker->count = 1;

$worker->onWorkerStart = function($worker)

{
    // 开启一个内部端口，方便内部系统推送数据，Text协议格式 文本+换行符
    $inner_text_worker = new Worker('text://0.0.0.0:5678');

    $inner_text_worker->onMessage = function($connection, $buffer)

    {


        // 通过workerman，向uid的页面推送数据

         echo $buffer;
        $buffer_arr=json_decode($buffer,true);
        if(!empty($buffer_arr['is_broadcast'])){
            $ret = broadcast($buffer);
        }else{
            if(empty($buffer_arr['uid'])){
                $ret=false;
            }else{
                $uid=getConnectUid($buffer_arr['uid'],$buffer_arr['type']);
                $sendRes=array('status'=>1,'message'=>'获取成功','data'=>$buffer_arr);

                $ret = sendMessageByUid($uid, json_encode($sendRes));
            }

        }

        // 返回推送结果
        $connection->send($ret ? 'success' : 'fail');
    };

    // ## 执行监听 ##

    $inner_text_worker->listen();

};

// 新增加一个属性，用来保存uid到connection的映射

$worker->uidConnections = array();

// 当有客户端发来消息时执行的回调函数

$worker->onMessage = function($connection, $data)

{

    global $worker;
    $data=json_decode($data,true);
    // 判断当前客户端是否已经验证,既是否设置了uid
    if(!empty($data['uid']))

    {
        if($data['uid']){
            $uid=getConnectUid($data['uid'],$data['type']);
            $connection->uid=$uid;
            $worker->uidConnections[$uid] = $connection;
            send_user_inspection_review_status_message($connection,'获取成功',$data['uid'],$data['type']);

            return;
        }



    }

     send_user_message_error($connection,'uid错误');
    return;

};



// 当有客户端连接断开时

$worker->onClose = function($connection)

{

    global $worker;

    if(isset($connection->uid))

    {

        // 连接断开时删除映射

        unset($worker->uidConnections[$connection->uid]);

    }

};



// 向所有验证的用户推送数据

function broadcast($message)

{

    global $worker;
    $i=0;

    foreach($worker->uidConnections as $connection)

    {
        $i++;
        $connection->send($message);

    }
    return $i;

}



// 针对uid推送数据

function sendMessageByUid($uid, $message)

{
    global $worker;

    if(isset($worker->uidConnections[$uid]))

    {

        $connection = $worker->uidConnections[$uid];

        $connection->send($message);

        return true;

    }

    return false;

}

function  getConnectUid($uid,$type){
    return $uid.$type;
}



//查询验货审核状态消息
function select_inspection_review_status_message($id,$type){
//    $url='https://myteachceshi.herokuapp.com/getMessageNotifyByReceiveId';
    $url='http://www.myteach.com/getMessageNotifyByReceiveId';
    $params=[
        'id'=>$id,
        'type'=>$type
    ];
    $res=curl($url, $params, 0, 0);

    return json_decode($res,true);
}

//发送给用户消息
function send_user_inspection_review_status_message($connection,$message,$uid,$type,$is_broadcast=0){
    $review_messages=select_inspection_review_status_message($uid,$type);

    $count=count($review_messages);
    $return_data=array( 'type'=>'','show_num'=>$count,
        'data'=>'');
    send_user_message_success($connection,$message,$return_data);
}


//发送给用户消息
function send_user_message_success($connection,$message,$data){
    $errmsg=array('status'=>1,'message'=>$message,'data'=>$data);
    $errmsg=json_encode($errmsg);
    $connection->send($errmsg);
}

//发送给用户消息
function send_user_message_error($connection,$message='uid错误'){
    $errmsg=array('status'=>0,'message'=>$message);
    $errmsg=json_encode($errmsg);
    $connection->send($errmsg);
}

//curl请求
function curl($url, $params = false, $ispost = 0, $https = 0,$header=[])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($https) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
    }
    if ($ispost) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);
    } else {
        if ($params) {
            if (is_array($params)) {
                $params = http_build_query($params);
            }
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }
    if($header){
        //设置头文件的信息作为数据流输出
        curl_setopt($ch, CURLOPT_HEADER,0);
        //设置请求头
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);

    }
    $response = curl_exec($ch);

    if (curl_error($ch)) {
        //echo "cURL Error: " . curl_error($ch);
        return [
            'code'=>1001,
            'msg'=> curl_error($ch)
        ];
    }
    curl_close($ch);
    return $response;
}




// 运行所有的worker

Worker::runAll();
