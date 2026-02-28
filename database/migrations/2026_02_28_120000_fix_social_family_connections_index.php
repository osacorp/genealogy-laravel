<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('social_family_connections')) {
            // Ensure we don't attempt to add the index twice, which leads to
            // "duplicate key name" errors when the migration is run more than
            // once against the same database (e.g. migrate:refresh).
            $dbName = DB::getDatabaseName();
            $exists = DB::selectOne(
                'SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
                    WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?',
                [$dbName, 'social_family_connections', 'sfc_account_social_id_idx']
            );

            if (!$exists) {
                try {
                    Schema::table('social_family_connections', function (Blueprint $table) {
                        // the previously-added index already has the correct short name.
                        // dropping it can fail if MySQL is using it for the foreign-key
                        // on connected_account_id, so we simply avoid removing it here.
                        $table->index(
                            ['connected_account_id', 'matched_social_id'],
                            'sfc_account_social_id_idx'
                        );
                    });
                } catch (\Illuminate\Database\QueryException $e) {
                    // ignore duplicate-key errors (1061) which can happen if the
                    // index already exists but we couldn't detect it earlier.
                    if ($e->getCode() !== '1061') {
                        throw $e;
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('social_family_connections')) {
            Schema::table('social_family_connections', function (Blueprint $table) {
                // simply remove the fixed index; the original long-named index
                // cannot be created on MySQL, so we don't attempt to recreate it
                // during rollback. This keeps the rollback safe and idempotent.
                $table->dropIndex('sfc_account_social_id_idx');
            });
        }
    }
};
