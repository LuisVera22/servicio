<?php
namespace App\Interfaces;

interface StoreHousexStoreRepositoryInterface
{
    public function fillStoreHouse($codigo,$idTypeManufacturing);
    public function searchStoreHouse($codigo,$idTypeManufacturing);
}
?>