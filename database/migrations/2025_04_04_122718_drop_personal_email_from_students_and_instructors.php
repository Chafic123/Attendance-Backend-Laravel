<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {

        Schema::table('instructors', function (Blueprint $table) {
            $table->dropColumn('personal_email');
        });
    }

    public function down()
    {

        Schema::table('instructors', function (Blueprint $table) {
            $table->string('personal_email')->nullable();
        });
    }
};
