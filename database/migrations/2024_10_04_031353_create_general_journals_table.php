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
        Schema::create('general_journal', function (Blueprint $table) {
            $table->id();
            $table->string('general_journal_number');
            $table->dateTime('general_journal_date');
            $table->decimal('nominal_debet',20,2);
            $table->decimal('nominal_credit',20,2);
            $table->text('note')->nullable();
            $table->string('company_code')->unsigned();
            $table->string('department_code');
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
        });

        Schema::table('general_journal', function($table) {
            $table->foreign('company_code')->references('company_code')->on('company');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_journal');
    }
};
