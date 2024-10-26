<?php

namespace App\Http\Controllers;

use App\Interfaces\StoreHousexStoreRepositoryInterface;
use App\Models\StoreHousexStore;
use App\Models\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StoreHousexStoreController
{
    private $repository;

    public function __construct(
        StoreHousexStoreRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function store(Request $request)
    {
        $rules = ['cantidad'    => 'required|integer|min:0'];
        
        $messages = [
            'cantidad.required' =>  'El campo cantidad es obligatorio.',
            'cantidad.integer'  =>  'El campo cantidad debe ser un numerico entero.',
            'cantidad.min'      =>  'El campo cantidad debe ser positivo.',
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
                            $operacion = $this->repository->fillStoreHouse($request['codigo'], $request['idTypeManufacturing']);

                            if (isset($operacion)) {

                                if ($operacion->quantity != 0 && $request['cantidad'] != 0) {

                                    $previousQuantity = $operacion->quantity;
                                    $newQuantity = $operacion->quantity + $request['cantidad'];

                                    StoreHousexStore::where([['idstorehouse', $operacion->idstorehouse], ['idstore', $request['idstore']]])
                                        ->update(['quantity'   => $newQuantity]);

                                    Transactions::create([
                                        'transactiontype'       => 'income',
                                        'quantity'              => $request['cantidad'],
                                        'quantitystorehouse'    => $previousQuantity,
                                        'newquantiry'           => $newQuantity,
                                        'transactiondate'       => now(),
                                        'idstorehouse'          => $operacion->idstorehouse
                                    ]);
                                }
                            } else {
                                $storeHouse = $this->repository->searchStoreHouse($request['codigo'], $request['idTypeManufacturing']);

                                if (isset($storeHouse)) {
                                    $addstorehousexstore = StoreHousexStore::create([
                                        'idstorehouse' => $storeHouse->id,
                                        'idstore'      => $request['idstore'],
                                        'quantity'     => $request['cantidad']
                                    ]);

                                    // obtener el nuevo valor de quantity
                                    $previousQuantity = 0;
                                    $newQuantity = $addstorehousexstore->quantity;
                                    $newid = $addstorehousexstore->idstorehouse;

                                    if ($addstorehousexstore->quantity != 0) {
                                        Transactions::create([
                                            'transactiontype'       => 'income',
                                            'quantity'              => $request['cantidad'],
                                            'quantitystorehouse'    => $previousQuantity,
                                            'newquantiry'           => $newQuantity,
                                            'transactiondate'       => now(),
                                            'idstorehouse'          => $newid
                                        ]);
                                    }
                                }
                            }
                            DB::commit();
                            return response()->json([
                                'status'  =>  true,
                                'message' =>  'Registro Generado.'
                            ], 200);
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

    public function listStoreHousexManufacturing($idManufacture, $idstore)
    {
        if ($idstore == 0) {
            $storehouse = StoreHousexStore::with(['storehouse', 'store'])
                ->whereHas('storehouse.products.typemanufacturing', function ($query) use ($idManufacture) {
                    $query->where('id', $idManufacture);
                })
                ->get();
        } else {
            $storehouse = StoreHousexStore::select('*')
                ->whereHas('storehouse.products.typemanufacturing', function ($query) use ($idManufacture) {
                    $query->where('id', $idManufacture);
                })
                ->where([
                    ['idstore', $idstore],
                ])
                ->get();
        }

        $storehouse->makeHidden(['idcombination', 'idproduct']);

        return response()->json($storehouse, 200);
    }

    private function isAppDownForMaintenance()
    {
        return app()->isDownForMaintenance();
    }
    private function isDatabaseDown()
    {
        try {
            DB::select('SELECT 1');

            return true;
        } catch (\Exception $e) {
            return $e;
        }
    }
}
