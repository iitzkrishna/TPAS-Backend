<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_booking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->foreignId('tourist_id')->constrained('tourists')->onDelete('cascade');
            $table->enum('request', ['pending', 'approved', 'rejected', 'completed', 'cancelled']);
            $table->dateTime('pref_start_date');
            $table->dateTime('pref_end_date');
            $table->integer('adults')->default(1);
            $table->integer('childrens')->default(0);
            $table->decimal('total_charge', 10, 2);
            $table->timestamps();

            // Add indexes
            $table->index('service_id');
            $table->index('tourist_id');
            $table->index('request');
            $table->index(['pref_start_date', 'pref_end_date']);
            $table->index('total_charge');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_booking');
    }
}; 