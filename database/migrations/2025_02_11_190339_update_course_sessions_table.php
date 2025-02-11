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
        Schema::table('course_sessions', function (Blueprint $table) {
            
            $table->dropForeign(['schedule_id']);
            $table->dropColumn(['schedule_id', 'start_time', 'end_time']);

            $table->date('date')->nullable()->after('course_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_sessions', function (Blueprint $table) {
            
            $table->foreignId('schedule_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->time('start_time');
            $table->time('end_time');

            $table->dropColumn('date');
        });
    }
};
