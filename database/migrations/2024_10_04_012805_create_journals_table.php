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
        Schema::create('journal', function (Blueprint $table) {
            $table->id();
            $table->string('document_number');
            $table->dateTime('document_date');
            $table->string('account_number')->comment('reference account_number from table purchase_invoice_detail');
            $table->text('notes')->nullable();
            $table->decimal('debet_nominal',20,2);
            $table->decimal('credit_nominal',20,2);
            $table->string('company_code');
            $table->string('department_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });

        Schema::table('journal', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
            $table->foreign('account_number')->references('account_number')->on('coa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
