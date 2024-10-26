<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MainWarehouseReception extends Model
{
    protected $table = 'mainwarehousereception';

    protected $fillable = [
        'status',
        'dateshipping',
        'dateforward',
        'dateapproved',
        'date',
        'observation',
        'status_laboratory',
        'status_dispatch',
        'enabled',
        'idquotation',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [        
        'created_at',
        'updated_at'
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class,'idquotation','id')->select(['*']);
    }
}
?>