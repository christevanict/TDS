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
        Schema::create('item_purchase', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->nullable();
            $table->string('item_code');
            $table->decimal('purchase_price');
            $table->string('unit');
            $table->string('supplier');
            $table->string('company_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });

        Schema::table('item_purchase', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
            $table->foreign('supplier')->references('supplier_code')->on('supplier')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_purchase');
    }
};
