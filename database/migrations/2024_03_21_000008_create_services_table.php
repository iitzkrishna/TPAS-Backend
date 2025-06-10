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
            $table->foreignId('partner_id')->constrained('partners')->onDelete('cascade');
            $table->string('title');
            $table->enum('type', ['tour', 'accommodation', 'transport', 'activity', 'other']);
            $table->decimal('amount', 10, 2);
            $table->text('thumbnail')->nullable();
            $table->text('description');
            $table->double('discount_percentage')->nullable();
            $table->dateTime('discount_expires_on')->nullable();
            $table->enum('status_visibility', ['active', 'inactive', 'draft']);
            $table->string('location');
            $table->foreignId('district_id')->constrained('districts', 'district_id');
            $table->json('availability')->nullable();
            $table->timestamps();

            // Add indexes
            $table->index('partner_id');
            $table->index('type');
            $table->index('status_visibility');
            $table->index('amount');
            $table->index('district_id');
            $table->index('location');
            $table->index('discount_expires_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
}; 