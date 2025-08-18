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
        Schema::create('purchase_requisition', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_requisition_number');
            $table->string('document_date');
            $table->string('ordered_date')->nullable();
            $table->string('department_code');
            $table->decimal('total',20,2);
            $table->string('supplier_code')->nullable();
            $table->string('notes')->nullable();
            $table->string('created_by');
            $table->string('updated_by');
            $table->string('status');
            $table->string('cancel_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requisitions');
    }
};
