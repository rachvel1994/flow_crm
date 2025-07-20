<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('color');
            $table->boolean('is_active')->default(true);
            $table->boolean('send_sms')->default(false);
            $table->boolean('send_email')->default(false);
            $table->unsignedInteger('order_column');
            $table->text('message')->nullable();
            $table->timestamps();
            $table->unique(['team_id', 'name']);
        });

        Schema::create('role_task_status_visibility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_status_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('role_task_status_admin_move', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_status_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('role_task_status_can_move_back', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_status_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_statuses');
        Schema::dropIfExists('role_task_status_visibility');
        Schema::dropIfExists('role_task_status_admin_move');
        Schema::dropIfExists('role_task_status_can_move_back');
    }
};
