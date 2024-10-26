<?php
namespace App\Interfaces;

interface HeadquartersRepositoryInterface
{
    public function repeatheadquarters($name);
    public function repeatheadquartersupdate($name,$id);
    public function delete($id,$request);
}
?>