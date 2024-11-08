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

    public function index()
    {
        $pettycash = pettycash::select('*')->get();

        return response()->json($pettycash);
    }

    public function store(Request $request)
    {
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
            if ($this->isDatabaseDown()) {
                if ($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['mantenimiento.'],
                        'message' => 'La aplicación está en mantenimiento. Inténtalo de nuevo más tarde.'
                    ], 503);
                } else {
                    $validator = Validator::make($request->input(),$rules,$messages);
                    if($validator->fails()){
                        return response()->json([
                            'status'    => false,
                            'alert'     => 'rules',
                            'errors'    => $validator->errors()->all()
                        ], 400);
                    } else {
                        try {

                            DB::beginTransaction();

                            $img_petty_cash_name = $request->input('img_petty_cash_name');
                            $img_petty_cash = $request->input('img_petty_cash');

                            $img_petty_cash_file = base64_decode($img_petty_cash);

                            Storage::disk('public')->put('img_petty_cash/' . $img_petty_cash_name, $img_petty_cash_file);

                            $pettycash = new PettyCash([
                                'description' => $request->input('description'),
                                'amount'      => $request->input('amount'),
                                'username'    => $request->input('username'),
                                'img_petty_cash_name'      => $img_petty_cash_name
                            ]);

                            $pettycash->save();

                            DB::commit();

                            return response()->json([
                                'status' => true,
                                'message' => 'Registro Generado.'
                            ], 200);
                        } catch (\Exception $e) {

                            DB::rollBack();

                            return response()->json([
                                'status' => false,
                                'errors' => ['Error al crear el registro.'],
                                'message' => 'Error: ' . $e->getMessage()
                            ], 500);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'errors' => ['No se pudo conectar a la base de datos'],
                'message' => 'Error: ' .  $e->getMessage()
            ], 500);
        }
    }

    public function show(PettyCash $pettycash)
    {
        if ($pettycash->img_petty_cash_name) {
            $pettycash->img_petty_cash_url = asset("storage/img_petty_cash/{$pettycash->img_petty_cash_name}");
        }

        return response()->json([
            'status' => true,
            'data'   => $pettycash
        ]);
    }

    public function update(Request $request, PettyCash $pettycash)
    {
        // Validación de las entradas
        $rules = [
            'description' => 'required|string',
            'amount' => 'required|numeric',
            'img_petty_cash_name' => 'required|string',
            'img_petty_cash' => 'nullable'
        ];

        $messages = [
            'description.string' => 'El campo descripción debe ser una cadena de texto',
            'amount.numeric' => 'El campo monto debe ser un número',
            'img_petty_cash_name.string' => 'El nombre de la imagen debe ser una cadena'
        ];

        try {
            if ($this->isDatabaseDown()) {
                if ($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['mantenimiento.'],
                        'message' => 'La aplicación está en mantenimiento. Inténtalo de nuevo más tarde.'
                    ], 503);
                } else {
                    $validator = Validator::make($request->all(), $rules, $messages);
                    if ($validator->fails()) {
                        return response()->json([
                            'status' => false,
                            'alert' => 'rules',
                            'errors' => $validator->errors()->all()
                        ], 400);
                    } else {
                        try {
                            
                            // Aquí estamos buscando el registro usando el id que debe estar presente en el modelo
                            // Asegúrate que el modelo contiene el id
                            $pettycashFound = PettyCash::find($pettycash->id); // Buscar el registro con el id


                            try {
                                DB::beginTransaction();
                                
                                $pettycashFound->description = $request->input('description');
                                $pettycashFound->amount = $request->input('amount');

                                // Verificar si se ha proporcionado una nueva imagen
                                if (!empty($request['img_petty_cash'])) {
                                    $imgpettycash = $request->input('img_petty_cash');
                                    $imgpettycashname = $request->input('img_petty_cash_name');
                            
                                    // Verificar si el nombre de la nueva imagen es diferente de la actual y eliminar la antigua
                                    if ($pettycashFound->img_petty_cash_name && $pettycashFound->img_petty_cash_name !== $imgpettycashname) {
                                        // Eliminar la imagen anterior de la carpeta de almacenamiento
                                        Storage::disk('public')->delete('img_petty_cash/' . $pettycashFound->img_petty_cash_name);
                                        
                                    }

                                    // Convertir el contenido base64 a datos binarios
                                    $img_petty_cash_file = base64_decode($imgpettycash);

                                    // Guardar la nueva imagen en archivos
                                    Storage::disk('public')->put('img_petty_cash/' . $imgpettycashname, $img_petty_cash_file);

                                    // Actualizar el nombre de la imagen en el modelo
                                    $pettycashFound->img_petty_cash_name = $imgpettycashname;
                                }

                                // Guardar los cambios
                                $pettycashFound->save();

                                DB::commit();

                                return response()->json([
                                    'status' => true,
                                    'message' => 'Actualización exitosa.'
                                ]);
                            } catch (\Exception $e) {
                                DB::rollBack();
                                return response()->json([
                                    'status' => false,
                                    'errors' => ['Error al intentar actualizar el registro'],
                                    'message' => 'Error: ' . $e->getMessage()
                                ], 400);
                            }
                        } catch (\Exception $e) {
                            return response()->json([
                                'status' => false,
                                'errors' => ['Error inesperado'],
                                'message' => 'Error: ' . $e->getMessage()
                            ], 500);
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



    public function destroy($id)
    {
        try {
            if ($this->isDatabaseDown()) {
                if ($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['mantenimiento.'],
                        'message' => 'La aplicación está en mantenimiento. Inténtalo de nuevo más tarde.'
                    ], 503);
                } else {
                    // Verificar si el registro existe
                    $record = PettyCash::find($id);
                    if (!$record) {
                        return response()->json([
                            'status' => false,
                            'message' => 'No se encontró el registro para eliminar.'
                        ], 404);
                    }

                    // Guardar el nombre del archivo antes de eliminar el registro
                    $fileName = $record->img_petty_cash_name; // Asumimos que esta es la columna que contiene el nombre del archivo

                    // Intentar eliminar el archivo
                    if ($fileName) {
                        $filePath = storage_path('app/public/img_petty_cash/' . $fileName);

                        // Verificar si el archivo existe antes de eliminarlo
                        if (file_exists($filePath)) {
                            unlink($filePath); // Eliminar archivo
                        } else {
                            return response()->json([
                                'status' => false,
                                'message' => 'Archivo no encontrado en el sistema.'
                            ], 404);
                        }
                    }

                    // Intentar eliminar el registro
                    $deleted = $record->delete(); // Eliminar el registro de la base de datos

                    if ($deleted) {
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
