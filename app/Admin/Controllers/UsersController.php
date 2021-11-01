<?php

namespace App\Admin\Controllers;

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

class UsersController extends Controller
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
        $grid = new Grid(new User());

        $grid->id('Id')->sortable();
        $grid->name('用户名');
        $grid->email('邮箱');
        $grid->role('Role');
        $grid->email_verify('已验证邮箱')->display(function ($value) {
            return $value ? '是' : '否';
        });

        $grid->school_id('所属学校')->display(function ($value) {
            $res=School::where('id',$value)->first();
            if(!empty($res['name'])){
                return $res['name'];
            }
            return '';

        });;
        $grid->line_user_id('Line user id');

        $grid->created_at('注册时间');
        $grid->updated_at('更新时间');

        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            // 不在每一行后面展示查看按钮
            $actions->disableView();

            // 不在每一行后面展示删除按钮
            $actions->disableDelete();

            // 不在每一行后面展示编辑按钮
            $actions->disableEdit();
        });

        $grid->tools(function ($tools) {

            // 禁用批量删除按钮
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });
        return $grid;
    }
    protected function detail($id)
    {
        $show = new Show(User::findOrFail($id));

        $show->id('Id');
        $show->name('用户名');

        return $show;
    }


    /**
     * Make a form builder.
     *
     * @return Form
     */
//    protected function form()
//    {
//        $form = new Form(new User);
//        $form->switch('is_admin_agree', '是否经过管理员同意');
//
//        return $form;
//    }


}
