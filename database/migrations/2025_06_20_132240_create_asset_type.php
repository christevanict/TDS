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
        Schema::create('asset_type', function (Blueprint $table) {
            $table->id();
            $table->string('asset_type_code');
            $table->string('asset_type_name');
            $table->string('depreciation_code');
            $table->integer('economic_life');
            $table->decimal('tariff_depreciation',20,2);
            $table->string('acc_number_asset');
            $table->string('acc_number_akum_depreciation');
            $table->string('acc_number_depreciation');
            $table->string('company_code');
            $table->string('department_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();

        });

        Schema::table('asset_type', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
            $table->foreign('acc_number_asset')->references('account_number')->on('coa');
            $table->foreign('acc_number_akum_depreciation')->references('account_number')->on('coa');
            $table->foreign('acc_number_depreciation')->references('account_number')->on('coa');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_type');
    }
};
