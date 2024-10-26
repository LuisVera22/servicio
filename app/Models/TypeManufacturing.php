<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeManufacturing extends Model
{
    protected $table = 'typemanufacturing';

    protected $fillable = [
        'description',
        'abbreviation',
        'job',
        'enabled',
        'created_at',
        'updated_at'
    ];
}
?>