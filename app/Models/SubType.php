<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubType extends Model
{
    protected $table = 'subtype';

    protected $fillable = [
        'description',
        'abbreviation',
        'enabled',
        'idtype',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'idtype',
        'created_at',
        'updated_at'
    ];

    public function type()
    {
        return $this->belongsTo(Type::class,'idtype','id')->select(['id','description','idtype_manufacturing'])->with('typemanufacturing');
    }
}
?>