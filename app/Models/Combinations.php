<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Combinations extends Model
{
    protected $table = 'combinations';

    protected $fillable = [
        'description',
        'esf',
        'cil',
        'add',
        'enabled',
        'created_at',
        'updated_at'
    ];
}
?>