<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyProduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'project',
        'day_shift',
        'night_shift',
        'mtd_ff_actual',
        'mtd_ff_plan',
        'mtd_rain_actual',
        'mtd_rain_plan',
        'mtd_haul_dist_actual',
        'mtd_haul_dist_plan',
        'limestone_day_shift',
        'limestone_swing_shift',
        'limestone_night_shift',
        'shalestone_day_shift',
        'shalestone_swing_shift',
        'shalestone_night_shift',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date:d-M-Y',
        'day_shift' => 'integer',
        'night_shift' => 'integer',
        'mtd_ff_actual' => 'integer',
        'mtd_ff_plan' => 'integer',
        'mtd_rain_actual' => 'integer',
        'mtd_rain_plan' => 'integer',
        'mtd_haul_dist_actual' => 'integer',
        'mtd_haul_dist_plan' => 'integer',
        'limestone_day_shift' => 'integer',
        'limestone_swing_shift' => 'integer',
        'limestone_night_shift' => 'integer',
        'shalestone_day_shift' => 'integer',
        'shalestone_swing_shift' => 'integer',
        'shalestone_night_shift' => 'integer',
        'created_by' => 'integer',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
