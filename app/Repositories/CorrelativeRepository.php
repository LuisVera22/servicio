<?php
namespace App\Repositories;

use App\Interfaces\CorrelativeRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CorrelativeRepository implements CorrelativeRepositoryInterface
{
    public function repeatcorrelative($numberdocument,$serie,$correlative)
    {
        return DB::table('correlative')
            ->where('iddocument_sales',$numberdocument)
            ->where('serie',$serie)
            ->where('correlative',$correlative)
            ->get();
    }
    public function repeatcorrelativeUpdate($numberdocument,$serie,$correlative,$id)
    {
        return DB::table('correlative')
            ->where('iddocument_sales',$numberdocument)
            ->where('serie',$serie)
            ->where('correlative',$correlative)
            ->where('id','!=',$id)
            ->get();
    }
    public function delete($param,$request)
    {
        return DB::table('correlative')
            ->where('id', $param)
            ->update(
                ["enabled"  => $request]
            );
    }
}
?>