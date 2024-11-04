<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCash extends Model
{
    use HasFactory;
    protected $table = 'pettycash';

    protected $fillable = [
        'id',
        'date',
        'time',
        'description',
        'amount',
        'username',
        'img_petty_cash_name',
        'created_at',
        'updated_at',
    ];
}
