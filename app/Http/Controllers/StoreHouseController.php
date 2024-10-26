<?php

namespace App\Http\Controllers;

use App\Exports\StoreHouseExport;
use App\Interfaces\StoreHouseRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\StoreHouse;
use Maatwebsite\Excel\Facades\Excel;

class StoreHouseController
{
    private $repository;

    public function __construct(
        StoreHouseRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }
    public function indexByJob($job)
    {
        $storehouse = StoreHouse::with(['products.typemanufacturing' => function ($query) use ($job) {
            $query->where('job', $job);
        }])
            ->whereHas('products.typemanufacturing', function ($query) use ($job) {
                $query->where('job', $job);
            })
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($storehouse);
    }
    public function show(StoreHouse $storehouse)
    {
        $storehouse->load([
            'StoreHousexstore'
        ]);
        return response()->json(['status'   => true, 'data' => $storehouse]);
    }
    public function store(Request $request)
    {
        $rules = [
            'idcombination' =>  'required',
            'idproduct'     =>  'required'
        ];
        $messages = [
            'idcombination.required'        =>  'El campo tipo de medida es obligatorio.',
            'idproduct.required'            =>  'El campo producto es obligatorio.',
        ];

        try {
            if ($this->isDatabaseDown()) {
                if ($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['mantenimiento'],
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

                            $code = $this->repository->codeStoreHouse($request['idmanufactura']);
                            $product = $this->repository->productStoreHouse($request['idproduct'], $request['idcombination'], $request['base'], $request['diametro']);

                            $repeatStoreHousexStore = $this->repository->repeatStoreHouse($product);

                            if (count($repeatStoreHousexStore) > 0) {
                                DB::rollBack();
                                return response()->json([
                                    'status'   =>  true,
                                    'message'  =>  'El valor ya existe en la base de datos.'
                                ], 409);
                            } else {
                                StoreHouse::create([
                                    'codigo'                => $code,
                                    'product'               => $product,
                                    'idcombination'         => $request['idcombination'],
                                    'idproduct'             => $request['idproduct'],
                                ]);

                                DB::commit();
                                return response()->json([
                                    'status'  =>  true,
                                    'message' =>  'Registro Generado.'
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
                        return response()->json([
                            'status'    =>   true,
                            'message'   => 'Eliminación exitosa'
                        ], 200);
                    } else {
                        return response()->json([
                            'status'   =>  false,
                            'message'  => 'Error al eliminar'
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
    public function listStoreHousexManufacturingActivos($idManufacture)
    {
        $storehouse = StoreHouse::select('*')
            ->whereHas('products.typemanufacturing', function ($query) use ($idManufacture) {
                $query->where('id', $idManufacture);
            })
            ->where('enabled', '1')
            ->get();
        return response()->json($storehouse);
    }
    public function exportExcel($id)
    {
        return Excel::download(new StoreHouseExport($id), 'productosAlmacen.xlsx');
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
