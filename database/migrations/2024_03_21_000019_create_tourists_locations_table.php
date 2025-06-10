<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tourists_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tourist_id')->constrained('tourists')->onDelete('cascade');
            $table->double('latitude');
            $table->double('longtitude');
            $table->enum('is_help_needed', ['yes', 'no'])->default('no');
            $table->timestamps();

            // Add indexes
            $table->index('tourist_id');
            $table->index(['latitude', 'longtitude']);
            $table->index('is_help_needed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tourists_locations');
    }
}; 