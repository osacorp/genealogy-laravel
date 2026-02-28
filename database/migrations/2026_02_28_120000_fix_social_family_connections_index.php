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
                // drop the short index if it exists; this avoids ever mentioning the
                // long, auto-generated name which MySQL refuses to parse.
                // We use the explicit name so Laravel doesn't build the default one.
                $table->dropIndex('sfc_account_social_id_idx');

                // now ensure the properly named, shorter index is present. If it
                // already exists the builder will ignore the second creation.
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
