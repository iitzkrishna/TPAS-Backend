<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('places', function (Blueprint $table) {
            $table->id('place_id');
            $table->string('place_name', 100);
            $table->foreignId('district_id')->constrained('districts', 'district_id');
            $table->timestamps();

            // Add indexes
            $table->index('place_name');
            $table->index('district_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('places');
    }
}; 