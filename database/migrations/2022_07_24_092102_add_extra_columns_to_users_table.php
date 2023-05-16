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
            $table->string('member_id')->nullable()->after('password');
            $table->boolean('is_enquiry')->nullable()->default(false);
            $table->string('interest', 500)->nullable()->after('password');
            $table->integer('type')->unsigned()->nullable()->after('password');
            $table->string('best_time_contact')->nullable()->after('password');
            $table->mediumText('note')->nullable()->after('password');
            $table->string('status')->nullable()->after('password');
            $table->string('source')->nullable()->after('password');
            $table->string('gender')->nullable()->after('password');
            $table->string('rag')->nullable()->after('password');
            $table->string('referal_code')->nullable()->after('password');
            $table->unsignedBigInteger('plan_id')->nullable()->after('password');
            $table->unsignedBigInteger('collect_id')->nullable()->after('password');
            $table->unsignedBigInteger('admin_id')->nullable()->after('password');
            $table->integer('assign')->unsigned()->nullable()->after('password');
            $table->string('username')->nullable()->after('password');
            $table->dateTime('enq_date')->nullable()->after('password');
            $table->dateTime('status_change_at')->nullable()->after('password');
            $table->boolean('foc')->nullable()->default(false)->after('password');

            $table->foreign('admin_id')->references('id')->on('admins')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('plan_id')->references('id')->on('plans')->cascadeOnUpdate()->nullOnDelete();
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
            $table->dropForeign('users_admin_id_foreign');
            $table->dropForeign('users_plan_id_foreign');
            $table->dropColumn([
                'title',
                'member_id',
                'interest',
                'type',
                'best_time_contact',
                'note',
                'status',
                'source',
                'gender',
                'rag',
                'referal_code',
                'plan_id',
                'collect_id',
                'admin_id',
                'assign',
                'username',
                'enq_date',
                'status_change_at',
                'rec',
                'other_rec',
                'foc',
                'mem_rec',
            ]);
        });
    }
};
