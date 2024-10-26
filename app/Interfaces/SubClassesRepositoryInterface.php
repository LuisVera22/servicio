<?php
namespace App\Interfaces;

interface SubClassesRepositoryInterface
{
    public function repeatsubclasses($name,$idclasses);
    public function repeatsubclassesupdate($name,$idclasses,$id);
    public function delete ($id,$request);
}
?>