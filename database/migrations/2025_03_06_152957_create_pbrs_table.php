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
        Schema::create('pbr', function (Blueprint $table) {
            $table->id();
            $table->string('pbr_number');
            $table->date('document_date');
            $table->string('customer_code');
            $table->decimal('disc_nominal',20,2);
            $table->decimal('subtotal',20,2);
            $table->decimal('add_tax',20,2);
            $table->decimal('tax_revenue',20,2);
            $table->decimal('total',20,2);
            $table->string('status');
            $table->string('tax');
            $table->string('token');
            $table->string('department_code');
            $table->string('company_code');
            $table->decimal('tax_revenue_tariff',20,2);
            $table->text('notes')->nullable();
            $table->string('reason')->nullable();
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pbr');
    }
};
