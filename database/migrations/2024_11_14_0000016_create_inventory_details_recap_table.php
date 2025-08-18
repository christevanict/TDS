<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryDetailsRecapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_detail_recap', function (Blueprint $table) {
            $table->id();
            $table->string('document_number');
            $table->date('document_date');
            $table->string('transaction_type');
            $table->string('from_to');
            $table->string('item_id');
            $table->decimal('quantity', 15, 2);
            $table->string('unit');
            $table->decimal('base_quantity', 15, 2);
            $table->string('unit_base');
            $table->string('department_code');
            $table->string('company_code');
            $table->decimal('first_qty')->nullable();
            $table->decimal('last_qty')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->decimal('total',20,2)->nullable();
            $table->decimal('cogs',20,2)->nullable();
            $table->decimal('qty_actual',20,2)->nullable();
            $table->timestamps();
        });

        Schema::table('inventory_detail_recap', function($table) {
            $table->foreign('warehouse_id')->references('id')->on('warehouse');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_detail_recap');
    }
}
