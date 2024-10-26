<?php
namespace App\Interfaces;

interface CorrelativeStoreRepositoryInterface
{
    public function repeatcorrelativestore($correlative,$store);
    public function delete($id,$request);
}
?>