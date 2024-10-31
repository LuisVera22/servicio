<?php
namespace App\Repositories;
use App\Interfaces\PettyCashRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PettyCashRepository implements PettyCashRepositoryInterface
{
    public function delete($param) {
        return DB::table("pettycash")
            ->where("id",$param)
            ->delete();
    }
}