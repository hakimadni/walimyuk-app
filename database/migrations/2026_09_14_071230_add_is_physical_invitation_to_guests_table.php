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
        Schema::table('guests', function (Blueprint $table) {
            $table->boolean('is_physical_invitation')->default(false)->after('is_invitation_sent');
            $table->index(['wedding_id', 'is_physical_invitation']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropIndex(['wedding_id', 'is_physical_invitation']);
            $table->dropColumn('is_physical_invitation');
        });
    }
};
