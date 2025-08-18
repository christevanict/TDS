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
        Schema::create('void_cheque_detail', function (Blueprint $table) {
            $table->id();
            $table->string('document_number');
            $table->string('document_payment_number');
            $table->dateTime('document_payment_date');
            $table->string('bg_cheque_number');
            $table->integer('nominal');
            $table->text('note');
            $table->integer('row_number');
            $table->string('company_code')->unsigned();
            $table->string('department_code');
            $table->timestamps();
        });

        Schema::table('void_cheque_detail', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('void_cheque_detail');
    }
};
