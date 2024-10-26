<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubClasses extends Model
{
    protected $table = 'subclasses';

    protected $fillable = [
        'description',
        'enabled',
        'idclasses',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'idclasses',
        'created_at',
        'updated_at'
    ];

    public function classes()
    {
        return $this->belongsTo(Classes::class,'idclasses','id')->select(['id','description','idmaterial'])->with('materials');
    }
}
?>