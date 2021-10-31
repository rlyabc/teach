<?php

namespace App\Http\Controllers;


use App\Follow;
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

class EmailController extends Controller
{
    use AuthenticatesUsers;



    public function emailVerify(Request $request){
        $inputs=$request->input();
        $user_id=$inputs['user_id'];
        User::where('id',$user_id)->update(array(
            'email_verify'=>1
        ));
        return redirect('http://localhost:8080/#/emailVerify');
    }

    public function login(Request $request){
        $inputs=$request->input();
        $email=$inputs['email'];
        $password=$inputs['password'];
//        return Hash::make($password);
        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            $user = $this->guard()->user();

            $this->generateToken($user);
            unset($user->password);
            return response()->json([
                'code'=>200,
                'msg'=>'登录成功',
                'data' => array_merge($user->toArray()),
            ]);
        }else{
            return response()->json([
                'status'=>0,
                'msg'=>'登录失败'
            ]);
        }
    }


    //邀请教师
    public function addTeacher(Request $request){
        try{
            DB::beginTransaction();//开启事务
            $inputs=$request->input();
            $password='123456';
            $email=$inputs['email'];
            $name=$inputs['name'];
            $school_id=$inputs['school_id'];
            $users=User::where('email',$email)->get();
            if(count($users)){
                throw new \Exception('邮箱已经存在');
            }
            $res=User::create(array(
                'email'=>$email,
                'password'=>Hash::make($password),
                'role'=>'teacher',
                'name'=>$name,
                'school_id'=>$school_id
            ));
//            $user->roles()->attach($roleId);

            $user_id=$res->id;
            dispatch(new Email($email,'http://www.myteach.com/emailVerify?user_id='.$user_id,array('sfsfsf')));
            DB::commit();
            return array(
                'code'=>200,
                'msg'=>'已发送验证邮箱'
            );
        }catch (\Exception $exception){
            DB::rollBack();
            return array(
                'code'=>1001,
                'msg'=>$exception->getMessage()
            );
        }
    }

    public function addSchool(Request $request){
        try{
            DB::beginTransaction();//开启事务
            $inputs=$request->input();
            $user=$request->user();
            $name=trim($inputs['name']);
            $school=School::where('name',$name)->get();
            if(count($school)){
                throw new \Exception('学校已经存在');
            }
            School::create(array(
                'user_id'=>$user['id'],
                'name'=>$name
            ));
            DB::commit();
            return array(
                'code'=>200,
                'msg'=>'添加成功'
            );
        }catch (\Exception $exception){
            DB::rollBack();
            return array(
                'code'=>1001,
                'msg'=>$exception->getMessage()
            );
        }
    }

    public function addStudent(Request $request){
        try{
            DB::beginTransaction();//开启事务
            $inputs=$request->input();
            $password='123456';
            $name=$inputs['name'];
            $school_id=$inputs['school_id'];
            $student=Student::where('name',$name)->get();
            if(count($student)){
                throw new \Exception('学生已经存在');
            }
            Student::create(array(
                'password'=>Hash::make($password),
                'name'=>$name,
                'school_id'=>$school_id
            ));
            DB::commit();
            return array(
                'code'=>200,
                'msg'=>'成功'
            );
        }catch (\Exception $exception){
            DB::rollBack();
            return array(
                'code'=>1001,
                'msg'=>$exception->getMessage()
            );
        }
    }

    public function getTeacherData(){
        try{
            $res=User::where('role','teacher')->paginate(10);
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

    public function getSchoolData(Request $request){
        try{
            $user=$request->user();
            $res=School::where('user_id',$user['id'])->paginate(10);
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

    public function getStudentData(Request $request){
        try{
            $user_id=Auth::id();
            $user_type=$request->input('user_type');
            $schoolIds=[];
            if($user_type=='teacher_admin'){
                $schoolRes=School::where('user_id',$user_id)->get();
                $schoolIds=[];
                if(count($schoolRes)){
                    $schoolArr=$schoolRes->toArray();
                    $schoolIds=array_column($schoolArr,'id');
                }
                $res=Student::with('school')
                    ->with(['follow'=>function($query)use($user_id){
                        $query->where('user_id',$user_id);
                    }])
                    ->whereIn('school_id',$schoolIds)
                    ->paginate(10);
            }else{
                $userInfo=User::where('id',$user_id)->first();
                if($userInfo){
                    $schoolIds[]=$userInfo["school_id"];
                }
                $res=Student::with('school')
                    ->whereHas('follow',function ($query)use($user_id){
                        $query->where('user_id',$user_id);
                    })
                    ->whereIn('school_id',$schoolIds)
                    ->paginate(10);

            }

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





    public function getSchoolsByUserId(Request $request){
        try{
            $user=$request->user();
            $res=School::where('user_id',$user['id'])->get();
            return array(
                'code'=>200,
                'message'=>'操作成功',
                'data'=>$res
            );
        }catch (\Exception $exception){
            return array(
                'code'=>1001,
                'msg'=>$exception->getMessage()
            );
        }
    }


    public function getStudentsByUserId(){
        try{
            $id=Auth::id();
            $res=Student::whereHas('school',function ($query)use($id){
                $query->where('user_id',$id);
            })->with('school')->get();
            return array(
                'code'=>200,
                'message'=>'操作成功',
                'data'=>$res
            );
        }catch (\Exception $exception){
            return array(
                'code'=>1001,
                'msg'=>$exception->getMessage()
            );
        }
    }


    public function getTeachersByUserId(){
        try{
            $id=Auth::id();
            $res=User::whereHas('school',function ($query)use($id){
                $query->where('user_id',$id);
            })->with('school')->get();
            return array(
                'code'=>200,
                'message'=>'操作成功',
                'data'=>$res
            );
        }catch (\Exception $exception){
            return array(
                'code'=>1001,
                'msg'=>$exception->getMessage()
            );
        }
    }
    //获得学生所在学校的老师管理员
    public function getTeacherAdminsByStudentId(){
        try{
            $id=Auth::id();
            $schoolRes=School::whereHas('student',function ($query)use($id){
                $query->where('id',$id);
            })->first();
            $res=User::where('id',$schoolRes['user_id'])->get();
            return array(
                'code'=>200,
                'message'=>'操作成功',
                'data'=>$res
            );
        }catch (\Exception $exception){
            return array(
                'code'=>1001,
                'msg'=>$exception->getMessage()
            );
        }
    }

    //获得学生所在学校的所有老师
    public function getTeachersDataByStudentId(){
        try{
            $id=Auth::id();
            $schoolRes=School::whereHas('student',function ($query)use($id){
                $query->where('id',$id);
            })->first();
            $res=User::where(function ($query)use($schoolRes){
                    $query->where('school_id',$schoolRes['id'])
                            ->Orwhere('id',$schoolRes['user_id']);
                    })
                ->with(['follow'=>function($query)use($id){
                    $query->where('student_id',$id);
                }])
                ->paginate(10);
            return array(
                'code'=>200,
                'message'=>'操作成功',
                'data'=>$res
            );
        }catch (\Exception $exception){
            return array(
                'code'=>1001,
                'msg'=>$exception->getMessage()
            );
        }
    }

    public function getFollowDataByUseId(){
        $user_id=Auth::id();
        $data=Follow::with('student')
            ->where('user_id',$user_id)
            ->paginate(10);

        return array(
            'code'=>200,
            'data'=>$data
        );
    }

    public function getFollowDataByStudentId(){
        $user_id=Auth::id();
        $data=Follow::with('user')
            ->where('user_id',$user_id)
            ->paginate(10);
        return array(
            'code'=>200,
            'data'=>$data
        );

    }

    public function follow(Request $request){
        $follow_user_id=$request->input('user_id');
        $status=$request->input('status');
        $user_id=Auth::id();
        $student = Student::where('id',$user_id)->find(1);
        if($status){
            $student->follow()->attach($follow_user_id);
            return array(
                'code'=>200,
                'msg'=>'关注成功'
            );
        }else{
            $student->follow()->detach($follow_user_id);
            return array(
                'code'=>200,
                'msg'=>'取关成功'
            );
        }




    }





}
