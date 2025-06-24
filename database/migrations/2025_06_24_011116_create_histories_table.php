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
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->nullable()->constrained('tasks')->onDelete('cascade');
            $table->foreignId('officer_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('old_odts_code')->nullable();
            $table->date('old_assigned_at')->nullable();
            $table->string('odts_code');
            $table->date('assigned_at');
            $table->string('activity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
