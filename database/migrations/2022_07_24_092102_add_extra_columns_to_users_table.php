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
            $table->string('title')->nullable()->after('password');
            $table->string('phone_number')->nullable()->after('password');
            $table->boolean('is_enquiry')->nullable()->default(false);
            $table->string('status')->nullable()->after('password');
            $table->string('gender')->nullable()->after('password');
            $table->string('rag')->nullable()->after('password');
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
