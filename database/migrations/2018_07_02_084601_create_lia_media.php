<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLiaMedia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lia_media', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('relate_id')->default('0');
            $table->string('type')->default('img');
            $table->string('preview')->nullable();
            $table->text('data');
            $table->string('title');
            $table->text('description')->nullable();
            foreach(config('lia-media.markers') as $key => $data)
                $table->{$data[0]}($key)->default(isset($data['form']['default']) ? $data['form']['default'] : NULL);
            $table->integer('active')->default(1);
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
        Schema::dropIfExists('lia_media');
    }
}
