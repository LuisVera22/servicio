<?php

namespace App\Http\Controllers;

use App\Interfaces\ProductOutputsRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\ProductOutputs;
use App\Models\ProductOutputsDetail;
use App\Models\StoreHousexStore;
use App\Models\Transactions;

class ProductOutputsController
{
    protected $detailController;
    private $repository;

    public function __construct(ProductOutputsDetail $detailController, ProductOutputsRepositoryInterface $repository)
    {
        $this->detailController = $detailController;
        $this->repository = $repository;
    }

    public function store(Request $request)
    {
        $rules = [
            'cantidad'    => 'required|integer|min:0',
            'idstore'     => 'required|integer'
        ];

        $messages = [
            'cantidad.required' =>  'El campo cantidad es obligatorio.',
            'cantidad.integer'  =>  'El campo cantidad debe ser un numerico entero.',
            'cantidad.min'      =>  'El campo cantidad debe ser positivo.',
            'idstore.required'  =>  'El campo local es obligatorio'
        ];

        try {
            if ($this->isDatabaseDown()) {
                if ($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status'    => false,
                        'errors'    => ['mantenimiento'],
                        'message'   => 'La aplicación esta en mantenimiento. Inténtalo de nuevo más tarde.'
                    ], 503);
                } else {
                    $validator = Validator::make($request->input(), $rules, $messages);
                    if ($validator->fails()) {
                        return response()->json([
                            'status'    => false,
                            'alert'     => 'rules',
                            'errors'    => $validator->errors()->all()
                        ], 400);
                    } else {
                        DB::beginTransaction();
                        try {
                            $storehouse = $this->repository->filterStoreHouse($request['codigo']);

                            if (isset($storehouse)) {
                                $storehousexstore = $this->repository->searchQuantity($request['local'], $storehouse->id);
                                $calculate = $storehousexstore->quantity - $request['cantidad'];
                                if ($calculate > 0) {

                                    $productoutputs = new ProductOutputs([
                                        'date'      => $request['date'],
                                        'idstore'   => $request['idstore']
                                    ]);

                                    $productoutputs->save();

                                    $this->detailController->store($request, $productoutputs->id);

                                    Transactions::create([
                                        'transactiontype'       => 'output',
                                        'quantity'              => $request['cantidad'],
                                        'quantitystorehouse'    => $storehousexstore->quantity,
                                        'newquantiry'           => $calculate,
                                        'transactiondate'       => now(),
                                        'idstorehouse'          => $storehouse->id
                                    ]);

                                    StoreHousexStore::where([['idstorehouse', $storehouse->id], ['idstore', $request['local']]])
                                        ->update(['quantity'   => $calculate]);

                                    DB::commit();
                                    return response()->json([
                                        'status'  =>  true,
                                        'message' =>  'Registro Generado.'
                                    ], 200);
                                } else {
                                    return response()->json([
                                        'status'  =>  true,
                                        'alert'  =>  'info',
                                        'message' =>  'Cantidad Minima.'
                                    ], 200);
                                }
                            } else {
                                return response()->json([
                                    'status'  =>  true,
                                    'alert'  =>  'info',
                                    'message' =>  'No existe Almacen.'
                                ], 200);
                            }
                        } catch (\Exception $e) {
                            DB::rollBack();
                            return response()->json([
                                'status'    => false,
                                'errors'    => ['Error al crear el registro.'],
                                'message'   => 'Error: ' . $e->getMessage(),
                            ], 500);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'errors'  => ['No se pudo conectar a la base de datos'],
                'message' => 'Error: '  . $e->getMessage()
            ], 500);
        }
    }

    private function isAppDownForMaintenance()
    {
        return app()->isDownForMaintenance();
    }

    private function isDatabaseDown()
    {
        try {
            // Intentar ejecutar una consulta de prueba
            DB::select('SELECT 1');

            return true;
        } catch (\Exception $e) {
            // Cualquier excepción, considerar que la base de datos está inaccesible
            return $e;
        }
    }
}
