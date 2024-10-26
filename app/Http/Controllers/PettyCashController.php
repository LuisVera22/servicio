<?php

use App\Models\pettycash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PettyCashController
{
    public function index(){
        $pettycash = pettycash::all();
        return response()->json($pettycash);
    }

    public function store(Request $request){
        /*Definición de reglas de validación*/
        $rules = [     
            'description' => 'required|text',
            'amount' => 'required|numeric|',
            'username' => 'required|string',
            'urlimg' => 'required|string',
        ];
        $messages = [
            'description.required' => 'El campo descripción es obligatorio.',
            'description.text' => 'El campo descripción debe ser una cadena de texto',
            'amount.required' => 'El campo monto es obligatorio.',
            'amount.numeric' => 'El campo monto debe ser un número',
            'urlimg.required' => 'El campo imagen es obligatorio.' 
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
                            /*Creación de la entrada en pettycash*/
                            $pettycash = new pettycash($request->only([
                                'description',
                                'amount',
                                'username',
                                'urlimg'
                            ]));
                            
                            /*
                            $pettycash = new PettyCash([
                                'description' => $request->input('description'),
                                'amount'      => $request->input('amount'),
                                'username'    => $request->input('username'),
                                'urlimg'      => $request->input('urlimg'),
                            ]);
                            */
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

    public function show(pettycash $pettycash){
        return response()->json(['status'=>true,'data'=>$pettycash]);
    }

    public function edit($id){
        
    }

    /*--------------------------¡Revisar este controlador!--------------------------------*/
    public function update(Request $request, pettycash $pettycash){
        /*¿Puede un usuario editar un registro que no es de su autoría?*/
        $rules = [     
            'description' => 'nullable|text',
            'amount' => 'nullable|numeric|',
            'urlimg' => 'nullable|string',
        ];
        $messages = [
            'description.text' => 'El campo descripción debe ser una cadena de texto',
            'amount.numeric' => 'El campo monto debe ser un número',
            'urlimg.string' => 'El campo urlimg no es valida'
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
                    $validator = Validator::make($request->input(),$rules,$messages);
                    if($validator->fails()){
                        return response()->json([
                            'status' => false,
                            'alert' => 'rules',
                            'errors' => $validator->errors()->all()
                        ],400);
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
                                $pettycash = pettycash::findOrFail($request->pettycash_id);

                                if(!$pettycash){
                                    return response()->json([
                                        'status'=>false,
                                        'errors'=>['Registro no encontrado']
                                    ],404);
                                } else{
                                    try{
                                        /*Iniciar una transacción*/
                                        DB::beginTransaction();

                                        /*Actualizar solo los campos enviados*/
                                        if($request->has('description')){
                                            $pettycash->description=$request->input('description');
                                        }
                                        if($request->has('amount')){
                                            $pettycash->amount = $request->input('amount');
                                        }
                                        if($request->has('urlimg')){
                                            $pettycash->amount = $request->input('urlimg');
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
            }
        } catch (\Exception $e){
            return response()->json([
                'status' => false,
                'errors' => ['No se pudo conectar a la base de datos'],
                'message' => 'Error: ' .  $e->getMessage()
            ], 500);
        }
    }

    public function destroy(pettycash $pettycash){
        $pettycash->update();
        return response()->json([
            'status' => true,
            'message' => 'Successfully deleted'
        ],200);
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