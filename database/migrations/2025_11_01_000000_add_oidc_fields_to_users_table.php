<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOidcFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('oidc_sub')->nullable()->index()->comment('OIDC Subject ID from provider');
            $table->string('oidc_provider')->nullable()->comment('OIDC Provider name (e.g., authentik)');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['oidc_sub', 'oidc_provider']);
        });
    }
}