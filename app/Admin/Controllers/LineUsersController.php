<?php

namespace App\Admin\Controllers;

use App\LineMessageUser;
use App\School;
use App\User;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LineUsersController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }



    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new LineMessageUser());

        $grid->id('Id')->sortable();
        $grid->name('line名称');
        $grid->message_user_id('messageUserId');

        $grid->created_at('注册时间');
        $grid->updated_at('更新时间');

        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            // 不在每一行后面展示查看按钮
            $actions->disableView();

            // 不在每一行后面展示删除按钮
            $actions->disableDelete();

            // 不在每一行后面展示编辑按钮
//            $actions->disableEdit();
        });

        $grid->tools(function ($tools) {

            // 禁用批量删除按钮
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });
        return $grid;
    }



    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new School);

        $form->text('contents', '内容');
        return $form;
    }

    public function update($id,Request $request,Content $content){
        $contents=$request->input('contents');
        $users=LineMessageUser::where('id',$id)->first();
        $this->sendMessageToLineUser($users['message_user_id'],$contents);
        $contents=array_merge(json_decode($users,true),$contents);
        LineMessageUser::where('id',$id)->update(array(
            'contents'=>json_encode($contents,JSON_UNESCAPED_UNICODE)
        ));
        return redirect()->back();
    }

    public function sendMessageToLineUser($userId,$content){
        $url='https://api.line.me/v2/bot/message/multicast';
        $params=[
            "to"=> [$userId],
            "messages"=>[
                array(
                    'type'=>'text',
                    'text'=>$content
                )
            ]
        ];
        $messageAccessToken='4haMb+fjavg5PA+9fBHOxqrEFVLTzhKEL6bX3BxdyPPvH/lVUuNP3KAkDQDF70LECwjRwgeQHpB4vl/W7i9YiC92idVKSxmQJm/rVGYm6qz24OQIK5qvsS+k3VlrFdTXgqKDlRQWGzAuLbwqfrlvmAdB04t89/1O/w1cDnyilFU=';
        $header=[
            'Content-Type:application/json',
            'Authorization: Bearer '.$messageAccessToken
        ];
        $params=json_encode($params);
       return $this->curl($url,$params,1,1,$header);
    }

    public function curl($url, $params = false, $ispost = 0, $https = 0,$header=[])
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


}
