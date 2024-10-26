<?php
namespace App\Repositories;

use App\Interfaces\BusinessRepositoryInterface;
use Illuminate\Support\Facades\DB;

class BusinessRepository implements BusinessRepositoryInterface
{
    public function actualizarImagen($param)
    {
        return DB::table('business')
            ->where('id', $param['id'])
            ->update([
                "img_logo_empresa_name" => $param['img_logo_empresa_name']
            ]);
    }
}
?>