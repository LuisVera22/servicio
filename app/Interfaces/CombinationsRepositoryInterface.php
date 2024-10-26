<?php
namespace App\Interfaces;

interface CombinationsRepositoryInterface
{
    public function repeatcombinations($description);
    public function repeatcombinationsUpdate($description,$id);
    public function delete($id,$request);
}
?>