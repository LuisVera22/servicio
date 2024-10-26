<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employee';

    protected $fillable = [
        'name',
        'lastname',
        'number_document',
        'address',
        'email',
        'cell_phone',
        'idtype_document',
        'idstore',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'idtype_document',
        'idstore'
    ];


    public function store()
    {
        return $this->belongsTo(Store::class, 'idstore', 'id')->select(['id','address','main','idheadquarter'])->with('headquarters');
    }
    public function typedocument()
    {
        return $this->belongsTo(TypeDocument::class,'idtype_document','id')->select(['id','description']);
    }
    public function user()
    {//para revisar con pruebas en una nueva relacion
        return $this->hasOne(User::class, 'idemployee', 'id')->with('role');
    }
}
