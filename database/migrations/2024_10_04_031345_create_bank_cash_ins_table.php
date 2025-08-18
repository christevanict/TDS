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
        Schema::create('bank_cash_in', function (Blueprint $table) {
            $table->id();
            $table->string('bank_cash_in_number');
            $table->dateTime('bank_cash_in_date');
            $table->string('account_number');
            $table->decimal('nominal',20,2);
            $table->text('note')->nullable();
            $table->string('company_code')->unsigned();
            $table->string('department_code')->unsigned();
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });

        Schema::table('bank_cash_in', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
            $table->foreign('account_number')->references('account_number')->on('coa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_cash_in');
    }
};
