<?php
namespace App\Repositories;

use App\Interfaces\RolesRepositoryInterface;
use Illuminate\Support\Facades\DB;

class RolesRepository implements RolesRepositoryInterface
{
    public function repeatroles($name)
    {
        return DB::table('roles')
            ->where('description',$name)
            ->get();
    }
    public function repeatrolesupdate($name,$id)
    {
        return DB::table('roles')
            ->where('description',$name)
            ->where('id','!=',$id)
            ->get();
    }
    public function delete($param,$request)
    {
        return DB::table('roles')
            ->where('id', $param)
            ->update(
                ["enabled"  => $request]
            );
    }
}
?>