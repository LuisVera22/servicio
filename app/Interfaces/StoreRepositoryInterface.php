<?php
namespace App\Interfaces;

interface StoreRepositoryInterface
{
    public function repeatstore($idstore,$address);
    public function repeatstoreUpdate($idstore,$address,$id);
    public function delete($id,$request);
    public function mainstoreexists();
    public function mainstoreexistsUpdate($id);
}
?>