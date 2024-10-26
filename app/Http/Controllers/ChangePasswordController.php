<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Mail\PasswordMail;
use App\Models\Employee;

class ChangePasswordController extends Controller
{
    public function store(Request $request)
    {
        $rules = [
            'email'     => 'required|email',
            'password'  => 'required|min:6',
        ];

        $messages = [
            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.email'       => 'El formato del correo electrónico es inválido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La nueva contraseña debe tener al menos 6 caracteres.',
        ];

        try {
            if ($this->isDatabaseDown()) {
                if ($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status'    => false,
                        'errors'    => ['mantenimiento'],
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
                    } else {
                        $emailEmployee = Employee::where([['email', $request['email']], ['enabled', "1"]])->first();

                        if (isset($emailEmployee)) {
                            $user = User::where('idemployee', $emailEmployee->id)->first();
                            if (isset($user)) {
                                if ($user->password_changed >= 3) {
                                    return response()->json([
                                        'status' => true,
                                        'message' => 'Ya has alcanzado el límite de 3 cambios de contraseña, por favor contacta con el administrador.',
                                    ], 403);
                        
                                } else {
                                    $user->update([
                                        'password'          => Hash::make($request['password']),
                                        'password_changed'  => $user->password_changed + 1,
                                        'email_verified_at' => $request['fecha']
                                    ]);

                                    Mail::to($emailEmployee->email)->send(new PasswordMail($user->username,$request['password']));

                                    return response()->json([
                                        'status'    =>  true,
                                        'message'   =>  'Actualización exitosa.'
                                    ], 200);
                                }
                            } else {
                                return response()->json([
                                    'status'    => true,
                                    'message'   => 'El empleado no cuenta con acceso al sistema.',
                                ], 409);
                            }
                        } else {
                            return response()->json([
                                'status'    => true,
                                'message'   => 'El correo electrónico ingresado no existe en el sistema.',
                            ], 409);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'errors'  => ['No se pudo conectar a la base de datos'],
                'message' => 'Error: '  . $e->getMessage()
            ], 500);
        }
    }

    private function isAppDownForMaintenance()
    {
        return app()->isDownForMaintenance();
    }

    // Verifica si la base de datos está inaccesible
    private function isDatabaseDown()
    {
        try {
            DB::connection()->getPdo(); // Intenta obtener la conexión PDO
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
