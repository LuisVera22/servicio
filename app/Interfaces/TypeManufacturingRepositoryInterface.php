<?php
namespace App\Interfaces;

interface TypeManufacturingRepositoryInterface
{
    public function repeattypeManufacturing($name,$abbreviation);    
    public function delete($id,$request);
}
?>