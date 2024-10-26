<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeDocument extends Model
{
    protected $table = 'typedocument';

    protected $fillable = [
        'description',
        'code_sunat',
        'enabled',
        'created_at',
        'updated_at'
    ];
}
?>