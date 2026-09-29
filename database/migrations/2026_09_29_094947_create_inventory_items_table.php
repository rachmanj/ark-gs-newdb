<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryItemsTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('inventory_snapshots')->cascadeOnDelete();
            $table->string('model_no')->nullable();
            $table->string('unit_no')->nullable();
            $table->string('item_code');
            $table->string('item_name')->nullable();
            $table->string('category');
            $table->string('uom')->nullable();
            $table->decimal('instock', 20, 4)->default(0);
            $table->decimal('committed', 20, 4)->default(0);
            $table->decimal('ordered', 20, 4)->default(0);
            $table->string('currency')->nullable();
            $table->decimal('last_price', 20, 4)->nullable();
            $table->decimal('total_value', 20, 2)->default(0);
            $table->string('whs_code')->nullable();
            $table->string('whs_name')->nullable();
            $table->string('project')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();

            $table->index(['snapshot_id', 'project']);
            $table->index(['snapshot_id', 'category']);
            $table->index(['snapshot_id', 'whs_code']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_items');
    }
}
