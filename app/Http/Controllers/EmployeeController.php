<?php

namespace App\Http\Controllers;

use App\Interfaces\EmployeeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeController
{
    private $repository;

    public function __construct(
        EmployeeRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function index()
    {
        $employees = Employee::with(['typedocument', 'store', 'user'])
            ->orderBy('id', 'desc')
            ->get();
        // Ocultar los valores del modelo Store dentro de cada empleado
        /*  $employees->each(function ($employee) {
            $employee->store->makeHidden(['enabled', 'phone', 'phone_2', 'phone_3', 'idbusiness', 'created_at', 'updated_at']);
        }); */
        return response()->json($employees, 200);
    }
    public function show(Employee $employee)
    {
        $employee->load([
            'typedocument',
            'store',
            'user'
        ]);
        // Ocultar los valores del modelo Store dentro del empleado
        //$employee->store->makeHidden(['enabled', 'phone', 'phone_2', 'phone_3', 'idbusiness', 'created_at', 'updated_at']);
        return response()->json(['status'   => true, 'data' => $employee], 200);
    }
    public function store(Request $request)
    {
        $rules = [
            'name'              =>  'required|string|',
            'lastname'          =>  'required|string|',
            'idtype_document'   =>  'required',
            'number_document'   =>  'required|string|',
            'idstore'           =>  'required',
            'idroles'           =>  'required'
        ];
        $messages = [
            'name.required'             =>  'El campo nombre es obligatorio.',
            'name.string'               =>  'El campo nombre debe ser una cadena de texto.',
            'lastname.required'         =>  'El campo apellido es obligatorio.',
            'lastname.string'           =>  'El campo apellido debe ser una cadena de texto.',
            'number_document.required'  =>  'El campo número documento es obligatorio.',
            'number_document.string'    =>  'El campo número documento debe ser una cadena de texto.',
            'idstore.required'          =>  'El campo sede es obligatorio.',
            'idtype_document.required'  =>  'El campo tipo documento es obligatorio.',
            'idroles.required'          =>  'El campo rol es obligatorio.'
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
                        $repeatemployee = $this->repository->repeatemployee($request['number_document']);
                        if (count($repeatemployee) > 0) {
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El documento ya se encuentra registrado.'
                            ], 409);
                        } else {
                            try {
                                // Iniciar una transacción de base de datos
                                DB::beginTransaction();
                                $employee = new Employee($request->only([
                                    'name',
                                    'lastname',
                                    'idtype_document',
                                    'number_document',
                                    'email',
                                    'idstore',
                                    'cell_phone',
                                    'address'
                                ]));

                                $employee->save();
                                if (!empty($request['username']) && !empty($request['password'])) {
                                    $repeatUser = $this->repository->repeatUser($request['unsername']);
                                    if (count($repeatUser) > 0) {
                                        DB::rollBack();
                                        return response()->json([
                                            'status'    =>  true,
                                            'message'   =>  'El usuario se repite para otro empleado.'
                                        ], 409);
                                    } else {
                                        $user = new User([
                                            'username'  => $request['username'],
                                            'password'  => Hash::make($request['password']),
                                            'idrole'   => $request['idroles']
                                        ]);
                                        $employee->user()->save($user);
                                    }
                                } else if (!empty($request['username']) && empty($request['password'])) {
                                    DB::rollBack();
                                    return response()->json([
                                        'status'    => false,
                                        'alert'     => 'rules',
                                        'errors'    => ['El usuario debe tener una contraseña.']
                                    ], 400);
                                } else if (empty($request['username']) && empty($request['password'])) {
                                    $user = new User([
                                        'idrole'   => $request['idroles']
                                    ]);
                                    $employee->user()->save($user);
                                }

                                // Confirmar la transacción si todo está bien
                                DB::commit();

                                return response()->json([
                                    'status'    => true,
                                    'message'   => 'Registro Generado.',
                                ], 200);
                            } catch (\Exception $e) {
                                // Revertir la transacción si hay un error
                                DB::rollBack();

                                return response()->json([
                                    'status'    => false,
                                    'errors'    => ['Error al crear el registro.'],
                                    'message'   => 'Error: ' . $e->getMessage(),
                                ], 500);
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
    public function update(Request $request, Employee $employee)
    {
        $rules = [
            'name'              =>  'required|string|',
            'lastname'          =>  'required|string|',
            'idtype_document'   =>  'required',
            'number_document'   =>  'required|string|',
            'idstore'           =>  'required',
            'idroles'           =>  'required'
        ];

        $messages = [
            'name.required'             =>  'El campo nombre es obligatorio.',
            'name.string'               =>  'El campo nombre debe ser una cadena de texto.',
            'lastname.required'         =>  'El campo apellido es obligatorio.',
            'lastname.string'           =>  'El campo apellido debe ser una cadena de texto.',
            'idstore.required'          =>  'El campo sede es obligatorio.',
            'idtype_document.required'  =>  'El campo tipo documento es obligatorio.',
            'idroles.required'          =>  'El campo rol es obligatorio.'
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
                        $repeatemployee = $this->repository->repeatemployeeUpdate($request['number_document'], $employee->id);
                        if (count($repeatemployee) > 0) {
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'El documento ya se encuentra registrado.'
                            ], 409);
                        } else {
                            try {
                                DB::beginTransaction();
                                $employee = Employee::findOrFail($employee->id); // Buscar el empleado por su ID

                                $employee->update($request->only([
                                    'name',
                                    'lastname',
                                    'idtype_document',
                                    'number_document',
                                    'email',
                                    'idstore',
                                    'cell_phone',
                                    'address'
                                ]));
                                $user = $employee->user; // Obtener el usuario asociado
                                $repeatUser = $this->repository->repeatUserUpdate($request['username'], $user->id);
                                if (!empty($request['username']) && !empty($request['password'])) {
                                    if (count($repeatUser) > 0) {
                                        DB::rollBack();
                                        return response()->json([
                                            'status'    =>  true,
                                            'message'   =>  'El usuario se repite para otro empleado.'
                                        ], 409);
                                    } else {
                                        $user->update([
                                            'username'  => $request['username'],
                                            'password'  => Hash::make($request['password']),
                                            'idrole'   => $request['idroles']
                                        ]);
                                    }
                                } else if (!empty($request['username']) && empty($request['password'])) {
                                    $acountexists = $this->repository->acountexists($request['username'], $user->id);
                                    if ($acountexists != "" || $acountexists != null) {
                                        if (count($repeatUser) > 0) {
                                            DB::rollBack();
                                            return response()->json([
                                                'status'    =>  true,
                                                'message'   =>  $repeatUser
                                            ], 409);
                                        } else {
                                            $user->update([
                                                'username'  => $request['username'],
                                                'idrole'    => $request['idroles']
                                            ]);
                                        }
                                    } else {
                                        DB::rollBack();
                                        return response()->json([
                                            'status'    => false,
                                            'alert'     => 'rules',
                                            'errors'    => ['El usuario debe tener una contraseña.']
                                        ], 400);
                                    }
                                } else if (empty($request['username']) && empty($request['password'])) {
                                    $user->update([
                                        'username'  => null,
                                        'password'  => null,
                                        'idrole'    => $request['idroles']
                                    ]);
                                }
                                DB::commit();

                                return response()->json([
                                    'status'    => true,
                                    'message'   => 'Actualización exitosa.',
                                ], 200);
                            } catch (\Exception $e) {
                                DB::rollBack();

                                return response()->json([
                                    'status'    => false,
                                    'errors'    => ['Error al crear el registro.'],
                                    'message'   => 'Error: ' . $e->getMessage(),
                                ], 500);
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
    public function listEmployeeRolActivos($name)
    {
        $employees = Employee::with(['user'])
            ->whereHas('user.role', function ($query) use ($name) {
                // Filtrar por el nombre del rol
                $query->where('description', $name);
            })
            ->where('enabled', 1)
            ->get();

        return response()->json($employees);
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
