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
        Schema::create('point_of_sales', function (Blueprint $table) {
            $table->id();
            $table->string('pos_number');
            $table->string('customer_id')->nullable(); // Tidak langsung menambahkan constraint
            $table->date('transaction_date');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('discount', 10, 2);
            $table->decimal('final_amount', 10, 2);
            $table->string('payment_method');
            $table->decimal('cash_received', 10, 2)->nullable();
            $table->decimal('change', 10, 2)->nullable();
            $table->string('voucher_code')->nullable();
            $table->string('created_by')->constrained('users');
            $table->string('department_code');
            $table->timestamps();
        });

       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_of_sales');
    }
};
