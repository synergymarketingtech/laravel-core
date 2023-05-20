<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone_number')->nullable()->after('email');
            $table->boolean('is_active')->nullable()->default(true)->after('remember_token');
            $table->string('title')->nullable()->after('id');
            $table->boolean('is_enquiry')->nullable()->default(false)->after('is_active');
            $table->string('status')->nullable()->after('is_active');
            $table->string('gender')->nullable()->after('name');
            $table->string('rag')->nullable()->after('is_active');
            $table->unsignedBigInteger('plan_id')->nullable()->after('status');
            $table->string('username')->nullable()->after('name');

            $table->foreign('plan_id')->references('id')->on('plans')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'phone_number',
                'is_enquiry',
                'status',
                'gender',
                'rag',
            ]);
        });
    }
};
