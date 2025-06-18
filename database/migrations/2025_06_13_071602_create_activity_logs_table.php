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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->nullable()->constrained('tasks')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('chief_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('officer_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->date('assigned_at_old')->nullable();
            $table->date('assigned_at')->nullable();
            $table->boolean('is_done_old')->default(false);
            $table->boolean('is_done')->default(false);
            $table->string('odts_code_old')->nullable();
            $table->string('odts_code')->nullable();
            $table->string('task_name')->nullable();
            $table->string('admin_name')->nullable();
            $table->string('chief_name')->nullable();
            $table->string('officer_name')->nullable();
            $table->string('activity');
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
