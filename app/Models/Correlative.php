<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Correlative extends Model
{
    protected $table = 'correlative';

    protected $fillable = [
        'serie',
        'correlative',
        'enabled',
        'iddocument_sales',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'iddocument_sales'
    ];

    public function documentsales()
    {
        return $this->belongsTo(DocumentSales::class,'iddocument_sales','id')->select(['id','description']);
    }
}
?>