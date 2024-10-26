<?php

namespace App\Repositories;

use App\Interfaces\ReplenishmentsRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ReplenishmentsRepository implements ReplenishmentsRepositoryInterface
{
    public function searchIdQuotation($codigo)
    {
        $quotation = DB::table('quotation')
            ->where('codigo', $codigo)
            ->first();

        return $quotation->id;
    }

    public function searchStoreHouse($codigo, $idstore)
    {
        $storehouse = DB::table('store_storehouse')
            ->join('storehouse', 'store_storehouse.idstorehouse', 'storehouse.id')
            ->where([['storehouse.codigo', $codigo], ['store_storehouse.idstore', $idstore]])
            ->first();

        return $storehouse;
    }

    public function delete($param, $request)
    {
        return DB::table('replenishments')
            ->where('id', $param)
            ->update(
                ["enabled" => $request]
            );
    }

    public function operationAmount($id, $idstorehouse, $idstore)
    {
        $storehouse = DB::table('store_storehouse')
            ->where([['idstorehouse', $idstorehouse], ['idstore', $idstore]])
            ->first();

        $replenishments = DB::table('replenishments')
            ->where('id', $id)
            ->first();

        $amount = $storehouse->quantity - $replenishments->replacementmount; 

        return $amount;
    }
}
