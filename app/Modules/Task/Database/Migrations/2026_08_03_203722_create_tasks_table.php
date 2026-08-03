<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('status')->default(1)->index();
            $table->unsignedTinyInteger('priority')->default(2)->index();
            $table->date('due_date')->nullable()->index();
            $table->timestamp('overdue_notified_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('task_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('task_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('activity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_activities');
        Schema::dropIfExists('task_notes');
        Schema::dropIfExists('tasks');
    }
};
