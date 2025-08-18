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
        Schema::create('coa_type', function (Blueprint $table) {
            $table->id();
            $table->text('account_sub_type');
            $table->string('account_type');
            $table->string('company_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });
        Schema::table('coa_type', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coa_type');
    }
};
