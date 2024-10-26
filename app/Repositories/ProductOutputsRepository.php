<?php

namespace App\Repositories;

use App\Interfaces\ProductOutputsRepositoryInterface;
use App\Models\StoreHouse;
use App\Models\StoreHousexStore;

class ProductOutputsRepository implements ProductOutputsRepositoryInterface
{
    public function filterStoreHouse($codigo)
    {
        $storehouse = StoreHouse::select('*')
            ->where('codigo', $codigo)
            ->first();

        return $storehouse;
    }

    public function searchQuantity($idstore, $idstorehouse)
    {
        $storehousexstore = StoreHousexStore::select('quantity')
            ->where([['idstore', $idstore], ['idstorehouse', $idstorehouse]])
            ->first();

        return $storehousexstore;
    }
}
