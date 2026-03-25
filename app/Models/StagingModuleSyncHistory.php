<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StagingModuleSyncHistory extends Model
{
    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'sap_date_start' => 'date',
        'sap_date_end' => 'date',
    ];
}
