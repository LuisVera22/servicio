<?php

namespace App\Repositories;

use App\Interfaces\HeadquartersRepositoryInterface;
use Illuminate\Support\Facades\DB;

class HeadquartersRepository implements HeadquartersRepositoryInterface
{
    public function repeatheadquarters($name)
    {
        return DB::table('headquarters')
            ->where('description',$name)
            ->get();
    }
    public function repeatheadquartersupdate($name,$id)
    {
        return DB::table('Headquarters')
            ->where('description',"=",$name)
            ->where('id','!=',$id)
            ->get();
    }
    public function delete($param,$request)
    {
        return DB::table('Headquarters')
            ->where('id', $param)
            ->update(
                ["enabled"  => $request]
            );
    }
}
?>