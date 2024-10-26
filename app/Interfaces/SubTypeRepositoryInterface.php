<?php
namespace App\Interfaces;

interface SubTypeRepositoryInterface
{
    public function repeatsubtype($name,$abrev,$idtype);
    public function repeatsubtypeupdate($name,$abrev,$idtype,$id);
    public function delete ($id,$request);
}
?>