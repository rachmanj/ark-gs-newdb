<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddStagingDedupeIndexesToGrpoMigiIncoming extends Migration
{
    public function up()
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            Schema::table('grpos', function (Blueprint $table) {
                $table->index(['grpo_no', 'item_code', 'po_no'], 'grpos_dedupe_key_idx');
            });
            Schema::table('migis', function (Blueprint $table) {
                $table->index(['posting_date', 'doc_type', 'doc_no', 'item_code', 'project_code', 'dept_code'], 'migis_dedupe_key_idx');
            });
            Schema::table('incomings', function (Blueprint $table) {
                $table->index(['posting_date', 'doc_type', 'doc_no', 'item_code', 'project_code', 'dept_code'], 'incomings_dedupe_key_idx');
            });

            return;
        }

        foreach (['grpos' => 'grpos_dedupe_key_idx', 'migis' => 'migis_dedupe_key_idx', 'incomings' => 'incomings_dedupe_key_idx'] as $table => $idx) {
            try {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$idx}`");
            } catch (\Throwable $e) {
                //
            }
        }

        DB::statement('ALTER TABLE grpos ADD INDEX grpos_dedupe_key_idx (grpo_no(64), item_code(64), po_no(64))');
        DB::statement('ALTER TABLE migis ADD INDEX migis_dedupe_key_idx (posting_date, doc_type(48), doc_no(48), item_code(48), project_code(32), dept_code(32))');
        DB::statement('ALTER TABLE incomings ADD INDEX incomings_dedupe_key_idx (posting_date, doc_type(48), doc_no(48), item_code(48), project_code(32), dept_code(32))');
    }

    public function down()
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE grpos DROP INDEX grpos_dedupe_key_idx');
            DB::statement('ALTER TABLE migis DROP INDEX migis_dedupe_key_idx');
            DB::statement('ALTER TABLE incomings DROP INDEX incomings_dedupe_key_idx');

            return;
        }

        Schema::table('grpos', function (Blueprint $table) {
            $table->dropIndex('grpos_dedupe_key_idx');
        });
        Schema::table('migis', function (Blueprint $table) {
            $table->dropIndex('migis_dedupe_key_idx');
        });
        Schema::table('incomings', function (Blueprint $table) {
            $table->dropIndex('incomings_dedupe_key_idx');
        });
    }
}
