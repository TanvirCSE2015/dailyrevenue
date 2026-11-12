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
        Schema::create('public_notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('body');

            $table->string('notice_type')->default('general');
            $table->boolean('is_important')->default(false);

            $table->date('published_at')->nullable();
            $table->date('expires_at')->nullable();

            $table->string('attachment')->nullable();

            $table->boolean('is_published')->default(false);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['is_published', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_notices');
    }
};
