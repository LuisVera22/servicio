<?php

namespace App\Http\Controllers;

use App\Interfaces\StoreRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Store;

class StoreController
{
    private $repository;

    public function __construct(
        StoreRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }
    public function index()
    {
        $store = Store::with('headquarters')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($store);
    }
    public function show(Store $store)
    {
        $store->load('headquarters');
        return response()->json(['status'   =>  true, 'data'    =>  $store]);
    }
    public function store(Request $request)
    {
        $rules = [
            'address'           =>  'required|string|',
            'idheadquarter'     =>  'required',
            'idbusiness'        =>  'required',
        ];
        $messages = [
            'address.required'          =>  'El campo dirección es obligatorio.',
            'address.string'            =>  'El campo dirección debe ser una cadena de texto.',
            'idheadquarter.required'    =>  'El campo sede es obligatorio.',
            'idbusiness.required'       =>  'El empresa es obligatorio.'
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
                        $repeatstore = $this->repository->repeatstore($request['idheadquarter'], $request['address']);
                        if (count($repeatstore) > 0) {
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $main = $this->repository->mainstoreexists();
                            if (count($main) > 0) {
                                return response()->json([
                                    'status'    =>  true,
                                    'message'   =>  'Ya existe un local asignado como sede principal.'
                                ], 409);
                            } else {
                                $store = new Store($request->input());
                                $store->save();
                                return response()->json([
                                    'status'    =>  true,
                                    'message'   =>  'Registro Generado.'
                                ], 200);
                            }
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
    public function update(Request $request, Store $store)
    {
        $rules = [
            'address'           =>  'required|string|',
            'idheadquarter'     =>  'required',
        ];
        $messages = [
            'address.required'  =>  'El campo dirección es obligatorio.',
            'address.string'    =>  'El campo dirección debe ser una cadena de texto.',
            'idheadquarter'     =>  'El campo sede es obligatorio.',
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
                        $repeatstore = $this->repository->repeatstoreUpdate($request['idheadquarter'], $request['address'], $store->id);
                        if (count($repeatstore) > 0) {
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El valor ya existe en la base de datos.'
                            ], 409);
                        } else {
                            $main = $this->repository->mainstoreexistsUpdate($store->id);
                            if (count($main) > 0) {
                                return response()->json([
                                    'status'    =>  true,
                                    'message'   =>  'Ya existe un local asignado como sede principal.'
                                ], 409);
                            } else {
                                $store->update($request->input());
                                return response()->json([
                                    'status'    =>  true,
                                    'message'   =>  'Actualización exitosa.'
                                ], 200);
                            }
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
        $store = Store::with('headquarters')
            ->where('enabled', '=', "1")
            ->get();

        $store->makeHidden(['phone', 'phone_2', 'phone_3', 'enabled', 'idbusiness', 'idheadquarter', 'created_at', 'updated_at']);
        return response()->json($store);
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
