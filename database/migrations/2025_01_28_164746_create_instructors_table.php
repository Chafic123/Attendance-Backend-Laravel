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
        Schema::create('instructors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('department_id');

            $table->foreign('user_id')
            ->references('id')
            ->on('users');

            $table->foreign('department_id')
            ->references('id')
            ->on('departments');
            $table->string('personal_email')->unique()->after('phone_number')->nullable();
            $table->string('phone_number');
            $table->string('image')->nullable(); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructors');
    }
};
