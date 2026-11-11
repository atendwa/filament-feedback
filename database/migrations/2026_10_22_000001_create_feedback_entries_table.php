<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_entries', function (Blueprint $table): void {
            $table->id();
            $table->uuid()->unique();
            $table->string('category')->nullable()->index();
            $table->string('title');
            $table->text('message');
            $table->string('status', 20)->default('open')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->boolean('is_anonymous')->default(value: false);
            $table->string('alias', 60);
            $table->string('name')->nullable();
            $table->boolean('wants_replies')->default(value: false);
            $table->text('contact_email')->nullable();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('feedback_comments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('feedback_id')->constrained('feedback_entries')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('is_from_submitter')->default(value: false);
            $table->boolean('is_internal')->default(value: false);
            $table->text('body');
            $table->timestamps();

            $table->index(['feedback_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_comments');
        Schema::dropIfExists('feedback_entries');
    }
};
