<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLockHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lock_histories', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id')->index()->nullable();
            $table->integer('lock_id')->index();
            $table->string('description');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('lock_histories', function (Blueprint $table) {
            $table->foreign('lock_id', 'fk_lock_histories_to_locks')
                ->references('id')
                ->on('locks');

            $table->foreign('user_id', 'fk_lock_histories_to_users')
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
        Schema::dropIfExists('lock_histories');
    }
}
