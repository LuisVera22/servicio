<?php
namespace App\Http\Controllers;

use App\Interfaces\SubTypeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\SubType;

class SubTypeController
{
    private $repository;

    public function __construct(
        SubTypeRepositoryInterface $repository
    )
    {
        $this->repository = $repository;
    }

    public function index()
    {
        $subtype = SubType::with('type')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($subtype,200);
    }
    public function show(SubType $subtype)
    {
        $subtype->load('type');
        return response()->json(['status'  =>  true, 'data'    =>  $subtype],200);
    }
    public function store(Request $request)
    {
        $rules = [
            'description'         =>       'required|string',
            'abbreviation'         =>       'required|string',
        ];
        $messages = [
            'description.required'          =>  'El campo descripción es obligatorio.',
            'description.string'            =>  'El campo descripción debe ser una cadena de texto.',
            'abbreviation.required'          =>  'El campo abbreviation es obligatorio.',
            'abbreviation.string'            =>  'El campo abbreviation debe ser una cadena de texto.',
        ];

        try {
            if ($this->isDatabaseDown()) {
                if($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['mantenimiento'],
                        'message'=> 'La aplicación esta en mantenimiento. Inténtalo de nuevo más tarde.'
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
                        $repeatsubtype = $this->repository->repeatsubtype($request['description'],$request['abbreviation'],$request['idtype']);
                        if (count($repeatsubtype) > 0) {
                            return response()->json([
                                'status'   =>  true,
                                'message'  =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $subtype = new SubType($request->input());
                            $subtype->save();
                            return response()->json([
                                'status'   =>   true,
                                'message'  =>   'Registro Generado.'
                            ], 200);
                        }
                    }
                }
            }
        }catch (\Exception $e) {
            return response()->json([
                'status'    =>  false,
                'errors'    =>  ['No se pudo conectar a la base de datos'],
                'message'   =>  'Error: '   .$e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, SubType $subtype)
    {
        $rules = [
            'description'      =>   'required|string|',
            'abbreviation'      =>   'required|string|',
        ];
        $messages = [
            'description.required'          =>  'El campo descripción es obligatorio.',
            'description.string'            =>  'El campo descripción debe ser una cadena de texto.',
            'abbreviation.required'          =>  'El campo abbreviation es obligatorio.',
            'abbreviation.int'               =>  'El campo abbreviation debe ser una cadena de texto.',
        ];

        try {
            if($this->isDatabaseDown()) {
                if($this->isAppDownForMaintenance()) {
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
                        $repeatsubtype = $this->repository->repeatsubtypeupdate($request['description'],$request['abbreviation'],$request['idtype'], $subtype->id);
                        if (count($repeatsubtype) > 0) {
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $subtype->update($request->input());
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
                    if (isset($response) && $response ==1) {
                        return response() ->json([
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
        $subtype  =  SubType::select('id', 'description','idtype')
            ->with('type')
            ->where('enabled', '1')
            ->get();
        return response()->json($subtype);
    }
    public function listTypexSubtypeActivos($id)
    {
        $subtype  =  SubType::select('id', 'description')
            ->where('enabled', '1')
            ->where('idtype', $id)
            ->get();
        return response()->json($subtype);
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