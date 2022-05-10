<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'citizen_id',
        'address_line_one',
        'address_line_two',
        'city',
        'postal_code',
    ];
}
