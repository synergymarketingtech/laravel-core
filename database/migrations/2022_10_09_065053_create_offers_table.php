<?php

use Coderstm\Models\Offer;
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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('tag_line')->nullable();
            $table->string('title_line_1')->nullable();
            $table->string('title_line_2')->nullable();
            $table->integer('order')->unsigned()->default(0);
            $table->boolean('is_active')->nullable()->default(true);
            $table->string('button')->nullable();
            $table->string('link')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Offer::create([
            'tag_line' => 'The Best Fitness Studio.',
            'title_line_1' => 'Best Top-Notch Gym',
            'title_line_2' => 'Health Fitness Services',
            'button' => 'OUR OFFERS',
            'link' => 'javascript:void(0);',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offers');
    }
};
