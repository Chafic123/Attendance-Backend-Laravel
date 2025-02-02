<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStudentIdToStudentsTable extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('student_id', 10)->unique()->after('id');
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('student_id');
        });
    }
}
