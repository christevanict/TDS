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
        Schema::create('purchase_return', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_return_number');
            $table->dateTime('document_date');
            $table->string('supplier_code');
            $table->string('purchase_invoice_number');
            $table->string('tax');
            $table->boolean('include');
            $table->decimal('subtotal',20,2);
            $table->decimal('disc_percent',20,2)->nullable();
            $table->decimal('disc_nominal',20,2);
            $table->decimal('tax_revenue',20,2)->nullable();
            $table->decimal('add_tax',20,2);
            $table->decimal('total',20,2);
            $table->text('notes')->nullable();
            $table->string('account_number')->comment('CR')->nullable();
            $table->string('company_code')->nullable();
            $table->string('department_code');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_return');
    }
};
