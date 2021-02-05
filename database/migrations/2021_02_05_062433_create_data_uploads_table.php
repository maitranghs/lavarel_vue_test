<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataUploadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('sqlite')->create('data_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('date');
            $table->string('area');
            $table->integer('average_price');
            $table->string('code');
            $table->integer('houses_sold');
            $table->float('no_of_crimes', 8, 1);
            $table->string('borough_flag');
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
        Schema::dropIfExists('data_uploads');
    }
}
