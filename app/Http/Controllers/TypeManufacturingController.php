<?php

namespace App\Http\Controllers;

use App\Interfaces\TypeManufacturingRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\TypeManufacturing;

class TypeManufacturingController
{
    private $repository;

    public function __construct(
        TypeManufacturingRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function index()
    {
        $typemanufacturing   = TypeManufacturing::select('*')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($typemanufacturing);
    }
    public function show(TypeManufacturing $typemanufacture)
    {
        return response()->json(['status'   =>  true, 'data'    =>  $typemanufacture]);
    }
    public function store(Request $request)
    {
        $rules = [
            'description'           =>  'required|string|',
            'abbreviation'          =>  'required|string|',
        ];
        $messages = [
            'description.required'          =>  'El campo descripción es obligatorio.',
            'description.string'            =>  'El campo descripción debe ser una cadena de texto.',
            'abbreviation.required'         =>  'El campo abreviación es obligatorio.',
            'abbreviation.string'           =>  'El campo abreviación debe ser una cadena de texto.'
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
                        $repeattypemanufacturing = $this->repository->repeattypeManufacturing($request['description'], $request['abbreviation']);
                        if (count($repeattypemanufacturing) > 0) {
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $typemanufacturing = new TypeManufacturing($request->input());
                            $typemanufacturing->save();
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

    public function destroy($id, Request $request)
    {
        try {
            if ($this->isDatabaseDown()) {
                if ($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['mantenimiento.'],
                        'message' => 'La aplicación esta en mantenimiento. Inténtalo de nuevo más tarde.'
                    ], 503);
                } else {
                    $response = $this->repository->delete($id, $request['enabled']);
                    if (isset($response) && $response == 1) {
                        return response()->json([
                            'status'    =>  true,
                            'message'   =>  'Eliminación exitosa'
                        ], 200);
                    } else {
                        return response()->json([
                            'status'    =>  false,
                            'message'   =>  'Error al eliminar'
                        ], 500);
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
    public function listAllActivos()
    {
        $typemanufacturing   = TypeManufacturing::select('id', 'description','abbreviation')
            ->where('enabled', "1")
            ->get();
        return response()->json($typemanufacturing);
    }
    public function listManufacturingxJobActivos($job)
    {
        $typemanufacturing   = TypeManufacturing::select('id', 'description', 'job')
            ->where([['enabled', "1"], ['job', $job]])
            ->orderBy('description', 'desc')
            ->get();
        return response()->json($typemanufacturing);
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
