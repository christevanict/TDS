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
        Schema::create('receivable_list_salesman_detail', function (Blueprint $table) {
            $table->id();
            $table->string("receivable_list_salesman_number");
            $table->string("document_number");
            $table->date("document_date");
            $table->string("customer_code_document");
            $table->decimal("nominal",20,2);
            $table->decimal("paid",20,2);
            $table->decimal("nominal_left",20,2);
            $table->string("company_code");
            $table->string("department_code");
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });

        Schema::table('receivable_list_salesman_detail', function($table) {
            $table->foreign('receivable_list_salesman_number')->references('receivable_list_salesman_number')->on('receivable_list_salesman');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivable_list_salesman_detail');
    }
};
