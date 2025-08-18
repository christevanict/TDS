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
        Schema::create('clearing_cheque', function (Blueprint $table) {
            $table->id();
            $table->string('document_number');
            $table->dateTime('document_date');
            $table->string('account_number');
            $table->integer('nominal');
            $table->text('note');
            $table->string('company_code')->unsigned();
            $table->string('department_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });

        Schema::table('clearing_cheque', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clearing_cheque');
    }
};
