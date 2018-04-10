<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhyloTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phylo', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('refseq_id')->unsigned();
            $table->string('samples'); // ex. 1,2,3
            $table->string('method'); // nj or upgma
            $table->string('status'); // RUNNING,FINISHED
            $table->dateTimeTz('submitted_at');
            $table->dateTimeTz('finished_at')->nullable();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('refseq_id')
                ->references('id')
                ->on('sequences')
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
        Schema::dropIfExists('phylo');
    }
}
