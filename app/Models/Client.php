<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'client';

    protected $fillable = [
        'codigo',
        'number_document',
        'names',
        'lastnames',
        'bussinesnames',
        'tradename',
        'address',
        'address_fiscal',
        'email',
        'cell_phone',
        'enabled',
        'idtype_document',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'idtype_document'
    ];

    public function typedocument()
    {
        return $this->belongsTo(TypeDocument::class,'idtype_document','id')->select(['id','description']);
    }

}
?>