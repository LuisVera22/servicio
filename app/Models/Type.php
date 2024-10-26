<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $table='type';

    protected $fillable=[
        'description',
        'abbreviation',
        'enabled',
        'idtype_manufacturing',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'idtype_manufacturing',
        'created_at',
        'updated_at'
    ];

    public function typemanufacturing()
    {
        return $this->belongsTo(TypeManufacturing::class,'idtype_manufacturing','id')->select(['id','description']);
    }
}