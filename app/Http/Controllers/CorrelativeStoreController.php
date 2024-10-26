<?php

namespace App\Http\Controllers;

use App\Interfaces\CorrelativeStoreRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\CorrelativeStore;

class CorrelativeStoreController
{
    private $repository;

    public function __construct(
        CorrelativeStoreRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function show($id)
    {
        $correlativestores = CorrelativeStore::with(['correlative'])
            ->where('idstore', $id)
            ->get();
        $correlativestores->each(function ($correlativestore){
            $correlativestore->correlative->makeHidden(['enabled', 'created_at', 'updated_at']);            
        });
        return response()->json($correlativestores);
    }

    public function store(Request $request)
    {
        $rules = [
            'idcorrelative' => 'required|integer|'
        ];
        $messages = [
            'idcorrelative.required'    =>  'El campo serie es obligatorio.',
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
                        $repeatcorrelativestore = $this->repository->repeatcorrelativestore($request['idcorrelative'], $request['idstore']);
                        if (count($repeatcorrelativestore) > 0) {
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $correlative = new CorrelativeStore($request->input());
                            $correlative->save();
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'Registro Generado.'
                            ], 200);
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
