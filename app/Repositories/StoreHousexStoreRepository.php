<?php

namespace App\Repositories;

use App\Interfaces\StoreHousexStoreRepositoryInterface;
use App\Models\StoreHouse;
use App\Models\StoreHousexStore;
use Illuminate\Support\Facades\DB;

class StoreHousexStoreRepository implements StoreHousexStoreRepositoryInterface
{
    public function fillStoreHouse($codigo,$idTypeManufacturing)
    {
        $storehouse = StoreHousexStore::whereHas('storehouse', function ($query) use ($codigo) {
            $query->where('codigo', $codigo);
        })->whereHas('storehouse.products', function ($query) use ($idTypeManufacturing) {
            $query->where('idtype_manufacturing', $idTypeManufacturing);
        })->first();

        return $storehouse;
    }

    public function searchStoreHouse($codigo,$idTypeManufacturing)
    {
        $storehouse = StoreHouse::select('*')
            ->where('codigo',$codigo)
            ->whereHas('products', function ($query) use ($idTypeManufacturing) {
                $query->where('idtype_manufacturing', $idTypeManufacturing);
            })->first();

        return $storehouse;
    }
}
