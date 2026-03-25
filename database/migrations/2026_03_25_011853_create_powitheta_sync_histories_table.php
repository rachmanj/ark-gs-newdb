<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePowithetaSyncHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('powitheta_sync_histories', function (Blueprint $table) {
            $table->id();
            $table->string('trigger', 20);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20);
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->text('message')->nullable();
            $table->text('error_detail')->nullable();
            $table->unsignedInteger('imported_count')->nullable();
            $table->unsignedInteger('total_records')->nullable();
            $table->boolean('convert_success')->nullable();
            $table->text('convert_message')->nullable();
            $table->date('sap_date_start')->nullable();
            $table->date('sap_date_end')->nullable();
            $table->timestamps();

            $table->index(['trigger', 'started_at']);
            $table->index('started_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('powitheta_sync_histories');
    }
}
