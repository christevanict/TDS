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
        Schema::create('reimburse', function (Blueprint $table) {
            $table->id();
            $table->string('reimburse_number');
            $table->timestamp('document_date');
            $table->timestamp('due_date');
            $table->string('contract_document_number');
            $table->decimal('total', 20, 2);
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
        Schema::dropIfExists('reimburse');
    }
};
