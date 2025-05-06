<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionPlan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'year',
        'month',
        'product',
        'uom',
        'qty',
        'project',
    ];

    /**
     * Get the project that this production plan belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function projectModel()
    {
        return $this->belongsTo(Project::class, 'project', 'code');
    }

    /**
     * Get month name from month number.
     *
     * @return string
     */
    public function getMonthNameAttribute()
    {
        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];

        return $months[$this->month] ?? 'Unknown';
    }

    /**
     * Get formatted project name.
     *
     * @return string
     */
    public function getProjectNameAttribute()
    {
        $projectModel = $this->projectModel;
        if ($projectModel) {
            return $projectModel->code . ($projectModel->name ? ' - ' . $projectModel->name : '');
        }
        return $this->project;
    }
}
