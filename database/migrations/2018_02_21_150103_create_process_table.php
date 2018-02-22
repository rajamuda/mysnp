<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('process', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('job_id')->unsigned();
            $table->string('pid')->nullable();
            $table->string('process'); /*mapping,sorting,calling,filtering,annotating*/
            $table->string('status')->default('WAITING'); /*WAITING, RUNNING, FINISHED, CANCELED*/
            $table->string('output')->nullable();
            $table->dateTimeTz('submitted_at');
            $table->dateTimeTz('finished_at')->nullable();

            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('process');
    }
}
