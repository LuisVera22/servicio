<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materials extends Model
{
    protected $table = 'materials';

    protected $fillable = [
        'description',
        'abbreviation',
        'enabled',
        'idtype',
        'idsubtype',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'idtype',
        'idsubtype',
        'created_at',
        'updated_at'
    ];

    public function type()
    {
        return $this->belongsTo(Type::class,'idtype','id')->select(['id','description','idtype_manufacturing'])->with('typemanufacturing');
    }
    public function subtype()
    {
        return $this->belongsTo(SubType::class,'idsubtype','id')->select(['id','description','idtype'])->with('type');
    }
}
?>