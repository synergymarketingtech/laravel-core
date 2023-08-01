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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->longText('description')->nullable();
            $table->longText('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_custom')->default(false);
            $table->string('interval')->default('month');
            $table->string('default_interval')->default('month');
            $table->unsignedInteger('interval_count')->default(1);
            $table->decimal('custom_fee', 5, 2)->default(0.00);
            $table->decimal('monthly_fee', 5, 2)->default(0.00);
            $table->decimal('yearly_fee', 5, 2)->default(0.00);
            $table->string('stripe_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('plan_prices', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('plan_id');
            $table->string('stripe_id')->nullable();
            $table->string('interval')->default('month');
            $table->unsignedInteger('interval_count')->default(1);
            $table->decimal('amount', 5, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('plans')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('plan_id');
            $table->string('label');
            $table->string('slug');
            $table->mediumText('description')->nullable();
            $table->integer('value')->unsigned()->default(0);
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('plans')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('plan_prices');
        Schema::dropIfExists('plans');
    }
};
