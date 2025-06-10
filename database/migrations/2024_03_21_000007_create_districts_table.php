<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id('district_id');
            $table->string('district_name', 50);
            $table->foreignId('province_id')->constrained('provinces', 'province_id');
            $table->timestamps();

            // Add indexes
            $table->index('district_name');
            $table->index('province_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('districts');
    }
}; 