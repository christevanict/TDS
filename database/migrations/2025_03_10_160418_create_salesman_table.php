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
        Schema::create('salesman', function (Blueprint $table) {
            $table->id();
            $table->string("salesman_code")->unique();
            $table->string("salesman_name");
            $table->integer('is_active')->commnet('0:Inactive; 1:Active;');
            $table->string('zone_code')->nullable();
            $table->string("created_by")->nullable();
            $table->string("updated_by")->nullable();
            $table->timestamps();
        });

        Schema::table('salesman', function($table) {
            $table->foreign('zone_code')->references('zone_code')->on('zone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salesman');
    }
};
