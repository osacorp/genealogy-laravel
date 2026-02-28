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
        if (Schema::hasTable('social_family_connections')) {
            Schema::table('social_family_connections', function (Blueprint $table) {
                // the previously-added index already has the correct short name.
                // dropping it can fail if MySQL is using it for the foreign-key
                // on connected_account_id, so we simply avoid removing it here.
                // Laravel will ignore attempts to recreate an existing index, so
                // the call below is safe in all cases.
                $table->index(
                    ['connected_account_id', 'matched_social_id'],
                    'sfc_account_social_id_idx'
                );
            });
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
