<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Citizen extends Model
{
    use HasFactory;

    protected $fillable = [
        'nic',
        'passport_no',
        'passport_expiry_date',
        'profile_image_path',
        'date_of_birth',
        'mobile',
        'profession',
        'employee_name',
        'user_id',
    ];
}
