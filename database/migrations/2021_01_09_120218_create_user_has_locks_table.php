<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserHasLocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_has_locks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('lock_id')->index();
            $table->integer('user_id')->index();
        });

        Schema::table('user_has_locks', function (Blueprint $table) {
            $table->foreign('lock_id', 'fk_user_has_locks_to_locks')
                ->references('id')
                ->on('locks');

            $table->foreign('user_id', 'fk_user_has_locks_to_users')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_has_locks');
    }
}
