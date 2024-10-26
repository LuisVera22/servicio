<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function create(Request $request)
    {
        $rules = [
            'username'  => 'required|string|unique:users',
            'password'  => 'required|string|min:6',
            'idrole'    => 'required'
        ];

        $messages = [
            'username.required' => 'El campo nombre es obligatorio.',
            'username.string'   => 'El campo nombre debe ser una cadena de texto.',
            'username.max'      => 'El campo nombre no debe exceder los 100 caracteres.',
            'username.unique'   => 'El nombre ya ha sido tomado.',
            'password.required' => 'El campo contraseña es obligatorio.',
            'password.string'   => 'El campo contraseña debe ser una cadena de texto.',
            'password.min'      => 'El campo contraseña debe tener al menos 6 caracteres.',
            'idrole.required'   => 'El campo idrole es obligatorio.',
        ];

        $validator = Validator::make($request->input(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'status'    => false,
                'errors'    => $validator->errors()->all()
            ], 400);
        }
        $user = User::create([
            'username'      => $request->username,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'idrole'        => $request->idrole,
            'idemployee'    => $request->idemployee
        ]);
        return response()->json([
            'status'    => true,
            'message'   => 'Usuario creado satisfactoriamente',
            'token'     => $user->createToken('API TOKEN')->plainTextToken
        ], 200);
    }

    public function login(Request $request)
    {
        $rules = [
            'username'  => 'required|string|max:100',
            'password'  => 'required|string|min:6'
        ];
        $messages = [
            'username.required' => 'El campo nombre es obligatorio.',
            'username.string'   => 'El campo nombre debe ser una cadena de texto.',            
            'password.required' => 'El campo contraseña es obligatorio.',
            'password.string'   => 'El campo contraseña debe ser una cadena de texto.',
            'password.min'      => 'El campo contraseña debe tener al menos 6 caracteres.',
        ];
        try {
            if ($this->isDatabaseDown()) {
                if ($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status'    => false,
                        'errors'    => ['mantenimiento.'],
                        'message'   => 'La aplicación esta en mantenimiento. Inténtalo de nuevo más tarde.'
                    ], 503);
                } else {
                    $validator = Validator::make($request->input(), $rules, $messages);
                    if ($validator->fails()) {
                        return response()->json([
                            'status'    => false,
                            'alert'     => 'rules',
                            'errors'    => $validator->errors()->all()
                        ], 400);
                    }
                    $user = User::where('username', $request->username)->with(['role','employee'])->first();
                    if (!$user) {
                        // Usuario no encontrado, el nombre de usuario es incorrecto
                        return response()->json([
                            'status'    => false,
                            'message'   => 'Nombre de usuario ingresado es incorrecto',
                            'errors'    => ['usuario incorrecto']
                        ], 401);
                    }
                    if (!Hash::check($request->password, $user->password)) {
                        // Contraseña incorrecta
                        return response()->json([
                            'status'    => false,
                            'message'   => 'Contraseña ingresado es incorrecto',
                            'errors'    => ['Contraseña incorrecta']
                        ], 401);
                    }
                    return response()->json([
                        'status'    => true,
                        'message'   => 'Usuario logeado satisfactoriamente',
                        'data'      => $user,
                        'token'     => $user->createToken('API TOKEN')->plainTextToken
                    ], 200);
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
    public function logout()
    {
        auth()->user()->tokens->each(function ($token, $key) {
            $token->delete();
        });
        return response()->json([
            'status'    => true,
            'message'   => 'Usuario desconectado satisfactoriamente'
        ], 200);
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
