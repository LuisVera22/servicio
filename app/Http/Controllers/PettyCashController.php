<?php

namespace App\Http\Controllers;

use App\Interfaces\PettyCashRepositoryInterface;
use App\Models\pettycash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PettycashController
{

    private $repository;
    private function _construct(
        PettyCashRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function index(){
        $pettycash = pettycash::select('*')->get();

        $pettycash = $pettycash->map(function ($item) {
            $item['img_petty_cash_url'] =  $item['img_petty_cash_name']
                ? asset("storage/img_petty_cash/{$item['img_petty_cash_name']}")
                : null;
            return $item;
        });

        return response()->json($pettycash);
    }

    public function store(Request $request){
        /*Definición de reglas de validación*/
        $rules = [     
            'description' => 'required|string',
            'amount' => 'required|numeric',
            'username' => 'required|string',
            'img_petty_cash_name' => 'required|string',
            'img_petty_cash' => 'required'
        ];
        $messages = [
            'description.required' => 'El campo descripción es obligatorio.',
            'description.string' => 'El campo descripción debe ser una cadena de texto',
            'amount.required' => 'El campo monto es obligatorio.',
            'amount.numeric' => 'El campo monto debe ser un número',
            'username.required' => 'El campo usuario es obligatorio',
            'img_petty_cash_name.required' => 'El campo de la imagen es obligatorio',
            'img_petty_cash.required' => 'El contenido de la imagen es obligatorio'
        ];

        try {
            /*Verificación de la base de datos*/
            if($this->isDatabaseDow()){
                if($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['mantenimiento.'],
                        'message' => 'La aplicación está en mantenimiento. Inténtalo de nuevo más tarde.'
                    ],503);
                } else {
                    /*Validación de la solicitud*/
                    $validator = Validator::make($request->input(),$rules,$messages);
                    if($validator->fails()) {
                        return response()->json([
                            'status' => false,
                            'alert' => 'rules',
                            'errors' => $validator->errors()->all()
                        ], 400);
                    } else {
                       /*Intentar la transacción*/
                        try {   
        
                            DB::beginTransaction();
                            
                            /*Obtener los datos de imagen*/
                            $img_petty_cash_name = $request->input('img_petty_cash_name');
                            $img_petty_cash = $request->input('img_petty_cash');

                            $img_petty_cash_file = base64_decode($img_petty_cash);

                            Storage::disk('public')->put('img_petty_cash/'.$img_petty_cash_name, $img_petty_cash_file);

                            $pettycash = new PettyCash([
                                'description' => $request->input('description'),
                                'amount'      => $request->input('amount'),
                                'username'    => $request->input('username'),
                                'img_petty_cash_name'      => $img_petty_cash_name
                            ]);
                            
                            $pettycash->save();

                            /*Confirmación de la transacción */
                            DB::commit();

                            return response()->json([
                                'satus' => true,
                                'message' => 'Registro Generado.'
                            ],200);

                        } catch (\Exception $e){
                            /*Revertir la transación en caso de error */
                            DB::rollBack();

                            return response()->json([
                                'status' => false,
                                'errors' => ['Error al crear el registro.'],
                                'message' => 'Error: ' . $e->getMessage()
                            ],500);
                        }
                    }
                }
            }
        } catch (\Exception $e){
            return response()->json([
                'status' => false,
                'errors' => ['No se pudo conectar a la base de datos'],
                'message' => 'Error: ' .  $e->getMessage()
            ], 500);
        }
    }

    public function show(PettyCash $pettycash){
        return response()->json(['status'=>true,'data'=>$pettycash]);
    }

    public function update(Request $request, $id){
        $rules = [     
            'description' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'img_petty_cash_name' => 'nullable|string',
            'img_petty_cash' => 'nullable'
        ];
        $messages = [
            'description.string' => 'El campo descripción debe ser una cadena de texto',
            'amount.numeric' => 'El campo monto debe ser un número',
            'img_petty_cash_name.string' => 'El nombre de la imgen debe ser una cadena'
        ];

        try {
            if($this->isDatabaseDow()){
                if($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['mantenimiento.'],
                        'message' => 'La aplicación está en mantenimiento. Inténtalo de nuevo más tarde.'
                    ],503);
                } else {
                        $validator = Validator::make($request->all(), $rules, $messages);
                        if($validator->fails()){
                            return response()->json([
                                'status' => false,
                                'alert' => 'rules',
                                'errors' => $validator->errors()->all()
                            ],400);
                        } else{
                            try {
                                /*Buscar el registro por el id*/
                                $pettycash = PettyCash::find($id);

                                if(!$pettycash){
                                    return response()->json([
                                        'status'=>false,
                                        'errors'=>['Registro no encontrado']
                                    ],404);
                                } else{
                                    try{
                                        DB::beginTransaction();

                                        /*Actualizar solo los campos enviados*/
                                        if ($request->has('description')) {
                                            $pettycash->description = $request->input('description');
                                        }
                                        if ($request->has('amount')) {
                                            $pettycash->amount = $request->input('amount');
                                        }
                                        if ($request->has('img_petty_cash') && !empty($request->input('img_petty_cash'))){
                                            /*Obtener el contenido de la imagen*/
                                            $img_petty_cash = $request->input('img_petty_cash');
                                            $img_petty_cash_name = $request->input('img_petty_cash_name');

                                            /*Convertir el contenido base64 a datos binarios*/
                                            $img_petty_cash_file = base64_decode($img_petty_cash);

                                            /*Guardar la imagen en archivos*/
                                            Storage::disk('public')->put('img_petty_cash/'.$img_petty_cash_name, $img_petty_cash_file);

                                            /*Actualizar el nombre de la imagen*/
                                            $pettycash->img_petty_cash_name = $img_petty_cash_name;
                                        }
                                    
                                        $pettycash->save();

                                        DB::commit();
                                    
                                        return response()->json([
                                            'status' => true,
                                            'message' => 'Actualización exitosa.'
                                        ]);
                                    } catch (\Exception $e){
                                        DB::rollBack();
                                        return response()->json([
                                            'status' => false,
                                            'errors' => ['Error al intentar actualizar el registro'],
                                            'message' => 'Error: ' . $e->getMessage()
                                        ],400);
                                    }
                                }
                            } catch(\Exception $e){

                            }
                        }
                }
            }
        } catch (\Exception $e){
            return response()->json([
                'status' => false,
                'errors' => ['No se pudo conectar a la base de datos'],
                'message' => 'Error: ' .  $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id){
        try {
            if ($this-> isDatabaseDow()){
                if($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['mantenimiento.'],
                        'message' => 'La aplicación esta en mantenimiento. Inténtalo de nuevo más tarde.'
                    ], 503);
                } else {

                     // Verificar si el registro existe
                $exists = PettyCash::where('id', $id)->exists();
                if (!$exists) {
                    return response()->json([
                        'status' => false,
                        'message' => 'No se encontró el registro para eliminar.'
                    ], 404);
                }

                // Intentar eliminar el registro
                $deleted = PettyCash::where('id', $id)->delete();

                if ($deleted > 0) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Registro eliminado exitosamente.'
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Error inesperado al eliminar el registro.'
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

    private function isAppDownForMaintenance(){
        return app()->isDownForMaintenance();
    }

    private function isDatabaseDow(){
        try{
            DB::select('SELECT 1');
            return true;
        } catch(\Exception $e){
            return $e;
        }
    }
}