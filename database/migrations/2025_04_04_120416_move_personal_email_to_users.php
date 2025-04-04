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
        Schema::table('users', function (Blueprint $table) {
            $table->string('personal_email')->nullable()->unique()->after('email');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('personal_email');
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('personal_email')->nullable()->unique()->after('phone_number');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('personal_email');
        });
    }
};
