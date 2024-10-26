<?php

namespace App\Http\Controllers;

use App\Interfaces\DocumentSalesRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\DocumentSales;
use League\CommonMark\Node\Block\Document;

class DocumentSalesController
{
    private $repository;

    public function __construct(
        DocumentSalesRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function index()
    {
        $documentsales   = DocumentSales::select('*')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($documentsales);
    }
    public function show(DocumentSales $documentsale)
    {
        return response()->json(['status'   =>  true, 'data'    =>  $documentsale]);
    }
    public function store(Request $request)
    {
        $rules = [
            'description'           =>  'required|string|',
            'code_sunat'            =>  'required|int|',
        ];
        $messages = [
            'description.required'          =>  'El campo descripción es obligatorio.',
            'description.string'            =>  'El campo descripción debe ser una cadena de texto.',
            'code_sunat.required'           =>  'El campo code_sunat es obligatorio.',
            'code_sunat.int'                =>  'El campo code_sunat debe ser un número.',
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
                        $repeatdocumensales = $this->repository->repeatdocumentsales($request['description']);
                        if (count($repeatdocumensales) > 0) {
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $documentsales = new DocumentSales($request->input());
                            $documentsales->save();
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
    public function update(Request $request, DocumentSales $documentsale)
    {
        $rules = [
            'description'           =>  'required|string|',
            'code_sunat'            =>  'required|int|',
        ];
        $messages = [
            'description.required'          =>  'El campo descripción es obligatorio.',
            'description.string'            =>  'El campo descripción debe ser una cadena de texto.',
            'code_sunat.required'           =>  'El campo code_sunat es obligatorio.',
            'code_sunat.int'                =>  'El campo code_sunat debe ser un número.',
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
                        $repeatdocumentsales = $this->repository->repeatdocumentsalesupdate($request['description'], $documentsale->id);
                        if (count($repeatdocumentsales) > 0) {
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $documentsale->update($request->input());
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
    public function listAllActivos()
    {
        $documentsales   = DocumentSales::select('id', 'description')
            ->where('enabled', '=', "1")
            ->get();
        return response()->json($documentsales);
    }
    public function DocumentSalesxCorrelative($Id)
    {
        $documentsale = DocumentSales::find($Id);
        $documentsale->load([
            'correlative' => function ($query){
                $query->where('enabled',1);
            }
        ]);
        if ($documentsale->correlative) {
            $documentsale->correlative->makeHidden(['correlative', 'enabled', 'created_at', 'updated_at']);
        }
        return response()->json(['status'   => true, 'data' => $documentsale]);
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
