<?php
namespace App\Interfaces;

interface EmployeeRepositoryInterface
{
    public function repeatemployee($numberdocument);
    public function repeatemployeeUpdate($numberdocument,$id);
    public function delete($id,$request);
    public function repeatUser($username);
    public function repeatUserUpdate($username,$iduser);
    public function acountexists($username,$iduser);
}
?>