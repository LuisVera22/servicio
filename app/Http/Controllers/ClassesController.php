<?php

namespace App\Http\Controllers;

use App\Interfaces\ClassesRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Classes;

class ClassesController
{
    private $repository;

    public function __construct(
        ClassesRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function index()
    {
        $class  = Classes::with('materials')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($class);
    }
    public function show(Classes $class)
    {
        $class->load('materials');
        return response()->json(['status'  =>  true, 'data'    =>  $class]);
        /* return $class = Classes::with('subclasses')->find('1'); */
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
                        $repeatclasses = $this->repository->repeatclasses($request['description'], $request['abbreviation'], $request['idmaterial']);
                        if (count($repeatclasses) > 0) {
                            return response()->json([
                                'status'   =>  true,
                                'message'  =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $classes = new Classes($request->input());
                            $classes->save();
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
    public function update(Request $request, Classes $class)
    {
        $rules = [
            'description'      =>   'required|string|',
            'abbreviation'      =>   'required|string|',
        ];
        $messages = [
            'description.required'          =>  'El campo descripción es obligatorio.',
            'description.string'            =>  'El campo descripción debe ser una cadena de texto.',
            'abbreviation.required'          =>  'El campo abrevacion es obligatorio.',
            'abbreviation.string'            =>  'El campo abrevacion debe ser una cadena de texto.',
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
                        $repeatclasses = $this->repository->repeatclassesupdate($request['description'], $request['abbreviation'], $request['idmaterial'], $class->id);
                        if (count($repeatclasses) > 0) {
                            return response()->json([
                                'status'     =>  true,
                                'message'    => 'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $class->update($request->input());
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
        $classes   =  Classes::select('id', 'description', 'idmaterial')
            ->with('materials')
            ->where('enabled', "1")
            ->get();
        return response()->json($classes);
    }
    public function listClassexMaterialActivos($id)
    {
        $classes   =  Classes::select('id', 'description', 'idmaterial')
            ->with('materials')
            ->where([['enabled', "1"], ['idmaterial', $id]])
            ->get();
        return response()->json($classes);
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
?>