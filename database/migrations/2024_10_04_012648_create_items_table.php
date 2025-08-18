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
        Schema::create('item', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->string('item_category')->unsigned();
            $table->string('base_unit')->unsigned();
            $table->string('sales_unit')->unsigned();
            $table->string('purchase_unit')->unsigned();
            $table->boolean('additional_tax');
            $table->boolean('include')->nullable();
            $table->string('company_code')->unsigned();
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });
        Schema::table('item', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item');
    }
};
