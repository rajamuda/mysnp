<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEffToDbSnp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('db_snp', function (Blueprint $table) {
            $table->text('annotation')->after('format')->nullable();
            $table->text('impact')->after('annotation')->nullable();
            $table->text('gene_name')->after('impact')->nullable();
            $table->text('gene_id')->after('gene_name')->nullable();
            $table->text('feature_type')->after('gene_id')->nullable();
            $table->text('feature_id')->after('feature_type')->nullable();
            $table->text('transcript_biotype')->after('feature_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('db_snp', function (Blueprint $table) {
            $table->dropColumn('annotation');
            $table->dropColumn('impact');
            $table->dropColumn('gene_name');
            $table->dropColumn('gene_id');
            $table->dropColumn('feature_type');
            $table->dropColumn('feature_id');
            $table->dropColumn('transcript_biotype');
        });
    }
}
