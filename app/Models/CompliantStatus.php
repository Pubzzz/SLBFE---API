<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompliantStatus extends Model
{
    use HasFactory;

    protected $table = 'compliant_status';

    protected $fillable = [
        'status',
        'comments',
        'compliant_id',
        'user_id',
    ];
}
