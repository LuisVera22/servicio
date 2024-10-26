<?php

namespace App\Http\Controllers;

use App\Interfaces\MaterialsRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Materials;

class MaterialsController
{
    private $repository;

    public function __construct(
        MaterialsRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function index()
    {
        $material  = Materials::with(['type', 'subtype'])
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($material, 200);
    }
    public function show(Materials $material)
    {
        $material->load(['type', 'subtype']);
        return response()->json(['status'  =>  true, 'data'    =>  $material], 200);
    }
    public function store(Request $request)
    {
        $rules = [
            'description'         =>       'required|string',
            'abbreviation'         =>       'required|string',
        ];
        $messages = [
            'description.required'          =>  'El campo descripción es obligatorio.',
            'description.string'            =>  'El campo descripción debe ser una cadena de texto.',
            'abbreviation.required'          =>  'El campo abreviación es obligatorio.',
            'abbreviation.string'            =>  'El campo abreviación debe ser una cadena de texto.',
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
                        $repeatmaterial = $this->repository->repeatmaterial($request['description'], $request['abbreviation'], $request['idtype'], $request['idsubtype']);
                        if (count($repeatmaterial) > 0) {
                            return response()->json([
                                'status'   =>  true,
                                'message'  =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $material = new Materials($request->input());
                            $material->save();
                            return response()->json([
                                'status'  =>  true,
                                'message' =>  'Registro Generado.'
                            ], 200);
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
    public function update(Request $request, Materials $material)
    {
        $rules = [
            'description'      =>   'required|string|',
            'abbreviation'      =>   'required|string|',
        ];
        $messages = [
            'description.required'          =>  'El campo descripción es obligatorio.',
            'description.string'            =>  'El campo descripción debe ser una cadena de texto.',
            'abbreviation.required'           =>  'El campo abbreviation es obligatorio.',
            'abbreviation.int'                =>  'El campo abbreviation debe ser un número.',
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
                            'status'    =>  false,
                            'alert'     => 'rules',
                            'errors'    => $validator->errors()->all()
                        ], 400);
                    } else {
                        $repeatmaterial = $this->repository->repeatmaterialupdate($request['description'], $request['abbreviation'], $request['idtype'], $request['idsubtype'], $material->id);
                        if (count($repeatmaterial) > 0) {
                            return response()->json([
                                'status'     =>  true,
                                'message'    => 'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $material->update($request->input());
                            return response()->json([
                                'status'    =>  true,
                                'message'   => 'Actualización exitosa.'
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
                            'status'      =>   true,
                            'message'     =>   'Eliminación exitosa'
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
                'status'  => false,
                'errors'  => ['No se pudo conectar a la base de datos'],
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function listAllActivos()
    {
        $material   =  Materials::select('id', 'description', 'idtype', 'idsubtype')
            ->with(['type', 'subtype'])
            ->where('enabled', '=', "1")
            ->get();
        return response()->json($material);
    }
    public function listTypexMaterialActivos($id)
    {
        $material   =  Materials::select('id', 'description', 'idtype')
            ->with(['type'])
            ->where([['enabled', "1"], ['idtype', $id]])
            ->get();
        return response()->json($material);
    }
    public function listSubTypexMaterialActivos($id)
    {
        $material   =  Materials::select('id', 'description', 'idsubtype')
            ->with(['subtype'])
            ->where([['enabled', "1"], ['idsubtype', $id]])
            ->get();
        return response()->json($material);
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
