<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDefaultParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('default_params', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tools');
            $table->string('param_name');
            $table->string('value_type');
            $table->string('default_value');
            $table->string('value_range')->nullable();
            $table->string('param_description');
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
        Schema::dropIfExists('default_params');
    }
}
