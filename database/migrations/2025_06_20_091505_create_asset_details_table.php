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
        Schema::create('asset_details', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code');
            $table->string('asset_name');
            $table->string('asset_number');
            $table->date('purchase_date');
            $table->boolean('is_sold')->default(false);
            $table->date('end_economic_life');
            $table->decimal('nominal',20,2);
            $table->string('depreciation_code');
            $table->string('company_code');
            $table->string('department_code');
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
        Schema::dropIfExists('asset_details');
    }
};
