<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->string('attendance_status');
            $table->integer('pax_count')->default(0);
            $table->string('guest_name_confirmed')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('comment')->nullable();
            $table->datetime('submitted_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->unique('guest_id');
            $table->index(['wedding_id', 'attendance_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsvps');
    }
};
