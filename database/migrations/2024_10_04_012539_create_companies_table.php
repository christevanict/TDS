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
        Schema::create('company', function (Blueprint $table) {
            $table->id();
            $table->string('company_code')->unique();
            $table->string('company_name');
            $table->text('address');
            $table->string('phone_number');
            $table->string('npwp');
            $table->boolean('pkp');
            $table->boolean('final_tax');
            $table->string('type_company')->unsigned();
            $table->string('cogs_method')->nullable();
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });
        Schema::table('company', function ($table) {
            $table->foreign('type_company')->references('type_company')->on('type_company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company');
    }
};
