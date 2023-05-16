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
        Schema::create('plan_usages', function (Blueprint $table) {
            $table->id();

            $table->string('slug');
            $table->integer('used')->unsigned()->default(0);
            $table->unsignedBigInteger('subscription_id');
            $table->dateTime('reset_at')->nullable();

            $table->unique(['slug', 'subscription_id']);
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_usages');
    }
};
