<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable,HasApiTokens;



//    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','role','school_id', 'line_user_id','is_admin_agress'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    public function findForPassport($login)
    {
        return $this->where('email', $login)->where('email_verify', 1)->first();
    }


    function school()
    {
        return $this->belongsTo('App\School', 'school_id', 'id');
    }

    function follow()
    {
        return $this->belongsToMany('App\Student', 'follows', 'user_id','student_id');
    }
}
