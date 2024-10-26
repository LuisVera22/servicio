<?php

namespace App\Repositories;

use App\Interfaces\SubClassesRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SubClassesRepository implements SubClassesRepositoryInterface
{
    public function repeatsubclasses($name, $idclasses)
    {
        return DB::table('subclasses')
            ->where([['description', $name], ['idclasses', $idclasses]])
            ->get();
    }
    public function repeatsubclassesupdate($name, $idclasses, $id)
    {
        return DB::table('subclasses')
            ->where([
                ['description', $name],
                ['idclasses', $idclasses],
                ['id', '!=', $id]
            ])
            ->get();
    }
    public function delete($param, $request)
    {
        return DB::table('subclasses')
            ->where('id', $param)
            ->update(
                ["enabled" => $request]
            );
    }
}
