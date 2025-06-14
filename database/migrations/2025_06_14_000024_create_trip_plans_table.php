<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trip_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->integer('total_days');
            $table->json('stay_points');
            $table->json('itinerary');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trip_plans');
    }
}; 