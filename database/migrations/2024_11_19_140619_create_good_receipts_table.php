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
        Schema::create('good_receipt', function (Blueprint $table) {
            $table->id();
            $table->string('good_receipt_number');
            $table->date('document_date');
            $table->string('supplier_code');
            $table->string('status');
            $table->text('notes');
            $table->string('warehouse_code');
            $table->string('department_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->string('cancel_notes')->nullable();
            $table->timestamps();
        });

        Schema::table('good_receipt', function($table) {
            $table->foreign('supplier_code')->references('supplier_code')->on('supplier')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('good_receipts');
    }
};
