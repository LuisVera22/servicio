<?php
namespace App\Interfaces;

interface ProductOutputsRepositoryInterface
{
    public function filterStoreHouse($codigo);
    public function searchQuantity($idstore,$idstorehouse);
}