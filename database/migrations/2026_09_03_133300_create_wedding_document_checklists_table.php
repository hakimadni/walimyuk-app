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
        Schema::create('wedding_document_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->string('stage_key'); // rt_rw, puskesmas, kelurahan, kua_rekomendasi, kua_venue
            $table->string('stage_title');
            $table->string('document_name');
            $table->text('notes')->nullable();
            $table->boolean('is_groom_checked')->default(false);
            $table->boolean('is_bride_checked')->default(false);
            $table->timestamp('groom_checked_at')->nullable();
            $table->timestamp('bride_checked_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['wedding_id', 'stage_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wedding_document_checklists');
    }
};
