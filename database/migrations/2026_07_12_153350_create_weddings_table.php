<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('status')->default('draft');
            $table->datetime('wedding_date');
            $table->string('timezone')->default('Asia/Jakarta');
            $table->string('cover_title')->nullable();
            $table->string('cover_subtitle')->nullable();
            $table->text('welcome_text')->nullable();
            $table->text('closing_text')->nullable();
            $table->json('theme_config')->nullable();
            $table->boolean('rsvp_required')->default(true);
            $table->boolean('comments_need_approval')->default(false);
            $table->integer('pax_buffer_percentage')->default(10);
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weddings');
    }
};
