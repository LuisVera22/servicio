<?php

namespace App\Interfaces;

interface ProductsxStoreRepositoryInterface
{
    public function repeatProductxStore($description, $abrevmaterials, $abrevtype, $abrevsubtype, $abrevclasses, $abrevsubclasses, $idtype_manufacturing, $idstore);
    public function repeatProductxStoreUpdate($idtypemanufacturing,$description, $price, $idstore, $id);
}
?>