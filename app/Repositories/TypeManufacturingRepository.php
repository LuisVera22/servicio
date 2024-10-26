<?php

namespace App\Repositories;

use App\Interfaces\TypemanufacturingRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TypeManufacturingRepository implements TypemanufacturingRepositoryInterface
{
    public function repeattypemanufacturing($name, $abbreviation)
    {
        return DB::table('type_manufacturing')
            ->where([
                ['description', $name],
                ['abbreviation', $abbreviation]
            ])
            ->get();
    }
    public function delete($param, $request)
    {
        return DB::table('type_manufacturing')
            ->where('id', $param)
            ->update(
                ["enabled"  => $request]
            );
    }
}
