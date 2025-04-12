<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('course_instructor', function (Blueprint $table) {
            // Remove the 'enrollment-date' column from the 'course_instructor' table
            $table->dropColumn('enrollment-date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_instructor', function (Blueprint $table) {
            $table->date('enrollment-date')->after('course_id');
        });
    }
};
