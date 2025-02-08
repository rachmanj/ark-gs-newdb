<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('doc_num')->nullable(); // PO Number
            $table->date('doc_date')->nullable(); // posting_date
            $table->date('create_date')->nullable(); // create_date
            $table->date('po_delivery_date')->nullable();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->date('po_eta')->nullable();
            $table->string('pr_no')->nullable();
            $table->string('unit_no')->nullable();
            $table->string('po_currency')->nullable();
            $table->decimal('total_po_price', 15, 2)->nullable();
            $table->decimal('po_with_vat', 15, 2)->nullable();
            $table->string('project_code')->nullable();
            $table->string('dept_code')->nullable();
            $table->string('po_status')->nullable();
            $table->string('po_delivery_status')->nullable();
            $table->string('budget_type')->nullable();
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
        Schema::dropIfExists('purchase_orders');
    }
}
