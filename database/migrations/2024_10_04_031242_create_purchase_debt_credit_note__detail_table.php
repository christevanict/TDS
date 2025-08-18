<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_debt_credit_note_details', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_credit_note_number');
            $table->string('account_number');
            $table->decimal('nominal',20,2);
            $table->text('note');
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_debt_credit_notes');
    }
};
