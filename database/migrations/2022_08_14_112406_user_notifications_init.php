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
        $tableName = 'user_notifications';
        Schema::create($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'id')) $table->bigIncrements('id')->index();
            if (!Schema::hasColumn($tableName, 'user_id')) $table->unsignedBigInteger('user_id')->default(0);
            if (!Schema::hasColumn($tableName, 'is_read')) $table->boolean('is_read')->default(false);
            if (!Schema::hasColumn($tableName, 'notification_id')) $table->unsignedBigInteger('notification_id')->default(0);

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
        $tableName = 'user_notifications';
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'id')) $table->dropColumn('id');
            if (!Schema::hasColumn($tableName, 'user_id')) $table->dropColumn('user_id');
            if (!Schema::hasColumn($tableName, 'is_read')) $table->dropColumn('is_read');
            if (!Schema::hasColumn($tableName, 'notification_id')) $table->dropColumn('notification_id');

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
        