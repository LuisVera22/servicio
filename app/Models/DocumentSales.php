<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSales extends Model
{
    protected $table = 'documentsales';

    protected $fillable = [
        'description',
        'code_sunat',
        'enabled',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function correlative()
    {
        return $this->hasMany(Correlative::class,'iddocument_sales','id');
    }
}
?>