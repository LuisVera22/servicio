<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\ProductOutputsDetail;

class ProductOutputsDetailController
{   

    public function store(Request $request, $id)
    {
        ProductOutputsDetail::create([
            'quantityouput'     => $request['cantidad'],
            'idproductouput'    => $id
        ]);
    }
}
