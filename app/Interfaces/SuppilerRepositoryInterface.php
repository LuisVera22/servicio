<?php
namespace App\Interfaces;

interface SuppilerRepositoryInterface
{
    public function repeatsuppiler($number_document);
    public function repeatsuppilerupdate($number_document,$id);
    public function delete($id,$request);
}
?>