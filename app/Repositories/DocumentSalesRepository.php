<?php
namespace App\Repositories;

use App\Interfaces\DocumentSalesRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DocumentSalesRepository implements DocumentSalesRepositoryInterface
{
    public function repeatdocumentsales($name)
    {
        return DB::table('document_sales')
            ->where('description',$name)
            ->get();
    }
    public function repeatdocumentsalesupdate($name,$id)
    {
        return DB::table('document_sales')
            ->where('description',$name)
            ->where('id','!=',$id)
            ->get();
    }
    public function delete($param,$request)
    {
        return DB::table('document_sales')
            ->where('id', $param)
            ->update(
                ["enabled"  => $request]
            );
    }
}
?>