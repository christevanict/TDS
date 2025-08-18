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
        Schema::create('void_cheque', function (Blueprint $table) {
            $table->id();
            $table->string('document_number');
            $table->dateTime('document_date');
            $table->string('account_number');
            $table->integer('nominal');
            $table->text('note');
            $table->string('company_code')->unsigned();
            $table->string('department_code');
            $table->timestamps();
        });

        Schema::table('void_cheque', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('void_cheque');
    }
};
