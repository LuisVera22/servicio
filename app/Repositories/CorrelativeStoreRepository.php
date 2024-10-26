<?php
namespace App\Repositories;

use App\Interfaces\CorrelativeStoreRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CorrelativeStoreRepository implements CorrelativeStoreRepositoryInterface
{
    public function repeatcorrelativestore($correlative,$store)
    {
        return DB::table('store_correlative')            
            ->where('idcorrelative',$correlative)
            ->where('idstore',$store)
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