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
        $tableName = 'notifications';
        Schema::create($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'id')) $table->bigIncrements('id')->index();
            if (!Schema::hasColumn($tableName, 'title')) $table->string('title')->index();
            if (!Schema::hasColumn($tableName, 'content')) $table->longtext('content')->nullable();
            if (!Schema::hasColumn($tableName, 'type')) $table->enum('type', ['system','direct'])->default('system');
            if (!Schema::hasColumn($tableName, 'category')) $table->string('category')->nullable();
            if (!Schema::hasColumn($tableName, 'link_source')) $table->enum('link_source', ['frontend','api','external'])->default('frontend');
            if (!Schema::hasColumn($tableName, 'link_url')) $table->string('link_url')->nullable();
            if (!Schema::hasColumn($tableName, 'date')) $table->dateTime('date');

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
        $tableName = 'notifications';
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'id')) $table->dropColumn('id');
            if (!Schema::hasColumn($tableName, 'title')) $table->dropColumn('title');
            if (!Schema::hasColumn($tableName, 'content')) $table->dropColumn('content');
            if (!Schema::hasColumn($tableName, 'type')) $table->dropColumn('type');
            if (!Schema::hasColumn($tableName, 'category')) $table->dropColumn('category');
            if (!Schema::hasColumn($tableName, 'link_source')) $table->dropColumn('link_source');
            if (!Schema::hasColumn($tableName, 'link_url')) $table->dropColumn('link_url');
            if (!Schema::hasColumn($tableName, 'date')) $table->dropColumn('date');

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
        