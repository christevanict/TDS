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
        Schema::create('sales_debt_credit_note_details', function (Blueprint $table) {
            $table->id();
            $table->string('sales_credit_note_number');
            $table->string('account_number');
            $table->decimal('nominal',20,2);
            $table->text('note');
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
        Schema::dropIfExists('sales_debt_credit_note_details');
    }
};
