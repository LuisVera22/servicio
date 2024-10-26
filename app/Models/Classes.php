<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'description',
        'abbreviation',        
        'enabled',
        'idmaterial',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'idmaterial',
        'created_at',
        'updated_at'
    ];

    public function materials()
    {
        return $this->belongsTo(Materials::class,'idmaterial','id')->select(['id','description','idtype','idsubtype'])->with(['type','subtype']);
    }
}
?>