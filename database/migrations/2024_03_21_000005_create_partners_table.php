<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('business_name');
            $table->string('business_registration_number')->unique();
            $table->text('business_address');
            $table->string('business_phone');
            $table->string('business_email')->unique();
            $table->string('business_website')->nullable();
            $table->text('business_description')->nullable();
            $table->string('business_logo')->nullable();
            $table->enum('status', ['pending', 'active', 'suspended', 'rejected'])->default('pending');
            $table->timestamps();

            // Add indexes
            $table->index('user_id');
            $table->index('business_name');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
}; 