<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGoogleTwoFaSecretToAdminUsersTable extends Migration
{
    public function config($key)
    {
        return config('admin.' . $key);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->config('database.users_table'), function (Blueprint $table) {
            if (Schema::hasColumns($this->config('database.users_table'), ['remember_token'])) {
                $table->string('google_2fa_secret', 32)->default('')->after('remember_token');
            } else {
                $table->string('google_2fa_secret', 32)->default('');
            }
            $table->string('last_session')->default('')->after('google_2fa_secret')->comment('最后登陆token');
            $table->boolean('status')->default(1)->after('last_session');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->config('database.users_table'), function (Blueprint $table) {
            $table->dropColumn('google_2fa_secret');
            $table->dropColumn('last_session');
            $table->dropColumn('status');
        });
    }
}
