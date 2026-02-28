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
                // if an old default-named index made it into the database we need to
                // remove it before adding a properly named, shorter index.
                // Using dropIndex with the column list will generate the same long
                // identifier Laravel would have used originally, so it will be
                // dropped if present and ignored otherwise.
                $table->dropIndex(['connected_account_id', 'matched_social_id']);

                // add the newer short name; if it's already there this is a no-op
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
                $table->dropIndex('sfc_account_social_id_idx');
                // restore the original index name (long) so the rollback restores
                // the previous state exactly; Laravel will recompute it for us.
                $table->index(['connected_account_id', 'matched_social_id']);
            });
        }
    }
};
