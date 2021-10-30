<?php

namespace App\Jobs;

use App\Mail\MailVerify;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Email implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $to_email;
    public $user_name;
    public $content;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to_email,$user_name,$content)
    {
        $this->to_email=$to_email;
        $this->user_name=$user_name;
        $this->content=$content;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('email send start:');
        $to_email=$this->to_email;
        $user_name=$this->user_name;
        $content=$this->content;

        $this->InspectionTaskNoticeEmail($to_email,$content,$cc_email='',$user_name);
        Log::info('email notice end:'.$user_name);
    }

    public function InspectionTaskNoticeEmail($to_email,$content,$cc_email='',$user_name=''){
        if(!$to_email){
            throw new \Exception('接收者邮件不能为空');
        }
        if(!$content){
            throw new \Exception('邮件内容不能为空');
        }

        $formData=array(
            'name'=>$user_name,
            'messageLines'=>$content
        );
        $mailsObj=Mail::to($to_email);
        //发送邮件
        if($cc_email){
            $mailsObj=$mailsObj->cc($cc_email);
        }
        $mailsObj->send(new MailVerify($formData));

    }
}
