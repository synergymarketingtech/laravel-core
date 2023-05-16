<?php

use Coderstm\Core\Traits\Helpers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use Helpers;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instructors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->longText('description')->nullable();
            $table->{$this->jsonable()}('urls')->nullable();
            $table->string('onlinefolder')->nullable();
            $table->boolean('insurance')->nullable()->default(false);
            $table->boolean('qualification')->nullable()->default(false);
            $table->boolean('is_pt')->nullable()->default(false);
            $table->decimal('hourspw', 5, 2)->nullable()->default(0.00);
            $table->decimal('rentpw', 5, 2)->nullable()->default(0.00);
            $table->enum('status', ['Active', 'Deactive', 'Hold'])->nullable()->default('Active');
            $table->timestamps();
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
        Schema::dropIfExists('instructors');
    }
};
