<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class MessageNotify extends Model
{
    use Notifiable;


    protected  $table='message_notify';

    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];


    function send_user()
    {
        return $this->belongsTo('App\User', 'send_user_id', 'id');
    }

    function receive_user()
    {
        return $this->belongsTo('App\User', 'send_user_id', 'id');
    }
}
