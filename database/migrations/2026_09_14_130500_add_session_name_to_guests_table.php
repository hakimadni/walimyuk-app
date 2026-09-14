<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->string('session_name')->nullable()->after('group_name');
            $table->index(['wedding_id', 'session_name']);
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropIndex(['wedding_id', 'session_name']);
            $table->dropColumn('session_name');
        });
    }
};
