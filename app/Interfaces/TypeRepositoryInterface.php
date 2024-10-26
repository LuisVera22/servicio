<?php
namespace App\Interfaces;

interface TypeRepositoryInterface
{
    public function repeattype($name,$abrev,$idtypemanufacturing);
    public function repeattypeupdate($name,$abrev,$idtypemanufacturing,$id);
    public function delete ($id,$request);
}