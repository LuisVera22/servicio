<?php
namespace App\Interfaces;

interface QuotationRepositoryInterface
{
    public function codeQuotation();
    public function delete($id,$request);
}
?>