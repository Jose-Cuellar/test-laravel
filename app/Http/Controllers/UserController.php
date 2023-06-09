<?php

namespace App\Http\Controllers;

use App\Utilities;
use App\Models\User;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller as BaseController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    // Metodo para el registro de usuarios
    public function registerUser(Request $request){
        try{
            Log::info('========  Iniciando servicio registerUser  ========');
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::error('Los datos ingresados son inválidos: ' . $validator->errors());
                return Utilities::sendMessage(
                    Utilities::COD_RESPONSE_ERROR_CREATE,
                    'Los datos ingresados son inválidos',
                    true,
                    Utilities::COD_RESPONSE_HTTP_BAD_REQUEST,
                    $validator->errors()
                );
            }
            Log::info('Validacion request completada con exito');

            // Se valida si el usuario no existe
            $allUsers = null;
            $allUsers = User::where('email', '=', $request->email)->first();
            Log::info('allUsers ' . $allUsers);

            // Se realiza el registro del usuario si aun no esta registrado
            if($allUsers == null){
                if($request->email == Utilities::EMAIL_ADMIN){
                    Log::info('Se va a registrar el usuario administrador');
                    $newUserAdmin = new User;
                    $newUserAdmin->name = $request->name;
                    $newUserAdmin->last_name = $request->last_name;
                    $newUserAdmin->email = $request->email;
                    $newUserAdmin->password = bcrypt($request->input('password'));
                    $newUserAdmin->role_id = Utilities::COD_ROLE_ADMIN;
                    $newUserAdmin->save();

                    return Utilities::sendMessage(
                        Utilities::COD_RESPONSE_SUCCESS,
                        'Usuario administrador registrado correctamente',
                        false,
                        Utilities::COD_RESPONSE_HTTP_CREATED,
                        null
                    );
                }else{
                    Log::info('Se va a registrar el usuario');
                    $newUser = new User;
                    $newUser->name = $request->name;
                    $newUser->last_name = $request->last_name;
                    $newUser->email = $request->email;
                    $newUser->password = bcrypt($request->input('password'));
                    $newUser->role_id = Utilities::COD_ROLE_USER;
                    $newUser->save();
                }
            }
            else {
                return(
                    $response = [
                        "responseCode" => Utilities::COD_RESPONSE_ERROR_CREATE,
                        "responseMessage" => "El email ingresado ya se encuentra registrado",
                    ]
                );
            }

            return Utilities::sendMessage(
                Utilities::COD_RESPONSE_SUCCESS,
                'Registro exitoso',
                false,
                Utilities::COD_RESPONSE_HTTP_CREATED,
                null
            );
        }
        catch (Exception $Exception) {
            Log::error('Error no controlado al registrar el usuario: ' . $Exception);
            Log::info('***** registerUser *****');
            return Utilities::sendMessage(
                Utilities::COD_RESPONSE_ERROR_CREATE,
                'Ocurrió un error realizando el registro',
                true,
                Utilities::COD_RESPONSE_HTTP_ERROR,
                null
            );
        }
    }

    // Metodo para el login de usuarios
    public function loginUser(Request $request){
        try{
            Log::info('========  Iniciando servicio loginUser  ========');
            $validator = Validator::make($request->all(), [
                'user' => 'required|string',
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                Log::error('Los datos ingresados son inválidos: ' . $validator->errors());
                return Utilities::sendMessage(
                    Utilities::COD_RESPONSE_ERROR_CREATE,
                    'Los datos ingresados son inválidos',
                    true,
                    Utilities::COD_RESPONSE_HTTP_BAD_REQUEST,
                    $validator->errors()
                );
            }
            Log::info('Validacion request completada con exito');

            // Se valida la información del usuario
            $user = null;
            $user = User::where('email', '=', $request->user)->first();

            if($user == null){
                return(
                    $response = [
                        "responseCode" => Utilities::COD_RESPONSE_HTTP_UNAUTHORIZED,
                        "responseMessage" => "El email ingresado no se encuentra registrado",
                    ]
                );
            }
            else{
                if (Hash::check($request->input('password'), $user->password)) {
                    return Utilities::sendMessage(
                        Utilities::COD_RESPONSE_SUCCESS,
                        'Inicio de sesión exitoso',
                        false,
                        Utilities::COD_RESPONSE_HTTP_CREATED,
                        $user
                    );
                }else{
                    return(
                        $response = [
                            "responseCode" => Utilities::COD_RESPONSE_HTTP_UNAUTHORIZED,
                            "responseMessage" => "Contraseña incorrecta",
                        ]
                    );
                }
            }
        }
        catch (Exception $Exception) {
            Log::error('Error no controlado al iniciar sesión: ' . $Exception);
            Log::info('***** loginUser *****');
            return Utilities::sendMessage(
                Utilities::COD_RESPONSE_ERROR_CREATE,
                'Ocurrió un error al iniciar sesión',
                true,
                Utilities::COD_RESPONSE_HTTP_ERROR,
                null
            );
        }
    }

    // Metodo para obtener el usuario logueado
    public function getDataUser(Request $request){
        try{
            Log::info('========  Iniciando servicio getDataUser  ========');

            $userData = User::where('id', '=', $request->id)->first();
            Log::info('userData ' . $userData);

            return Utilities::sendMessage(
                Utilities::COD_RESPONSE_SUCCESS,
                'Información del usuario',
                false,
                Utilities::COD_RESPONSE_HTTP_CREATED,
                $userData
            );
        }
        catch (QueryException $QueryException) {
            Log::error('Ocurrió un error en la base de datos: ' . $QueryException);
            Log::info('***** getDataUser *****');
            return Utilities::sendMessage(
                Utilities::COD_RESPONSE_ERROR_CREATE_SQL,
                'Ocurrió un error obteniendo el usuario',
                true,
                Utilities::COD_RESPONSE_HTTP_ERROR,
                null
            );
        }
        catch (Exception $Exception) {
            Log::error('Error no controlado al obtener el usuario: ' . $Exception);
            Log::info('***** getDataUser *****');
            return Utilities::sendMessage(
                Utilities::COD_RESPONSE_ERROR_CREATE,
                'Ocurrió un error obteniendo el usuario',
                true,
                Utilities::COD_RESPONSE_HTTP_ERROR,
                null
            );
        }
    }

    // Metodo para actualización de datos de usuario
    public function updateUser(Request $request){
        try{
            Log::info('========  Iniciando servicio updateUser  ========');
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::error('Los datos ingresados son inválidos: ' . $validator->errors());
                return Utilities::sendMessage(
                    Utilities::COD_RESPONSE_ERROR_CREATE,
                    'Los datos ingresados son inválidos',
                    true,
                    Utilities::COD_RESPONSE_HTTP_BAD_REQUEST,
                    $validator->errors()
                );
            }
            Log::info('Validacion request completada con exito');

            // Se valida si el usuario no existe
            $user = null;
            $user = User::where('id', '=', $request->user_id)->first();
            Log::info('user ' . $user);

            // Se realiza la actualización de los datos
            if($user == null){
                return Utilities::sendMessage(
                    Utilities::COD_RESPONSE_ERROR_UPDATE,
                    'Ocurrió un error actualizando la información',
                    false,
                    Utilities::COD_RESPONSE_ERROR_UPDATE,
                    null
                );
            }else{
                Log::info('Se va a actualizar la información del usuario');
                $user->name = $request->name;
                $user->last_name = $request->last_name;
                $user->email = $request->email;
                $user->update();
            }

            return Utilities::sendMessage(
                Utilities::COD_RESPONSE_SUCCESS,
                'Datos actualizados con éxito',
                false,
                Utilities::COD_RESPONSE_HTTP_CREATED,
                null
            );
        }
        catch (QueryException $QueryException) {
            Log::error('Ocurrió un error en la base de datos: ' . $QueryException);
            Log::info('***** updateUser *****');
            return Utilities::sendMessage(
                Utilities::COD_RESPONSE_ERROR_CREATE_SQL,
                'No se actualizo la información del usuario',
                true,
                Utilities::COD_RESPONSE_HTTP_ERROR,
                null
            );
        }
        catch (Exception $Exception) {
            Log::error('Error no controlado al actualizar la información del usuario: ' . $Exception);
            Log::info('***** updateUser *****');
            return Utilities::sendMessage(
                Utilities::COD_RESPONSE_ERROR_CREATE,
                'No se actualizo la información del usuario',
                true,
                Utilities::COD_RESPONSE_HTTP_ERROR,
                null
            );
        }
    }
}