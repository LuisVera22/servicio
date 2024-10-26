<?php

namespace App\Http\Controllers;

use App\Interfaces\BusinessRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\Business;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BusinessController
{
    private $repository;

    public function __construct(
        BusinessRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function index()
    {
        $business = Business::select('*')->get();

         // Añadir la ruta completa de la imagen al objeto
         $business = $business->map(function ($item) {
            $item['img_logo_empresa_url'] = $item['img_logo_empresa_name']
                ? asset("storage/img_logo_empresa/{$item['img_logo_empresa_name']}")
                : null;
    
            return $item;
        });

        return response()->json($business);
    }
    public function store(Request $request)
    {
        $rules = [
            'ruc'           =>  'required|string|min:11',
            'razon_social'  =>  'required|string',
        ];

        $messages = [
            'ruc.required'          =>  'El campo ruc es obligatorio.',
            'ruc.string'            =>  'El campo ruc debe ser una cadena de texto.',
            'ruc.min'               =>  'El campo ruc debe tener al menos 11 caracteres.',
            'razon_social.required' =>  'El campo razón social es obligatorio.',
            'razon_social.string'   =>  'El campo razón social debe ser una cadena de texto.',
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
                        $business = new Business($request->input());
                        $business->save();
                        return response()->json([
                            'status'    =>  true,
                            'message'   =>  'Registro Generado.'
                        ], 200);
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

    public function show(Business $business)
    {
        return response()->json(['status'   =>  true, 'data'    =>  $business]);
    }

    public function update(Request $request, Business $business)
    {
        $rules = [
            'ruc'           =>  'required|string|min:11|max:11',
            'razon_social'  =>  'required|string',
        ];

        $messages = [
            'ruc.required'          =>  'El campo ruc es obligatorio.',
            'ruc.string'            =>  'El campo ruc debe ser una cadena de texto.',
            'ruc.min'               =>  'El campo ruc debe tener al menos 11 caracteres.',
            'ruc.max'               =>  'El campo ruc debe tener al menos 11 caracteres.',
            'razon_social.required' =>  'El campo razón social es obligatorio.',
            'razon_social.string'   =>  'El campo razón social debe ser una cadena de texto.',
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
                        $business->update($request->input());
                        return response()->json([
                            'status'    =>  true,
                            'message'   =>  'Actualización exitosa.'
                        ], 200);
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
    public function updateImage(Request $request)
    {
        $imgLogoName = $request->input('img_logo_empresa_name');
        $imgLogoContent = $request->input('img_logo_empresa');

        // Convertir el contenido base64 a datos binarios
        $imgLogoFile = base64_decode($imgLogoContent);

        try {
            if ($this->isDatabaseDown()) {
                if ($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['mantenimiento.'],
                        'message' => 'La aplicación esta en mantenimiento. Inténtalo de nuevo más tarde.'
                    ], 503);
                } else {
                    $response = $this->repository->actualizarImagen($request);
                    if ($response == 1 || $response == 0) {
                        Storage::disk('public')->put('img_logo_empresa/' . $imgLogoName, $imgLogoFile);
                        return response()->json([
                            'status'    =>  true,
                            'message'   =>  'Actualización Imagen exitosa'
                        ], 200);
                    } else {
                        if(isset($response)){
                            return response()->json([
                                'status'    =>  false,
                                'message'   =>  'Existe problemas al actualizar la imagen'
                            ], 505);
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
