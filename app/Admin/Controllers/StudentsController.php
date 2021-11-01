<?php

namespace App\Admin\Controllers;

use App\School;
use App\Student;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class StudentsController extends Controller
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
//    public function show($id, Content $content)
//    {
//        return $content
//            ->header('Detail')
//            ->description('description')
//            ->body($this->detail($id));
//    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
//    public function edit($id, Content $content)
//    {
//        return $content
//            ->header('Edit')
//            ->description('description')
//            ->body($this->form()->edit($id));
//    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
//    public function create(Content $content)
//    {
//        return $content
//            ->header('Create')
//            ->description('description')
//            ->body($this->form());
//    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Student);

        $grid->id('Id');
        $grid->name('姓名');
        $grid->school_id('所属学校')->display(function ($value) {
            $res=School::where('id',$value)->first();
            return $res['name'];
        });;;
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

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
//    protected function detail($id)
//    {
//        $show = new Show(Student::findOrFail($id));
//
//        $show->id('Id');
//        $show->name('Name');
//        $show->password('Password');
//        $show->school_id('School id');
//        $show->line_user_id('Line user id');
//        $show->remember_token('Remember token');
//        $show->created_at('Created at');
//        $show->updated_at('Updated at');
//
//        return $show;
//    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
//    protected function form()
//    {
//        $form = new Form(new Student);
//
//        $form->text('name', 'Name');
//        $form->password('password', 'Password');
//        $form->number('school_id', 'School id');
//        $form->text('line_user_id', 'Line user id');
//        $form->text('remember_token', 'Remember token');
//
//        return $form;
//    }
}
