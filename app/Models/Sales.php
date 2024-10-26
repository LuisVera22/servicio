<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $table = 'sales';

    protected $fillable = [
        'number_document',
        'client',
        'address_client',
        'date_issue',
        'delivery_time',
        'forma_pago',
        'subtotal',
        'igv',
        'total',
        'docserie',
        'serie',
        'numserie',
        'estadofe',
        'observacionfe',
        'correlativebaja',
        'correlativeresumen',
        'tiponota',
        'motivonota',
        'anexado',
        'motivobaja',
        'listquotation',
        'idcorrelative',
        'idtype_document',
        'idvendor',
        'idstore',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function correlative()
    {
        return $this->belongsTo(Correlative::class,'idcorrelative','id')->select(['id','serie','correlative','iddocument_sales'])->with('document_sales');
    }

    public function store()
    {
        return $this->belongsTo(Store::class,'idstore','id')->select(['id','address','idheadquarter'])->with('headquarters');
    }
    public function vendor()
    {
        return $this->belongsTo(Employee::class,'idvendor','id')->select(['id','name','lastname']);
    }
    public function typedocument()
    {
        return $this->belongsTo(TypeDocument::class,'idtype_document','id')->select(['id','description']);
    }
}
?>