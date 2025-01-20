<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Powitheta extends Model
{
    use HasFactory;

    protected $table = 'powithetas';

    protected $fillable = [
        'po_no',
        'create_date',
        'posting_date',
        'vendor_code',
        'item_code',
        'description',
        'uom',
        'qty',
        'unit_no',
        'project_code',
        'dept_code',
        'po_currency',
        'unit_price',
        'item_amount',
        'total_po_price',
        'po_with_vat',
        'po_status',
        'po_delivery_status',
        'po_delivery_date',
        'po_eta',
        'remarks',
        'budget_type'
    ];
}
