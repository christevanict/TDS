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
        Schema::create('debt_other_detail', function (Blueprint $table) {
            $table->id();
            $table->string('debt_other_number');
            $table->string('account_number');
            $table->decimal('nominal',20,2);
            $table->text('notes')->nullable();
            $table->string('department_code');
            $table->string('company_code');
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
        Schema::dropIfExists('debt_other_detail');
    }
};
