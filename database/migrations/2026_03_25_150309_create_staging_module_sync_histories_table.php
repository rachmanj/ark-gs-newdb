<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStagingModuleSyncHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('staging_module_sync_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('run_id');
            $table->string('module', 16);
            $table->string('trigger', 20);
            $table->string('status', 20);
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->text('message')->nullable();
            $table->text('error_detail')->nullable();
            $table->unsignedInteger('imported_count')->nullable();
            $table->unsignedInteger('skipped_duplicate_count')->nullable();
            $table->unsignedInteger('total_from_sap')->nullable();
            $table->date('sap_date_start')->nullable();
            $table->date('sap_date_end')->nullable();
            $table->timestamps();

            $table->index(['run_id', 'module']);
            $table->index(['module', 'started_at']);
            $table->index('started_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('staging_module_sync_histories');
    }
}
