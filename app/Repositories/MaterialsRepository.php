<?php

namespace App\Repositories;

use App\Interfaces\MaterialsRepositoryInterface;
use Illuminate\Support\Facades\DB;

class MaterialsRepository implements MaterialsRepositoryInterface
{
    public function repeatmaterial($name, $abrev,$idtype,$idsubtype)
    {
        return DB::table('materials')
            ->where([
                ['description', $name],
                ['abbreviation', $abrev],
                ['idtype', $idtype],
                ['idsubtype', $idsubtype]
            ])
            ->get();
    }
    public function repeatmaterialupdate($name, $abrev,$idtype,$idsubtype, $id)
    {
        return DB::table('materials')
            ->where([
                ['description', $name],
                ['abbreviation', $abrev],
                ['idtype', $idtype],
                ['idsubtype', $idsubtype],
                ['id', '!=', $id]
            ])
            ->get();
    }
    public function delete($param, $request)
    {
        return DB::table('materials')
            ->where('id', $param)
            ->update(
                ["enabled" => $request]
            );
    }
}
