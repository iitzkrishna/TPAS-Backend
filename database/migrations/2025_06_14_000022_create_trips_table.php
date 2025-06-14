<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tourist_id')->constrained('tourists')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('trip_type', ['solo', 'partner', 'friends', 'family']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });

        Schema::create('trip_destinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->foreignId('district_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('trip_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->foreignId('interest_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trip_interests');
        Schema::dropIfExists('trip_destinations');
        Schema::dropIfExists('trips');
    }
}; 