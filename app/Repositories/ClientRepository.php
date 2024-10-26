<?php
namespace App\Repositories;

use App\Interfaces\ClientRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ClientRepository implements ClientRepositoryInterface
{
    public function repeatclient($numberdocument)
    {
        return DB::table('client')
            ->where('number_document',$numberdocument)
            ->get();
    }
    public function repeatclientUpdate($numberdocument,$id)
    {
        return DB::table('client')
            ->where('number_document',$numberdocument)
            ->where('id','!=',$id)
            ->get();
    }
    public function delete($param,$request)
    {
        return DB::table('client')
            ->where('id', $param)
            ->update(
                ["enabled"  => $request]
            );
    }
}
?>