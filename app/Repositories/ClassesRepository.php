<?php

namespace App\Repositories;

use App\Interfaces\ClassesRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ClassesRepository implements ClassesRepositoryInterface
{
    public function repeatclasses($name,$abbreviation, $idmaterial)
    {
        return DB::table('classes')
            ->where([
                ['description', $name],
                ['abbreviation', $abbreviation],
                ['idmaterial', $idmaterial]
            ])
            ->get();
    }
    public function repeatclassesupdate($name,$abbreviation, $idmaterial, $id)
    {
        return DB::table('classes')
            ->where([
                ['description', $name],
                ['abbreviation', $abbreviation],
                ['idmaterial', $idmaterial],
                ['id', '!=', $id],
            ])
            ->get();
    }
    public function delete($param, $request)
    {
        return DB::table('classes')
            ->where('id', $param)
            ->update(
                ["enabled" => $request]
            );
    }
}
