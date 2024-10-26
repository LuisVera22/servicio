<?php

namespace App\Http\Controllers;

use App\Interfaces\ClientRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Client;

class ClientController
{
    private $repository;

    public function __construct(
        ClientRepositoryInterface $repository
    )
    {
        $this->repository = $repository;
    }
    public function index()
    {
        $client = Client::with(['typedocument'])
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($client);
    }
    
    public function show(Client $client){
        $client->load([
            'typedocument',
        ]);
        return response()->json(['status'   => true, 'data' => $client]);
    }   
    public function store(Request $request)
    {
        $rules = [
            'idtype_document'   =>  'required',
            'number_document'   =>  'required|string|'
        ];
        $messages = [
            'number_document.required'  =>  'El campo número documento es obligatorio.',
            'number_document.string'    =>  'El campo número documento debe ser una cadena de texto.',
            'idtype_document.required'  =>  'El campo tipo documento es obligatorio.'            
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
                    $validator = Validator::make($request->input(),$rules,$messages);
                    if($validator->fails()){
                        return response()->json([
                            'status'    => false,
                            'alert'     => 'rules',
                            'errors'    => $validator->errors()->all()
                        ], 400);
                    }else{
                        $repeatcliente = $this->repository->repeatclient($request['number_document']);
                        if(count($repeatcliente)>0){
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El documento ya se encuentra registrado.'
                            ], 409);
                        }else{
                            $client = new Client($request->input());
                            $client->save();
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
    public function update(Request $request, Client $client)
    {
        $rules = [
            'idtype_document'   =>  'required',
            'number_document'   =>  'required|string|'
        ];
        $messages = [
            'number_document.required'  =>  'El campo número documento es obligatorio.',
            'number_document.string'    =>  'El campo número documento debe ser una cadena de texto.',
            'idtype_document.required'  =>  'El campo tipo documento es obligatorio.'            
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
                    $validator = Validator::make($request->input(),$rules,$messages);
                    if($validator->fails()){
                        return response()->json([
                            'status'    => false,
                            'alert'     => 'rules',
                            'errors'    => $validator->errors()->all()
                        ], 400);
                    }else{
                        $repeatcliente = $this->repository->repeatclientUpdate($request['number_document'],$client->id);
                        if(count($repeatcliente)>0){
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El documento ya se encuentra registrado.'
                            ], 409);
                        }else{
                            $client->update($request->input());                            
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
    public function destroy($id,Request $request)
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
        $clients = Client::select('*')
            ->where('enabled', "1")
            ->get();
        return response()->json($clients);
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
