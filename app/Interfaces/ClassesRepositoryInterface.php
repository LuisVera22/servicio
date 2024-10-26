<?php
namespace App\Interfaces;

interface ClassesRepositoryInterface
{
    public function repeatclasses($name,$abbreviation,$idmaterial);
    public function repeatclassesupdate($name,$abbreviation,$idmaterial,$id);
    public function delete ($id,$request);
}
?>