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
        Schema::create('delivery_order', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_order_number');
            $table->date('document_date');
            $table->string('customer_code');
            $table->text('notes')->nullable();
            $table->string('department_code');
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
        Schema::dropIfExists('delivery_order');
    }
};
