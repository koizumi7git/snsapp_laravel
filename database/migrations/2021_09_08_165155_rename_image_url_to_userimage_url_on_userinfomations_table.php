<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameImageUrlToUserimageUrlOnUserinfomationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('userinfomations', function (Blueprint $table) {
            $table->renameColumn('image_url','userimage_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('userinfomations', function (Blueprint $table) {
            $table->renameColumn('userimage_url','image_url');
        });
    }
}
