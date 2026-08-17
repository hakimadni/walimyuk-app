<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone_number')->nullable();
            $table->string('group_name')->nullable();
            $table->integer('max_pax')->default(1);
            $table->string('token');
            $table->string('slug')->nullable();
            $table->boolean('is_invitation_sent')->default(false);
            $table->datetime('sent_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['wedding_id', 'token']);
            $table->index(['wedding_id', 'group_name']);
            $table->index(['wedding_id', 'is_invitation_sent']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
