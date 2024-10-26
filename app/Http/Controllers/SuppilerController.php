<?php

namespace App\Http\Controllers;

use App\Interfaces\SuppilerRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Suppiler;

class SuppilerController
{
    private $repository;

    public function __construct(
        SuppilerRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }
    public function index()
    {
        $suppiler = Suppiler::select('*')
            ->orderBy('id','desc')
            ->get();
        return response()->json($suppiler);
    }
    public function show(Suppiler $suppiler)
    {
        return response()->json(['status'  =>  true, 'data'  =>  $suppiler]);
    }
    public function store(Request $request)
    {
        $rules = [
           'number_document'         =>  'required|string|',
           'bussinesname'            =>  'required|string|',
        ];
        $messages = [
           'number_document.required'      =>  'El campo RUC es obligatorio',
           'bussinesname.required'         =>  'El campo nombre de proveedor es obligatorio',
           'bussinesname.string'           =>  'El campo nombre de proveedor debe ser una cadena de texto',
        ];

        try {
            if ($this->isDatabaseDown()) {
                if ($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['mantenimiento.'],
                        'message' => 'La aplicaciòn esta en mantenimiento. Intèntalo de nuevo màs tarde.'
                    ], 503);
                } else {
                    $validator = Validator::make($request->input(), $rules, $messages);
                    if($validator->fails()) {
                        return response()->json([
                            'status'   => false,
                            'alert'    => 'rules',
                            'errors'   => $validator->errors()->all()
                        ], 400);
                    } else {
                        $repeatsuppiler = $this->repository->repeatsuppiler($request['number_document']);
                        if (count($repeatsuppiler)  > 0) {
                            return response()->json([
                                'status'   =>  true,
                                'message'  => 'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $suppiler = new Suppiler($request->input());
                            $suppiler->save();
                            return response()->json([
                                'status'   =>  true,
                                'message'  =>  'Registro Generado.'
                            ], 200);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'errors' => ['No se pudo conectar a la base de datos'],
                'message' => 'Error: '  . $e->getMessage()
            ],500);
        }

    }
    public function update(Request $request, Suppiler $suppiler)
    {
        $rules = [
           'number_document'         =>  'required|string|',
           'bussinesname'            =>  'required|string|',
        ];
        $messages = [
           'number_document.required'      =>  'El campo RUC es obligatorio',
           'bussinesname.required'         =>  'El campo nombre de proveedor es obligatorio',
           'bussinesname.string'           =>  'El campo nombre de proveedor debe ser una cadena de texto',
        ];

        try{
            if($this->isDatabaseDown()) {
                if($this->isAppDownForMaintenance()) {
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
                        $repeatsuppiler = $this->repository->repeatsuppilerupdate($request['number_document'],$suppiler->id);
                        if(count($repeatsuppiler) > 0) {
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $suppiler->update($request->input());
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
