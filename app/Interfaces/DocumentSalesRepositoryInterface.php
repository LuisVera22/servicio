<?php
namespace App\Interfaces;

interface DocumentSalesRepositoryInterface
{
    public function repeatdocumentsales($name);
    public function repeatdocumentsalesupdate($name,$id);
    public function delete($id,$request);
}
?>