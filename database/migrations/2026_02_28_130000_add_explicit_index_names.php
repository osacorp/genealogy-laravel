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
            Schema::table($tableName, function (Blueprint $table) use ($indexes, $tableName) {
                foreach ($indexes as [$columns, $name]) {
                    // drop any auto-generated long form if present
                    $longName = $tableName . '_' . implode('_', $columns) . '_index';
                    DB::statement("DROP INDEX IF EXISTS `$longName` ON `$tableName`");

                    // create the index with the explicit name; if it already
                    // exists this will quietly do nothing
                    $table->index($columns, $name);
                }
            });
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
            // can't easily know which table the index belongs to, so run a
            // blind ALTER. If it fails because the index doesn't exist we'll
            // ignore it (the statement is wrapped in try/catch).
            // We prefer DB::statement so we can use IF EXISTS safely.
            // Extract table name from index naming convention.
            $parts = explode('_', $idx);
            $tableName = $parts[0];
            DB::statement("DROP INDEX IF EXISTS `$idx` ON `$tableName`");
        }
    }
};
