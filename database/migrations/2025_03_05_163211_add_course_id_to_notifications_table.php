<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')->after('instructor_id')->nullable();
        });

        $defaultCourseId = 1; 
        DB::table('notifications')->update(['course_id' => $defaultCourseId]);

        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')->nullable(false)->change();
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropColumn('course_id');
        });
    }
};
