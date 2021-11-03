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
            $table->integer('send_user_id')->nullable($value = true);
            $table->integer('receive_user_id')->nullable($value = true);
            $table->string('send_student_id')->nullable($value = true);
            $table->integer('receive_student_id')->nullable($value = true);
            $table->tinyInteger('status')->default(0);
            $table->text('content')->nullable($value = true);
            $table->text('path')->nullable($value = true);
            $table->integer('pid')->nullable($value = true);
            $table->string('receive_user_type')->nullable($value = true);
            $table->string('send_user_type')->nullable($value = true);
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
