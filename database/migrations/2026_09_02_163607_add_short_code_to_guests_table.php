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
            $table->string('short_code', 16)->nullable()->unique()->after('token');
        });

        // Backfill existing guests with unique short codes
        \App\Models\Guest::withTrashed()->chunkById(100, function ($guests) {
            foreach ($guests as $guest) {
                if (empty($guest->short_code)) {
                    $guest->updateQuietly([
                        'short_code' => \App\Models\Guest::generateUniqueShortCode(),
                    ]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn('short_code');
        });
    }
};
