<?php
namespace App\Interfaces;

interface ProductsRepositoryInterface
{
    public function delete($id,$request);
    public function repeatProduct($description, $abrevmaterials, $abrevtype, $abrevsubtype, $abrevclasses, $abrevsubclasses, $idtype_manufacturing);
    public function generateDescription($request);
}
?>