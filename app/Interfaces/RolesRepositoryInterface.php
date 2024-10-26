<?php
namespace App\Interfaces;

interface RolesRepositoryInterface
{
    public function repeatroles($name);
    public function repeatrolesupdate($name,$id);
    public function delete($id,$request);
}
?>