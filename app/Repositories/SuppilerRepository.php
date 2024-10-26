<?php
namespace App\Repositories;

use App\Interfaces\SuppilerRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SuppilerRepository implements SuppilerRepositoryInterface
{
    public function repeatsuppiler($number_document)
    {
        return DB::table('suppiler')
            ->where('number_document',$number_document)
            ->get();
    }
    public function repeatsuppilerupdate($number_document,$id)
    {
        return DB::table('suppiler')
            ->where('number_document',$number_document)
            ->where('id','!=',$id)
            ->get();
    }
    public function delete($param,$request)
    {
        return DB::table('suppiler')
            ->where('id', $param)
            ->update(
                ["enabled"  => $request]
            );
    }
}
?>
