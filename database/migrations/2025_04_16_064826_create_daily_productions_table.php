<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyProductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_productions', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('project')->nullable();
            $table->decimal('day_shift', 10, 2)->nullable();
            $table->decimal('night_shift', 10, 2)->nullable();
            $table->decimal('mtd_ff_actual', 10, 2)->nullable();
            $table->decimal('mtd_ff_plan', 10, 2)->nullable();
            $table->decimal('mtd_rain_actual', 10, 2)->nullable();
            $table->decimal('mtd_rain_plan', 10, 2)->nullable();
            $table->decimal('mtd_haul_dist_actual', 10, 2)->nullable();
            $table->decimal('mtd_haul_dist_plan', 10, 2)->nullable();
            $table->decimal('limestone_day_shift', 10, 2)->nullable();
            $table->decimal('limestone_swing_shift', 10, 2)->nullable();
            $table->decimal('limestone_night_shift', 10, 2)->nullable();
            $table->decimal('shalestone_day_shift', 10, 2)->nullable();
            $table->decimal('shalestone_swing_shift', 10, 2)->nullable();
            $table->decimal('shalestone_night_shift', 10, 2)->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_productions');
    }
}
