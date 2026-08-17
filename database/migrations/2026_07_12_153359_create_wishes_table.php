<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('message');
            $table->string('moderation_status')->default('approved');
            $table->timestamps();

            $table->index(['wedding_id', 'moderation_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishes');
    }
};
