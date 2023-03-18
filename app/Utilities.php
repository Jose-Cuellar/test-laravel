<?php

namespace App;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Webpatser\Uuid\Uuid;

class Utilities
{
    // Códigos de respuesta exitosa
    const COD_RESPONSE_SUCCESS = 200;
    const COD_RESPONSE_SUCCESS_CREATE = 1;

    // Códigos de respuesta errada
    const COD_RESPONSE_ERROR_CREATE = 1001;
    const COD_RESPONSE_ERROR_UPDATE = 1002;
    const COD_RESPONSE_ERROR_DELETE = 1003;
    const COD_RESPONSE_ERROR_LIST = 1004;
    const COD_RESPONSE_ERROR_LOGIN = 1005;
    const COD_RESPONSE_ERROR_UNAUTHORIZED = 1006;
    const COD_RESPONSE_ERROR_UPLOAD = 1007;
    const COD_RESPONSE_ERROR_FLOORS_DATA = 1008;
    const COD_RESPONSE_ERROR_SEND_MAIL = 1009;
    const COD_RESPONSE_ERROR_SHOW = 1010;
    const COD_RESPONSE_ERROR_LOGIN_LUB = 1011;
    const COD_RESPONSE_ERROR_LOGIN_USER = 1012;

    // Códigos de respuesta errada SQL
    const COD_RESPONSE_ERROR_CREATE_SQL = 2001;
    const COD_RESPONSE_ERROR_UPDATE_SQL = 2002;
    const COD_RESPONSE_ERROR_DELETE_SQL = 2003;
    const COD_RESPONSE_ERROR_LIST_SQL = 2004;

    // Códigos de respuesta HTTP
    const COD_RESPONSE_HTTP_OK = 200;
    const COD_RESPONSE_HTTP_CREATED = 201;
    const COD_RESPONSE_HTTP_BAD_REQUEST = 400;
    const COD_RESPONSE_HTTP_UNAUTHORIZED = 401;
    const COD_RESPONSE_HTTP_FORBIDDEN = 403;
    const COD_RESPONSE_HTTP_NOT_FOUND = 404;
    const COD_RESPONSE_HTTP_ERROR = 500;

    const SEND_REPORT_ERROR = 'sebastian.ramirez@tars.dev';

    const ORIGIN_LUBRICATOR = 2;

    // Estados
    const COD_STATUS_ACTIVE = 1;
    const COD_STATUS_REDIMIDO = 2;
    const COD_STATUS_SHOPPING_CART = 3;
    const COD_STATUS_BUY = 4;
    const COD_STATUS_PENDING = 5;
    const STATUS_APROBADO = 6;
    const STATUS_RECHAZADO = 7;
    const COD_STATUS_INACTIVE = 8;
    const COD_STATUS_PENDING_SEND = 9;
    const COD_STATUS_DELETE = 10;

    // Emails de administradores
    const EMAIL_ADMIN = "jucuellar4@gmail.com";

    //Roles
    const COD_ROLE_ADMIN = 1;
    const COD_ROLE_USER = 2;

    // Register origin
    const COD_REGISTER_ORIGIN_ORIGINAL = 1;
    const COD_REGISTER_ORIGIN_SECOND = 2;
    const COD_REGISTER_ORIGIN_GOOGLE = 3;
    const COD_REGISTER_ORIGIN_FACEBOOK = 4;

    // Dato otro en motos
    const COD_MOTORCYCLE_BRAND_OTHER = 44;
    const COD_MOTORCYCLE_USE_OTHER = 4;

    // Tipos de producto
    const COD_PRODUCT_TYPE_WON_RECARGAS = 1;
    const COD_PRODUCT_TYPE_WON_COMBOS = 2;
    const COD_PRODUCT_TYPE_MOTOS = 3;
    const COD_PRODUCT_TYPE_WON_BONOS = 4;
    const COD_PRODUCT_TYPE_BONO_COMBUSTIBLE = 5;    
    const COD_PRODUCT_TYPE = 6;
    const COD_PRODUCT_TYPE_SOAT = 7;
    
    // mensaje de respuesta
    public static function sendMessage($cod, $message, $error, $codHttp, $data)
    {
        Log::info('Armando mensaje de envío');
        try {
            if (isset($cod) && isset($message) && isset($error) && isset($codHttp)) {
                Log::info('Llegaron todos los datos');
                $response = [
                    'cod' => $cod,
                    'error' => $error,
                    'message' => $message,
                    'data' => $data
                ];
                return response()->json($response, $codHttp);
            } else {
                Log::warning('No llegaron los datos necesarios para armar el mensaje');
                return response()->json([], 500);
            }
        } catch (Exception $e) {
            Log::warning('Ocurrión un error inesperado armando el mensaje');
            return response()->json([], 500);
        }
    }
    public static function saveRegister($action, $userAdmin){
        try {
            $register = new Register();
            $register->origin = $action;
            $register->user_id = $userAdmin;
            $register->save();
            return $register->id;
        } catch (Exception $e) {
            Log::info('ERROR: ' . $e->getMessage());
            return 'error';
        }
    }
    public static function saveLog($userId, $registerId, $statusId ,$message )
    {
        try {
            Log::info($userId);
            Log::info($registerId);
            Log::info($statusId);
            Log::info($message);

            if($registerId != null){
                $register = new RegisterLog();
                $register->register_id = $registerId;
                $register->user_id = $userId;
                $register->status_id = $statusId;
                $register->message = $message;
                Log::info($register);
                $register->save();
                return 'ok';
            } else {
                Log::info("Error: no se registro el register Log");
            }
        } catch (Exception $e) {
            Log::info('ERROR: ' . $e->getMessage());
            return 'error';
        }
    }

    public static function validateFile($file, $type)
    {
        try {
            Log::info('SUBIENDO ARCHIVO');
            if($file){
                $sizeFile = $file->getSize();
                $extension = $file->getClientOriginalExtension();
            }
            if ($sizeFile <= 1024000) {
                Log::info('Pasó la validación de tamaño');
                if ($type == 6 || $type == 7 || $type == 8) {
                    if ($extension != "png" && $extension != "jpg" && $extension != "jpeg") {
                        Log::error('La extensión del archivo es inválida: ', ['Extension', $extension]);
                        return 'error_extension';
                    }
                } else {
                    if ($extension != "doc" && $extension != "odt" && $extension != "docx" && $extension != "pdf") {
                        Log::error('La extensión del archivo es inválida: ', ['Extension', $extension]);
                        return 'error_extension';
                    }
                }
            } else {
                Log::error('El archivo pesa mas de 1MB');
                return 'error_size';
            }
        } catch (\Exception $e) {
            Log::error('Ocurrió un error inesperado subiendo el archivo: ' . $e->getMessage());
            return 'error';
        }
    }

    public static function uploadFile($file, $name)
    {
        Log::info('Subiendo archivo');
        $date = Carbon::now();
        $extension = $file->getClientOriginalExtension();

        $newName = $name . '.' . $extension;

        $path = $file->storeAs($date->format('d-m-Y'), str_replace(' ', '_', $newName));
        $url = env('APP_URL') . Storage::url($path);
        return $url;
    }
}