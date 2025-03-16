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
    Schema::table('attendances', function (Blueprint $table) {
        if (!Schema::hasColumn('attendances', 'attended_at')) {
            $table->timestamp('attended_at')->nullable();
        }
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('attended_at');
        });
    }
};
