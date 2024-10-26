<?php

namespace App\Repositories;

use App\Interfaces\ProductsRepositoryInterface;
use App\Models\Classes;
use App\Models\Materials;
use App\Models\SubClasses;
use App\Models\SubType;
use App\Models\Type;
use Illuminate\Support\Facades\DB;

class ProductsRepository implements ProductsRepositoryInterface
{
    public function repeatProduct($description, $abrevmaterials, $abrevtype, $abrevsubtype, $abrevclasses, $abrevsubclasses, $idtype_manufacturing)
    {
        return DB::table('products')
            ->where([
                ['description', $description],
                ['abrevmaterials', $abrevmaterials],
                ['abrevtype', $abrevtype],
                ['abrevsubtype', $abrevsubtype],
                ['abrevclasses', $abrevclasses],
                ['abrevsubclasses', $abrevsubclasses],
                ['idtype_manufacturing', $idtype_manufacturing]
            ])
            ->get();
    }
    public function delete($param, $request)
    {
        return DB::table('products')
            ->where('id', $param)
            ->update(
                ["enabled"  => $request]
            );
    }
    public function generateDescription($request)
    {
        $tipo       = Type::find($request->idtype);
        $subtipo    = SubType::find($request->idsubtype);
        $material   = Materials::find($request->idmaterial);
        $clase      = Classes::find($request->idclasses);
        $subclase   = SubClasses::find($request->idsubclasses);

        $abrevtype = $tipo ? $tipo->abbreviation : '';
        $abrevsubtype = $subtipo ? $subtipo->abbreviation : '';
        $abrevmaterials = $material ? $material->abbreviation : '';
        $abrevclasses = $clase ? $clase->abbreviation : '';
        $abrevsubclasses = $subclase ? $subclase->description : '';

        // Generar la descripción
        $description = trim("$abrevmaterials $abrevtype $abrevsubtype $abrevclasses $abrevsubclasses");

        return [
            'description' => $description,
            'abrevtype' => $abrevtype,
            'abrevsubtype' => $abrevsubtype,
            'abrevmaterials' => $abrevmaterials,
            'abrevclasses' => $abrevclasses,
            'abrevsubclasses' => $abrevsubclasses,
        ];
    }
}
