<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrelativeStore extends Model
{
    protected $table = 'store_correlative';

    protected $fillable = [
        'idcorrelative',
        'idstore',
        'enabled',        
    ];

    public function correlative()
    {
        return $this->belongsTo(Correlative::class,'idcorrelative','id')->with(['documentsales']);
    } 
}
?>