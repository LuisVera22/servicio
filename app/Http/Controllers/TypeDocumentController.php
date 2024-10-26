<?php
namespace App\Http\Controllers;

use App\Interfaces\TypeDocumentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\TypeDocument;

class TypeDocumentController
{
    private $repository;

    public function __construct(
        TypeDocumentRepositoryInterface $repository
    )
    {
        $this->repository = $repository;
    }

    public function index()
    {
        $typedocument   = TypeDocument::select('*')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($typedocument);
    }
    public function show(TypeDocument $typedocument)
    {
        return response()->json(['status'   =>  true, 'data'    =>  $typedocument]);
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
                        $repeattypedocument = $this->repository->repeattypedocument($request['description']);
                        if (count($repeattypedocument) > 0) {
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $typedocument = new TypeDocument($request->input());
                            $typedocument->save();
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
    public function update(Request $request, TypeDocument $typedocument)
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
                        $repeattypedocument = $this->repository->repeattypedocumentupdate($request['description'], $typedocument->id);
                        if (count($repeattypedocument) > 0) {
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $typedocument->update($request->input());
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
        $typedocument   = TypeDocument::select('id', 'description')
            ->where('enabled', '=', "1")
            ->get();
        return response()->json($typedocument);
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
?>