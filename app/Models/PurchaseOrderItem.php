<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public static function getPriceHistory($item_code = null, $description = null, $unit_no = null)
    {
        $query = self::with(['purchaseOrder', 'purchaseOrder.supplier'])
            ->whereHas('purchaseOrder', function ($q) {
                $q->whereNotNull('doc_num');
            })
            ->orderBy('created_at', 'desc');

        if ($item_code) {
            $query->where('item_code', 'like', '%' . $item_code . '%');
        }

        if ($description) {
            $query->where('description', 'like', '%' . $description . '%');
        }

        if ($unit_no) {
            $query->whereHas('purchaseOrder', function ($q) use ($unit_no) {
                $q->where('unit_no', $unit_no);
            });
        }

        return $query;
    }
}
