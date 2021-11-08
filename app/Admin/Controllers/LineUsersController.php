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
        $form = new Form(new LineMessageUser);

        $form->text('contents', '发送消息内容');
        return $form;
    }

    public function update($id,Request $request,Content $content){
        $contents=$request->input('contents');
        $users=LineMessageUser::where('id',$id)->first();
        $this->sendMessageToLineUser($users['message_user_id'],$contents);
        $existContent=json_decode($users['contents'],true);
        if($existContent){
            $contents=array_merge(json_decode($users['contents'],true),array($contents));
        }else{
            $contents=array($contents);
        }
        
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
       return curl($url,$params,1,1,$header);
    }



}
