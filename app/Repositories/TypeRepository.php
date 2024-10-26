<?php
namespace App\Repositories;

use App\Interfaces\TypeRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TypeRepository implements TypeRepositoryInterface
{
    public function repeattype($name, $abrev,$idtypemanufacturing)
    {
        return DB::table('type')
            ->where([
                ['description', $name],
                ['abbreviation', $abrev],
                ['idtype_manufacturing', $idtypemanufacturing]
            ])
            ->get();
    }
    public function repeattypeupdate($name,$abrev,$idtypemanufacturing, $id)
    {
        return DB::table('type')
            ->where([
                ['description', $name],
                ['abbreviation', $abrev],
                ['idtype_manufacturing', $idtypemanufacturing],
                ['id', '!=', $id]
            ])
            ->get();
    }
    public function delete($param, $request)
    {
        return DB::table('type')
            ->where('id', $param)
            ->update(
                ["enabled" => $request]
            );
    }
}