<?php

namespace App\Repositories;

use App\Interfaces\QuotationRepositoryInterface;
use Illuminate\Support\Facades\DB;

class QuotationRepository implements QuotationRepositoryInterface
{
    public function codeQuotation()
    {
        $result = DB::table('quotation')
            ->select('codigo')
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        if ($result) {
            $lastCode = $result->codigo;
        } else {
            $lastCode = "OT-00";
        }

        $lastNumber = intval(substr($lastCode, 3));
        // Incrementar el número
        $newNumber = $lastNumber + 1;
        $newCode = 'OT-'.sprintf('%02d',$newNumber);
        return $newCode;
    }
    public function delete($param,$request)
    {
        return DB::table('quotation')
        ->where('id', $param)
        ->update(
            ["enabled"  => $request]
        );
    }
}
