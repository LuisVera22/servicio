<?php
namespace App\Repositories;

use App\Interfaces\EmployeeRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    public function repeatemployee($numberdocument)
    {
        return DB::table('employee')
            ->where('number_document',$numberdocument)
            ->get();
    }
    public function repeatemployeeUpdate($numberdocument,$id)
    {
        return DB::table('employee')
            ->where('number_document',$numberdocument)
            ->where('id','!=',$id)
            ->get();
    }
    public function delete($param,$request)
    {
        return DB::table('employee')
            ->where('id', $param)
            ->update(
                ["enabled"  => $request]
            );
    }
    public function repeatUser($username)
    {
        return DB::table('users')
            ->where('username',$username)
            ->get();
    }
    public function repeatUserUpdate($username,$iduser)
    {
        return DB::table('users')
            ->where('username',$username)
            ->where('id','!=',$iduser)
            ->get();
    }
    public function acountexists($username,$iduser)
    {
        return DB::table('users')
            ->where('username',$username)
            ->where('id','=',$iduser)
            ->pluck('password')
            ->first();
    }
}
?>