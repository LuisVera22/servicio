<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{
    protected $table = 'laboratory';

    protected $fillable = [
        'esfod',
        'cylod',
        'addod',
        'ejeod',
        'prismaod',
        'altod',
        'dipod',
        'diametrood',
        'esfoi',
        'cyloi',
        'addoi',
        'ejeoi',
        'prismaoi',
        'altoi',
        'dipoi',
        'diametrooi',
        'v',
        'h',
        'd',
        'pte',
        'alt',
        'dip',
        'inicialespaciente',
        'diametro',
        'corredor',
        'reduccion',
        'idquotationdetail',
        'created_at',
        'updated_at'
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
