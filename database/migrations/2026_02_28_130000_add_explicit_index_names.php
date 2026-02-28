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
        $definitions = [
            'connected_accounts' => [
                [['user_id', 'id'], 'connected_accounts_user_id_id_idx'],
                [['provider', 'provider_id'], 'connected_accounts_provider_provider_id_idx'],
            ],
            'subscriptions' => [
                [['user_id', 'stripe_status'], 'subscriptions_user_id_status_idx'],
            ],
            'smart_matches' => [
                [['user_id', 'status'], 'smart_matches_user_id_status_idx'],
                [['person_id', 'confidence_score'], 'smart_matches_person_id_confidence_idx'],
            ],
            'checklist_template_items' => [
                [['checklist_template_id', 'order'], 'checklist_template_items_template_id_order_idx'],
            ],
            'checklist_templates' => [
                [['category', 'is_public'], 'checklist_templates_category_public_idx'],
                [['is_default', 'is_public'], 'checklist_templates_default_public_idx'],
            ],
            'user_checklists' => [
                [['user_id', 'status'], 'user_checklists_user_status_idx'],
                [['subject_type', 'subject_id'], 'user_checklists_subject_type_id_idx'],
                [['priority', 'due_date'], 'user_checklists_priority_due_date_idx'],
                [['status', 'due_date'], 'user_checklists_status_due_date_idx'],
            ],
            'user_checklist_items' => [
                [['user_checklist_id', 'order'], 'user_checklist_items_checklist_order_idx'],
                [['user_checklist_id', 'is_completed'], 'user_checklist_items_checklist_completed_idx'],
                [['is_completed', 'completed_at'], 'user_checklist_items_completed_date_idx'],
            ],
            'virtual_events' => [
                [['team_id', 'status'], 'virtual_events_team_status_idx'],
                [['start_time', 'end_time'], 'virtual_events_start_end_idx'],
            ],
        ];

        foreach ($definitions as $tableName => $indexes) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            $dbName = DB::getDatabaseName();

            foreach ($indexes as [$columns, $name]) {
                $longName = $tableName . '_' . implode('_', $columns) . '_index';

                // drop the automatically generated long-form index if it exists
                $idxExists = DB::selectOne(
                    'SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
                        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?',
                    [$dbName, $tableName, $longName]
                );
                if ($idxExists) {
                    DB::statement("ALTER TABLE `$tableName` DROP INDEX `$longName`");
                }

                // only add the explicit index if it's not already present
                $nameExists = DB::selectOne(
                    'SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
                        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?',
                    [$dbName, $tableName, $name]
                );
                if (!$nameExists) {
                    try {
                        Schema::table($tableName, function (Blueprint $table) use ($columns, $name) {
                            $table->index($columns, $name);
                        });
                    } catch (\Illuminate\Database\QueryException $e) {
                        if ($e->getCode() !== '1061') {
                            throw $e;
                        }
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
        foreach ([
            'connected_accounts_user_id_id_idx',
            'connected_accounts_provider_provider_id_idx',
            'subscriptions_user_id_status_idx',
            'smart_matches_user_id_status_idx',
            'smart_matches_person_id_confidence_idx',
            'checklist_template_items_template_id_order_idx',
            'checklist_templates_category_public_idx',
            'checklist_templates_default_public_idx',
            'user_checklists_user_status_idx',
            'user_checklists_subject_type_id_idx',
            'user_checklists_priority_due_date_idx',
            'user_checklists_status_due_date_idx',
            'user_checklist_items_checklist_order_idx',
            'user_checklist_items_checklist_completed_idx',
            'user_checklist_items_completed_date_idx',
            'virtual_events_team_status_idx',
            'virtual_events_start_end_idx',
        ] as $idx) {
            // determine table name from index naming convention
            $parts = explode('_', $idx);
            $tableName = $parts[0];

            // check existence via information_schema before trying to drop
            $dbName = DB::getDatabaseName();
            $idxExists = DB::selectOne(
                'SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
                    WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?',
                [$dbName, $tableName, $idx]
            );
            if ($idxExists) {
                DB::statement("ALTER TABLE `$tableName` DROP INDEX `$idx`");
            }
        }
    }
};
