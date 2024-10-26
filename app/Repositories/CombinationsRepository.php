<?php
namespace App\Repositories;

use App\Interfaces\CombinationsRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CombinationsRepository implements CombinationsRepositoryInterface
{
    public function repeatcombinations($description)
    {
        return DB::table('combinations')
            ->where('description',$description)
            ->get();
    }
    public function repeatcombinationsUpdate($description,$id)
    {
        return DB::table('combinations')
            ->where('description',$description)
            ->where('id','!=',$id)
            ->get();
    }
    public function delete($param,$request)
    {
        return DB::table('combinations')
            ->where('id', $param)
            ->update(
                ["enabled"  => $request]
            );
    }
}