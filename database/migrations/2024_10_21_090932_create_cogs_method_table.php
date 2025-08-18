<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCogsMethodTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cogs_methods', function (Blueprint $table) {
            $table->id();
            $table->string('cogs_method');
            $table->string('created_by')->nullable(); // Ganti tipe data menjadi string
            $table->string('updated_by')->nullable(); // Ganti tipe data menjadi string
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cogs_methods');
    }
}
