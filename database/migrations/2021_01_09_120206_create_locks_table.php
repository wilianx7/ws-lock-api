<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('created_by_user_id')->index();
            $table->string('mac_address')->unique();
            $table->string('state', 30)->default('LOCKED');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('locks', function (Blueprint $table) {
            $table->foreign('created_by_user_id', 'fk_locks_to_created_user')
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
        Schema::dropIfExists('locks');
    }
}
