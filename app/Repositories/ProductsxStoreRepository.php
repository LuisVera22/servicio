<?php

namespace App\Repositories;

use App\Interfaces\ProductsxStoreRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProductsxStoreRepository implements ProductsxStoreRepositoryInterface
{
    public function repeatProductxStore($description, $abrevmaterials, $abrevtype, $abrevsubtype, $abrevclasses, $abrevsubclasses, $idtype_manufacturing, $idstore)
    {
        return DB::table('productsxstore')
            ->select(
                'productsxstore.*',
                'products.*'
            )
            ->join('products', 'products.id', '=', 'productsxstore.idproduct')
            ->where([
                ['idstore', $idstore],
                ['products.description', $description],
                ['products.abrevmaterials', $abrevmaterials],
                ['products.abrevtype', $abrevtype],
                ['products.abrevsubtype', $abrevsubtype],
                ['products.abrevclasses', $abrevclasses],
                ['products.abrevsubclasses', $abrevsubclasses],
                ['products.idtype_manufacturing', $idtype_manufacturing],
            ])
            ->get();
    }
    public function repeatProductxStoreUpdate($idtypemanufacturing,$description, $price, $idstore, $id)
    {
        return DB::table('productsxstore')
            ->select(
                'productsxstore.*',
                'products.*'
            )
            ->join('products', 'products.id', '=', 'productsxstore.idproduct')
            ->where([
                ['products.idtype_manufacturing', $idtypemanufacturing],
                ['products.description', $description],
                ['price', $price],
                ['idstore', $idstore],
                ['productsxstore.idproduct', '!=', $id]
            ])
            ->get();
    }
}
