<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sp_id')->constrained('service_providers')->onDelete('cascade');
            $table->string('title');
            $table->enum('type', ['tour', 'accommodation', 'transport', 'activity', 'other']);
            $table->decimal('amount', 10, 2);
            $table->text('thumbnail')->nullable();
            $table->text('description');
            $table->double('discount_percentage')->nullable();
            $table->dateTime('discount_expires_on')->nullable();
            $table->enum('status_visibility', ['active', 'inactive', 'draft']);
            $table->double('location_latitude');
            $table->double('location_longitude');
            $table->json('availability')->nullable();
            $table->timestamps();

            // Add indexes
            $table->index('sp_id');
            $table->index('type');
            $table->index('status_visibility');
            $table->index('amount');
            $table->index(['location_latitude', 'location_longitude']);
            $table->index('discount_expires_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
}; 