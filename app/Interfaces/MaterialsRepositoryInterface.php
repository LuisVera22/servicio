<?php
namespace App\Interfaces;

interface MaterialsRepositoryInterface
{
    public function repeatmaterial($name,$abrev,$idtype,$idsubtype);
    public function repeatmaterialupdate($name,$abrev,$idtype,$idsubtype,$id);
    public function delete ($id,$request);
}
?>