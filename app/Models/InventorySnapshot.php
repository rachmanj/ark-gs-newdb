<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventorySnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'snapshot_date',
        'status',
        'row_count',
        'total_value',
        'error_message',
        'duration_ms',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'row_count' => 'integer',
        'total_value' => 'decimal:2',
        'duration_ms' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'snapshot_id');
    }
}
