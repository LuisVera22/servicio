<?php

namespace App\Http\Controllers;

use App\Interfaces\TypeRepositoryInterface;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Type;

class TypeController
{
    private $repository;

    public function __construct(
        TypeRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function index()
    {
        $type = Type::with('typemanufacturing')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($type, 200);
    }

    public function show(Type $type)
    {
        $type->load('typemanufacturing');
        return response()->json(['status'   => true, 'data' => $type], 200);
    }

    public function store(Request $request)
    {
        $rules = [
            'description'      =>   'required|string|',
            'abbreviation'     =>   'required|string|',
        ];

        $messages = [
            'description.required'          =>  'El campo descripción es obligatorio.',
            'description.string'            =>  'El campo descripción debe ser una cadena de texto.',
            'abbreviation.required'         =>  'El campo abreviación es obligatorio.',
            'abbreviation.string'           =>  'El campo abreviación debe ser una cadena de texto.',
        ];

        try {
            if ($this->isDatabaseDown()) {
                if ($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status'  => false,
                        'errors'  => ['mantenimiento.'],
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
                        $repeattype = $this->repository->repeattype($request['description'], $request['abbreviation'], $request['idtypemanufacturing']);
                        if (count($repeattype) > 0) {
                            return response()->json([
                                'status'   =>  true,
                                'message'  =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $subtype = new Type($request->input());
                            $subtype->save();
                            return response()->json([
                                'status'   =>   true,
                                'message'  =>   'Registro Generado.'
                            ], 200);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  =>  false,
                'errors'  =>  ['No se pudo conectar a la base de datos'],
                'message' => 'Error: '  . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Type $type)
    {
        $rules = [
            'description'      =>   'required|string|',
            'abbreviation'      =>   'required|string|',
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
                        'status'  => false,
                        'errors'  => ['mantenimiento.'],
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
                        $repeattype = $this->repository->repeattypeupdate($request['description'], $request['abbreviation'], $request['idtypemanufacturing'], $type->id);
                        if (count($repeattype) > 0) {
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $type->update($request->input());
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
                'status'  =>  false,
                'errors'  =>  ['No se pudo conectar a la base de datos'],
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

    public function listAllActivos()
    {
        $type  =  Type::select('id', 'description', 'idtype_manufacturing')
            ->with('typemanufacturing')
            ->where('enabled', '1')
            ->get();
        return response()->json($type, 200);
    }

    public function listManufxTipoActivos($id)
    {
        $type  =  Type::select('id', 'description', 'idtype_manufacturing')
            ->with('typemanufacturing')
            ->where([['enabled', '1'], ['idtype_manufacturing', $id]])
            ->get();
        return response()->json($type, 200);
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
