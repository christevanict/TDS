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
        Schema::create('asset_sales', function (Blueprint $table) {
            $table->id();
            $table->string('asset_sales_number');
            $table->string('asset_number');
            $table->string('customer_code');
            $table->date('document_date');
            $table->date('due_date');
            $table->string('depreciation_code');
            $table->decimal('subtotal',20,2);
            $table->decimal('add_tax',20,2);
            $table->decimal('nominal',20,2);
            $table->decimal('accum_value',20,2);
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_sales');
    }
};
