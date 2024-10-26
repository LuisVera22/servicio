<?php

namespace App\Http\Controllers;

use App\Interfaces\CorrelativeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Correlative;

class CorrelativeController
{
    private $repository;

    public function __construct(
        CorrelativeRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function index()
    {
        $correlative = Correlative::with(['documentsales'])
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($correlative);
    }

    public function show(Correlative $correlative)
    {
        $correlative->load([
            'documentsales'
        ]);
        return response()->json(['status'   => true, 'data' => $correlative]);
    }
    public function store(Request $request)
    {
        $rules = [
            'serie'             =>  'required|string|',
            'correlative'       =>  'required|integer|',
            'iddocument_sales'  =>  'required'
        ];
        $messages = [
            'serie.required'            =>  'El campo serie es obligatorio.',
            'serie.string'              =>  'El campo serie debe ser una cadena de texto.',
            'correlative.required'      =>  'El campo correlativo es obligatorio.',
            'correlative.integer'       =>  'El campo correlativo debe ser numerico.',
            'iddocument_sales.required' =>  'El campo tipo documento es obligatorio.'
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
                        $repeatcorrelative = $this->repository->repeatcorrelative($request['iddocument_sales'],$request['serie'],$request['correlative']);
                        if (count($repeatcorrelative) > 0) {
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $correlative = new Correlative($request->input());
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
    public function update(Request $request, Correlative $correlative)
    {
        $rules = [
            'serie'             =>  'required|string|',
            'correlative'       =>  'required|integer|',
            'iddocument_sales'  =>  'required'
        ];
        $messages = [
            'serie.required'            =>  'El campo serie es obligatorio.',
            'serie.string'              =>  'El campo serie debe ser una cadena de texto.',
            'correlative.required'      =>  'El campo correlativo es obligatorio.',
            'correlative.integer'           =>  'El campo correlativo debe ser numerico.',
            'iddocument_sales.required' =>  'El campo tipo documento es obligatorio.'
        ];

        try {
            if($this->isDatabaseDown()){
                if($this->isAppDownForMaintenance()){
                    return response()->json([
                        'status' => false,
                        'errors' => ['mantenimiento.'],
                        'message' => 'La aplicación esta en mantenimiento. Inténtalo de nuevo más tarde.'
                    ], 503);
                }else{
                    $validator = Validator::make($request->input(), $rules, $messages);
                    if($validator->fails()){
                        return response()->json([
                            'status'    => false,
                            'alert'     => 'rules',
                            'errors'    => $validator->errors()->all()
                        ], 400);
                    }else{
                        $repeatcorrelative = $this->repository->repeatcorrelativeUpdate($request['iddocument_sales'],$request['serie'],$request['correlative'],$correlative->id);
                        if(count($repeatcorrelative)>0){
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        }else{
                            $correlative->update($request->input());
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'Actualización exitosa.'
                            ],200);
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
    public function destroy($id,Request $request)
    {
        try {
            if($this->isDatabaseDown()){
                if($this->isAppDownForMaintenance()){
                    return response()->json([
                        'status' => false,
                        'errors' => ['mantenimiento.'],
                        'message' => 'La aplicación esta en mantenimiento. Inténtalo de nuevo más tarde.'
                    ], 503);
                }else{
                    $response = $this->repository->delete($id,$request['enabled']);
                    
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
            // Intentar ejecutar una consulta de prueba
            DB::select('SELECT 1');

            return true;
        } catch (\Exception $e) {
            // Cualquier excepción, considerar que la base de datos está inaccesible
            return $e;
        }
    }
}
