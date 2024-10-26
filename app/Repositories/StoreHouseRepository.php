<?php

namespace App\Repositories;

use App\Interfaces\StoreHouseRepositoryInterface;
use App\Models\Combinations;
use App\Models\Products;
use App\Models\TypeManufacturing;
use Illuminate\Support\Facades\DB;

class StoreHouseRepository implements StoreHouseRepositoryInterface
{
    public function codeStoreHouse($idmanufactura)
    {

        $typemanufacturing = TypeManufacturing::find($idmanufactura);

        $valor = $typemanufacturing->abbreviation;

        $codigo = $valor . "-" . substr(uniqid(), 5);

        return $codigo;
    }
    public function productStoreHouse($idproduct, $idcombination, $base, $diametro)
    {
        $product = Products::find($idproduct);
        $combination = Combinations::find($idcombination);

        if (isset($base) && $base != null) {
            $textBase = ' Base: ' . $base;
        } else {
            $textBase = null;
        }
        if (isset($diametro) && $diametro != null) {
            $textDiametro = ' Diam: ' . $diametro;
        } else {
            $textDiametro = null;
        }

        $productStore = $product->abrevmaterials . ' ' . $product->abrevclasses . ' ' . $product->abrevsubclasses . ' ' . $combination->description . ' ' . $textBase . ' ' . $textDiametro;

        return $productStore;
    }
    public function repeatStoreHouse($product)
    {
        return DB::table('storehouse')
            ->select(
                '*'
            )
            ->where([
                ['product', $product]
            ])
            ->get();
    }
    public function delete($param, $request)
    {
        return DB::table('storehouse')
            ->where('id', $param)
            ->update(
                ["enabled" => $request]
            );
    }
}
