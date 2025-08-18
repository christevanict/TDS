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
        Schema::create('beginning_balance', function (Blueprint $table) {
            $table->id();
            $table->string('account_number');
            $table->decimal('begin_debet_nominal',20,2);
            $table->decimal('begin_credit_nominal',20,2);
            $table->decimal('adjust_debit_nominal',20,2);
            $table->decimal('adjust_credit_nominal',20,2);
            $table->decimal('ending_debet_balance',20,2);
            $table->decimal('ending_credit_balance',20,2);
            $table->string('periode');
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
        Schema::dropIfExists('beginning_balances');
    }
};
