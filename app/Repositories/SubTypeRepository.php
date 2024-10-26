<?php

namespace App\Repositories;

use App\Interfaces\SubTypeRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SubTypeRepository implements SubTypeRepositoryInterface
{
    public function repeatsubtype($name,$abrev,$idtype)
    {
        return DB::table('subtype')
            ->where([
                ['description', $name],
                ['abbreviation', $abrev],
                ['idtype', $idtype]
            ])
            ->get();
    }
    public function repeatsubtypeupdate($name,$abrev,$idtype,$id)
    {
        return DB::table('subtype')
            ->where([
                ['description', $name],
                ['abbreviation', $abrev],
                ['idtype', $idtype],
                ['id', '!=', $id]
            ])
            ->get();
    }
    public function delete($param, $request)
    {
        return DB::table('subtype')
            ->where('id', $param)
            ->update(
                ["enabled" => $request]
            );
    }
}
