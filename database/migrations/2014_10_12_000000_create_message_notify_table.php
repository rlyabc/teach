<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageNotifyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_notify', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('send_user_id');
            $table->integer('receive_user_id');
            $table->string('send_student_id');
            $table->integer('receive_student_id');
            $table->tinyInteger('status')->default(0);
            $table->text('content');
            $table->text('path');
            $table->integer('pid');
            $table->string('receive_user_type');
            $table->string('send_user_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schools');
    }
}
