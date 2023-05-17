<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = 'users';
        Schema::create($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'id')) $table->bigIncrements('id')->index();
            if (!Schema::hasColumn($tableName, 'name')) $table->string('name');
            if (!Schema::hasColumn($tableName, 'username')) $table->string('username');
            if (!Schema::hasColumn($tableName, 'password')) $table->string('password');
            if (!Schema::hasColumn($tableName, 'email')) $table->string('email');
            if (!Schema::hasColumn($tableName, 'email_verified_at')) $table->dateTime('email_verified_at')->nullable();
            if (!Schema::hasColumn($tableName, 'remember_token')) $table->string('remember_token')->nullable();
            if (!Schema::hasColumn($tableName, 'picture')) $table->text('picture')->nullable();
            if (!Schema::hasColumn($tableName, 'role_id')) $table->unsignedBigInteger('role_id')->index();
            if (!Schema::hasColumn($tableName, 'is_ban')) $table->boolean('is_ban')->default(false);

            if (!Schema::hasColumn($tableName, 'created_by')) $table->unsignedBigInteger('created_by')->nullable();
            if (!Schema::hasColumn($tableName, 'updated_by')) $table->unsignedBigInteger('updated_by')->nullable();
            if (!Schema::hasColumn($tableName, 'deleted_by')) $table->unsignedBigInteger('deleted_by')->nullable();

            if (!Schema::hasColumn($tableName, 'created_ip')) $table->string('created_ip')->nullable();
            if (!Schema::hasColumn($tableName, 'updated_ip')) $table->string('updated_ip')->nullable();
            if (!Schema::hasColumn($tableName, 'deleted_ip')) $table->string('deleted_ip')->nullable();
    
            $table->timestamps(0);
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableName = 'users';
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'id')) $table->dropColumn('id');
            if (!Schema::hasColumn($tableName, 'name')) $table->dropColumn('name');
            if (!Schema::hasColumn($tableName, 'username')) $table->dropColumn('username');
            if (!Schema::hasColumn($tableName, 'password')) $table->dropColumn('password');
            if (!Schema::hasColumn($tableName, 'email')) $table->dropColumn('email');
            if (!Schema::hasColumn($tableName, 'email_verified_at')) $table->dropColumn('email_verified_at');
            if (!Schema::hasColumn($tableName, 'remember_token')) $table->dropColumn('remember_token');
            if (!Schema::hasColumn($tableName, 'picture')) $table->dropColumn('picture');
            if (!Schema::hasColumn($tableName, 'role_id')) $table->dropColumn('role_id');
            if (!Schema::hasColumn($tableName, 'is_ban')) $table->dropColumn('is_ban');

            if (!Schema::hasColumn($tableName, 'created_at')) $table->dropColumn('created_at');
            if (!Schema::hasColumn($tableName, 'updated_at')) $table->dropColumn('updated_at');
            if (!Schema::hasColumn($tableName, 'deleted_at')) $table->dropColumn('deleted_at');

            if (!Schema::hasColumn($tableName, 'created_by')) $table->dropColumn('created_by');
            if (!Schema::hasColumn($tableName, 'updated_by')) $table->dropColumn('updated_by');
            if (!Schema::hasColumn($tableName, 'deleted_by')) $table->dropColumn('deleted_by');

            if (!Schema::hasColumn($tableName, 'created_ip')) $table->dropColumn('created_ip');
            if (!Schema::hasColumn($tableName, 'updated_ip')) $table->dropColumn('updated_ip');
            if (!Schema::hasColumn($tableName, 'deleted_ip')) $table->dropColumn('deleted_ip');
    
        });
    }
};      
        