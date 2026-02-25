<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoExclusion extends Model
{
    use HasFactory;

    protected $fillable = ['po_no', 'reason'];
}
