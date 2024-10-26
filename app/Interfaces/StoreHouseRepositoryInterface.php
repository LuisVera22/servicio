<?php
namespace App\Interfaces;

interface StoreHouseRepositoryInterface
{
    public function codeStoreHouse($idmanufactura);
    public function productStoreHouse($idproduct,$idcombination,$base,$diametro);
    public function repeatStoreHouse($product);
    public function delete ($id,$request);
}
?>