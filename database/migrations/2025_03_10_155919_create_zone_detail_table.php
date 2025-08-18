<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('zone_detail', function (Blueprint $table) {
            $table->id();
            $table->string("zone_code");
            $table->string("city_code");
            $table->string("created_by")->nullable();
            $table->string("updated_by")->nullable();
            $table->timestamps();
        });

        Schema::table('zone_detail', function($table) {
            $table->foreign('city_code')->references('city_code')->on('city');
            $table->foreign('zone_code')->references('zone_code')->on('zone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zone_detail');
    }
};
