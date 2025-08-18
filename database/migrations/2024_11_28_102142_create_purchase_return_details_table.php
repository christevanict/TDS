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
        Schema::create('purchase_return_detail', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_return_number');
            $table->string('item_id');
            $table->decimal('qty',20,2);
            $table->string('unit');
            $table->float('price');
            $table->float('disc_percent');
            $table->decimal('disc_nominal',20,2);
            $table->decimal('disc_header',20,2)->nullable();
            $table->decimal('nominal',20,2);
            $table->decimal('base_qty',20,2);
            $table->string('base_unit');
            $table->string('description')->nullable();
            $table->decimal('qty_left',20,2);
            $table->decimal('base_qty_left',20,2);
            $table->string('account_number')->comment('DB')->nullable();
            $table->string('company_code')->unsigned();
            $table->string('department_code');
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
        Schema::dropIfExists('purchase_return_detail');
    }
};
