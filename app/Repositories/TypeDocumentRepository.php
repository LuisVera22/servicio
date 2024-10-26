<?php
namespace App\Repositories;

use App\Interfaces\TypeDocumentRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TypeDocumentRepository implements TypeDocumentRepositoryInterface
{
    public function repeattypedocument($name)
    {
        return DB::table('type_document')
            ->where('description',$name)
            ->get();
    }
    public function repeattypedocumentupdate($name,$id)
    {
        return DB::table('type_document')
            ->where('description',$name)
            ->where('id','!=',$id)
            ->get();
    }
    public function delete($param,$request)
    {
        return DB::table('type_document')
            ->where('id', $param)
            ->update(
                ["enabled"  => $request]
            );
    }
}
?>