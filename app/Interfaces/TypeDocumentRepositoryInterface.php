<?php
namespace App\Interfaces;

interface TypeDocumentRepositoryInterface
{
    public function repeattypedocument($name);
    public function repeattypedocumentupdate($name,$id);
    public function delete($id,$request);
}
?>