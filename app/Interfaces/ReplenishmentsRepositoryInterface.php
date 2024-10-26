<?php
namespace App\Interfaces;

interface ReplenishmentsRepositoryInterface
{
    public function searchIdQuotation($codigo);
    public function searchStoreHouse($codigo,$idstore);
    public function delete($id,$request);
    public function operationAmount($id,$idstorehouse,$idstore);
}