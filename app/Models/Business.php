<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $table = 'business';

    protected $fillable = [
        'razon_social',
        'ruc',
        'address',
        'fiscal_address',
        'email',
        'img_logo_empresa_name',
        'created_at',
        'updated_at'
    ];
}
?>