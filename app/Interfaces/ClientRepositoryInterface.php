<?php
namespace App\Interfaces;

interface ClientRepositoryInterface
{
    public function repeatclient($numberdocument);
    public function repeatclientUpdate($numberdocument,$id);
    public function delete($id,$request);
}
?>