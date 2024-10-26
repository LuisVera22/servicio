<?php

namespace App\Http\Controllers;

use App\Interfaces\SubClassesRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\SubClasses;

class SubClassesController
{
    private $repository;

    public function __construct(
        SubClassesRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function index()
    {
        $subclasses  = SubClasses::with('classes')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($subclasses, 200);
    }
    public function show(SubClasses $subclass)
    {
        $subclass->load('classes');
        return response()->json(['status'  =>  true, 'data'    =>  $subclass], 200);
    }
    public function store(Request $request)
    {
        $rules = [
            'description'         =>       'required|string',
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
                        $repeatsubclasses = $this->repository->repeatsubclasses($request['description'],$request['idclasses']);
                        if (count($repeatsubclasses) > 0) {
                            return response()->json([
                                'status'   =>  true,
                                'message'  =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $subclasses = new SubClasses($request->input());
                            $subclasses->save();
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
    public function update(Request $request, SubClasses $subclass)
    {
        $rules = [
            'description'      =>   'required|string|'
        ];
        $messages = [
            'description.required'          =>  'El campo descripción es obligatorio.',
            'description.string'            =>  'El campo descripción debe ser una cadena de texto.'
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
                        $repeatsubclasses = $this->repository->repeatsubclassesupdate($request['description'],$request['idclasses'], $subclass->id);
                        if (count($repeatsubclasses) > 0) {
                            return response()->json([
                                'status'     =>  true,
                                'message'    => 'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $subclass->update($request->input());
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
        $subclasses   =  SubClasses::select('id', 'description')
            ->where('enabled', "1")
            ->get();
        return response()->json($subclasses);
    }
    public function listSubClassexClasseActivos($id)
    {
        $subclasses   =  SubClasses::select('id', 'description','idclasses')
            ->with('classes')
            ->where([['enabled', "1"],['idclasses',$id]])
            ->get();
        return response()->json($subclasses);
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
