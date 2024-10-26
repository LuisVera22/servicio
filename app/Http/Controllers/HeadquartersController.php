<?php

namespace App\Http\Controllers;

use App\Interfaces\HeadquartersRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Headquarters;

class HeadquartersController
{
    private $repository;

    public function __construct(
        HeadquartersRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }
    public function index()
    {
        $headquarters   = Headquarters::select('*')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($headquarters);
    }
    public function show(Headquarters $headquarters)
    {
        return response()->json(['status'   =>  true, 'data'    =>  $headquarters]);
    }
    public function store(Request $request)
    {
        $rules = [
            'description'           =>  'required|string|',
        ];
        $messages = [
            'description.required'          =>  'El campo descripción es obligatorio.',
            'description.string'            =>  'El campo descripción debe ser una cadena de texto.',
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
                        $repeatheadquarters = $this->repository->repeatheadquarters($request['description']);
                        if (count($repeatheadquarters) > 0) {
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $headquarters = new Headquarters($request->input());
                            $headquarters->save();
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
    public function update(Request $request, Headquarters $headquarters)
    {
        $rules = [
            'description'           =>  'required|string|',
        ];
        $messages = [
            'description.required'          =>  'El campo descripción es obligatorio.',
            'description.string'            =>  'El campo descripción debe ser una cadena de texto.',
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
                        $repeatheadquartersupdate = $this->repository->repeatheadquartersupdate($request['description'], $headquarters->id);
                        if (count($repeatheadquartersupdate) > 0) {
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $headquarters->update($request->input());
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'Actualización exitosa.'
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
        $headquarters   = Headquarters::select('id', 'description')
            ->where('enabled', '=', "1")
            ->get();
        return response()->json($headquarters);
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
