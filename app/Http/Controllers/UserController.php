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
            $allUsers = User::where('email', '=', $request->email)->first();
            Log::info('allUsers ' . $allUsers);

            // Se realiza el registro del usuario si aun no esta registrado
            if($allUsers == ''){
                Log::info('Se va a registrar el usuario');
                $newUser = new User;
                $newUser->name = $request->name;
                $newUser->last_name = $request->last_name;
                $newUser->email = $request->email;
                $newUser->password = bcrypt($request->input('password'));
                $newUser->save();
            } 
            else {
                return Utilities::sendMessage(
                    Utilities::COD_RESPONSE_ERROR_CREATE,
                    'El email ingresado ya se encuentra registrado',
                    true,
                    Utilities::COD_RESPONSE_HTTP_ERROR,
                    null
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
        catch (QueryException $QueryException) {
            Log::error('Ocurrió un error en la base de datos: ' . $QueryException);
            Log::info('***** registerUser *****');
            return Utilities::sendMessage(
                Utilities::COD_RESPONSE_ERROR_CREATE_SQL,
                'No se actualizo la orden',
                true,
                Utilities::COD_RESPONSE_HTTP_ERROR,
                null
            );
        }
        catch (Exception $Exception) {
            Log::error('Error no controlado al actualizar la orden: ' . $Exception);
            Log::info('***** registerUser *****');
            return Utilities::sendMessage(
                Utilities::COD_RESPONSE_ERROR_CREATE,
                'No se actualizo la orden',
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
            Log::notice('Validacion request completada con exito');

        }
        catch (QueryException $QueryException) {
            Log::error('Ocurrió un error en la base de datos: ' . $QueryException);
            Log::info('***** loginUser *****');
            return Utilities::sendMessage(
                Utilities::COD_RESPONSE_ERROR_CREATE_SQL,
                'No se actualizo la orden',
                true,
                Utilities::COD_RESPONSE_HTTP_ERROR,
                null
            );
        }
        catch (Exception $Exception) {
            Log::error('Error no controlado al actualizar la orden: ' . $Exception);
            Log::info('***** loginUser *****');
            return Utilities::sendMessage(
                Utilities::COD_RESPONSE_ERROR_CREATE,
                'No se actualizo la orden',
                true,
                Utilities::COD_RESPONSE_HTTP_ERROR,
                null
            );
        }
    }
}