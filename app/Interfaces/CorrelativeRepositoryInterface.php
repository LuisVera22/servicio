<?php
namespace App\Interfaces;

interface CorrelativeRepositoryInterface
{
    public function repeatcorrelative($numberdocument,$serie,$correlative);
    public function repeatcorrelativeUpdate($numberdocument,$serie,$correlative,$id);
    public function delete($id,$request);
}
?>