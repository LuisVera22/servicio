<?php

namespace App\Http\Controllers;

use App\Interfaces\WarehouseReceptionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\MainWarehouseReception;
use App\Models\Quotation;

class MainWarehouseReceptionController
{
    public function store(Request $request)
    {
        try {
            if ($this->isDatabaseDown()) {
                if ($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status'    => false,
                        'errors'    => ['mantenimiento'],
                        'message'   => 'La aplicación esta en mantenimiento. Inténtalo de nuevo más tarde.'
                    ], 503);
                } else {
                    DB::beginTransaction();
                    try {

                        $ids = array_map(function ($item) {
                            return $item['idquotation'];
                        }, $request['quotation']);

                        $changedCodes = Quotation::whereIn('id', $ids)->where('status_rbcalmacen', 1)->pluck('codigo');

                        if ($changedCodes->isNotEmpty()) {
                            DB::rollBack();
                            // Crear un mensaje con los IDs que ya fueron cambiados
                            $changedCodesList = implode(', ', $changedCodes->toArray());

                            return response()->json([
                                'message' => "Los siguientes códigos de orden ya habían sido enviados: $changedCodesList.",
                                'alert'  =>  'info',
                                'status' => true
                            ], 200);

                        } else {
                            $dataToInsert = array_map(function ($item) use ($request) {
                                return [
                                    'dateshipping'  => $request['dateshipping'],
                                    'date'          => $request['date'],
                                    'idquotation'   => $item['idquotation']
                                ];
                            }, $request['quotation']);

                            // Insertar todos los registros de una sola vez
                            MainWarehouseReception::insert($dataToInsert);



                            // Actualizar la tabla Quotation usando los IDs recolectados
                            Quotation::whereIn('id', $ids)->update(['status_rbcalmacen' => 1]);

                            DB::commit();
                            return response()->json([
                                'status'  =>  true,
                                'message' =>  'Registro Generado.'
                            ], 201);
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
