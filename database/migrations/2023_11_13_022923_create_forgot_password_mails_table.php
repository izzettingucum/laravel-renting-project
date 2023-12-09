<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForgotPasswordMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forgot_password_mails', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->index()->constrained("users")->onDelete("cascade");
            $table->integer("code");
            $table->string("remember_token", 8);
            $table->dateTime("expired_at");
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
        Schema::dropIfExists('forgot_password_mails');
    }
}
