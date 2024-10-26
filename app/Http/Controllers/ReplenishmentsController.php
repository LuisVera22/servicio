<?php

namespace App\Http\Controllers;

use App\Interfaces\ReplenishmentsRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Replenishments;
use App\Models\StoreHousexStore;

class ReplenishmentsController
{
    private $repository;

    public function __construct(
        ReplenishmentsRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function show(Replenishments $replenishment)
    {
        $replenishment->load([
            'storehousexstore',
            'quotation'
        ]);
        return response()->json(['status'   => true, 'data' => $replenishment], 200);
    }

    public function store(Request $request)
    {
        $rules = [
            'codigoSh'      =>  'required|string|',
            'codigoOt'      =>  'required|string|',
            'quantity'      =>  'required|numeric|',
            'description'   =>  'required|string|',
            'idstore'       =>  'required|numeric|'
        ];
        
        $messages = [
            'codigoSh.required'     =>  'El campo código producto es obligatorio.',
            'codigoSh.string'       =>  'El campo código producto debe ser una cadena de texto.',
            'codigoOt.required'     =>  'El campo código orden trabajo es obligatorio.',
            'codigoOt.string'       =>  'El campo código orden trabajo debe ser una cadena de texto.',
            'quantity.required'     =>  'El campo cantidad es obligatorio.',
            'quantity.numeric'      =>  'El campo cantidad debe ser numerico entero.',
            'description.required'  =>  'El campo descripción es obligatorio.',
            'description.string'    =>  'El campo descripción debe ser una cadena de texto.',
            'idstore.required'      =>  'La sede es obligatorio.',
            'idstore.numeric'       =>  'No se encontró la sede para reponer.',
        ];

        try {
            if ($this->isDatabaseDown()) {
                if ($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['mantenimiento.'],
                        'message' => 'La aplicación esta en mantenimiento. Inténtalo de nuevo más tarde.'
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
                            $searchIdQuotation = $this->repository->searchIdQuotation($request['codigoOt']);
                            $storehouse = $this->repository->searchStoreHouse($request['codigoSh'], $request['idstore']);

                            if ($searchIdQuotation) {
                                if ($storehouse) {
                                    $newQuantity = $storehouse->quantity + $request['quantity'];
                                    StoreHousexStore::where([['idstorehouse', $storehouse->idstorehouse], ['idstore', $request['idstore']]])
                                        ->update(['quantity'   => $newQuantity]);

                                    Replenishments::create([
                                        'description'       => $request['description'],
                                        'datetime'          => $request['datetime'],
                                        'date'              => $request['date'],
                                        'codigoquotation'   => $request['codigoOt'],
                                        'replacementmount'  => $request['quantity'],
                                        'oldquantity'       => $storehouse->quantity,
                                        'newquantity'       => $newQuantity,
                                        'idquotation'       => $searchIdQuotation,
                                        'idstorehouse'      => $storehouse->id,
                                    ]);
                                    DB::commit();
                                    return response()->json([
                                        'status'  =>  true,
                                        'message' =>  'Registro Generado.'
                                    ], 200);
                                } else {
                                    return response()->json([
                                        'status'    => false,
                                        'alert'     => 'rules',
                                        'errors'    => ['Código de producto no encontrado.']
                                    ], 400);
                                }
                            } else {
                                return response()->json([
                                    'status'    => false,
                                    'alert'     => 'rules',
                                    'errors'    => ['Código de orden no encontrado.']
                                ], 400);
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
                'status' => false,
                'errors' => ['No se pudo conectar a la base de datos'],
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id, Request $request)
    {
        try {
            if ($this->isDatabaseDown()) {
                if ($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status'  => false,
                        'errors'  => ['mantenimiento.'],
                        'message' => 'La aplicación esta en mantenimiento. Inténtalo de nuevo más tarde.'
                    ], 503);
                } else {
                    $response = $this->repository->delete($id, $request['enabled']);
                    if (isset($response) && $response == 1) {

                        $decreasedamount = $this->repository->operationAmount($id, $request['idstorehouse'], $request['idstore']);

                        if ($decreasedamount) {
                            StoreHousexStore::where([['idstorehouse', $request['idstorehouse']]])
                                ->update(['quantity'   => $decreasedamount]);

                            return response()->json([
                                'status'    =>   true,
                                'message'   => 'Eliminación exitosa.'
                            ], 200);
                        } else {
                            return response()->json([
                                'status'   =>  false,
                                'message'  => 'Error al eliminar.'
                            ], 500);
                        }
                    } else {
                        return response()->json([
                            'status'   =>  false,
                            'message'  => 'Error al eliminar.'
                        ], 500);
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  =>  false,
                'errors'  =>  ['No se pudo conectar a la base de datos'],
                'message' => 'Error :' . $e->getMessage()
            ], 500);
        }
    }

    public function listReplenishmentsxManufacturing($idManufacture, $idstore)
    {
        if ($idstore == 0) {
            $storehouse = Replenishments::with(['storehousexstore'])
                ->whereHas('storehousexstore.storehouse.products.typemanufacturing', function ($query) use ($idManufacture) {
                    $query->where('id', $idManufacture);
                })
                ->get();
        } else {
            $storehouse = Replenishments::select('*')
                ->whereHas('storehousexstore.storehouse.products.typemanufacturing', function ($query) use ($idManufacture) {
                    $query->where('id', $idManufacture);
                })
                ->whereHas('storehousexstore', function ($query) use ($idstore) {
                    $query->where('idstore', $idstore);
                })
                ->get();
        }

        $storehouse->makeHidden(['idcombination', 'idproduct']);

        return response()->json($storehouse, 200);
    }
    public function listReplenishmentsxManufacturingActivos($idManufacture)
    {
        $storehouse = Replenishments::select('*')
            ->whereHas('storehousexstore.storehouse.products.typemanufacturing', function ($query) use ($idManufacture) {
                $query->where('id', $idManufacture);
            })
            ->where('enabled', '1')
            ->get();
        return response()->json($storehouse);
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
