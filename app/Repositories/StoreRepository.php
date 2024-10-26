<?php

namespace App\Repositories;

use App\Interfaces\StoreRepositoryInterface;
use Illuminate\Support\Facades\DB;

class StoreRepository implements StoreRepositoryInterface
{
    public function repeatstore($idheadquarter, $address)
    {
        return DB::table('store')
            ->where([
                ['idheadquarter', $idheadquarter],
                ['address', $address]
            ])
            ->get();
    }
    public function repeatstoreUpdate($idheadquarter, $address, $id)
    {
        return DB::table('store')
            ->where([
                ['idheadquarter', $idheadquarter],
                ['address', $address],
                ['id', '!=', $id]
            ])
            ->get();
    }
    public function delete($param, $request)
    {
        return DB::table('store')
            ->where('id', $param)
            ->update(
                ["enabled"  => $request]
            );
    }
    public function mainstoreexists()
    {
        return DB::table('store')
            ->where('main', 1)
            ->get();
    }
    public function mainstoreexistsUpdate($id)
    {
        return DB::table('store')
            ->where([
                ['main', 1],
                ['id', '!=', $id]
            ])
            ->get();
    }
}
