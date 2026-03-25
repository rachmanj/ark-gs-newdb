<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexToPurchaseOrdersDocNumTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $rows = DB::table('purchase_orders')
            ->whereNotNull('doc_num')
            ->where('doc_num', '!=', '')
            ->orderBy('doc_num')
            ->orderBy('id')
            ->get(['id', 'doc_num']);

        $grouped = collect($rows)->groupBy('doc_num');

        foreach ($grouped as $orders) {
            if ($orders->count() <= 1) {
                continue;
            }

            $ids = $orders->sortBy('id')->pluck('id')->values();
            $keepId = $ids->first();
            $deleteIds = $ids->slice(1)->all();

            if (count($deleteIds) === 0) {
                continue;
            }

            DB::table('purchase_order_items')->whereIn('purchase_order_id', $deleteIds)->delete();
            DB::table('purchase_orders')->whereIn('id', $deleteIds)->delete();
        }

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unique('doc_num', 'purchase_orders_doc_num_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropUnique('purchase_orders_doc_num_unique');
        });
    }
}
