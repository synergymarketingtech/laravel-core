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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->boolean('has_size')->default(false);
            $table->boolean('is_active')->default(false);
            $table->string('status')->default('Active');
            $table->decimal('price', 10, 2)->nullable()->default(0.00);
            $table->{$this->jsonable()}('sizes')->nullable();

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
        Schema::dropIfExists('products');
    }
};
