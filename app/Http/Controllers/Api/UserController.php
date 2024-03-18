<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ResultResponse;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();

        $resultResponse=new ResultResponse();

        $resultResponse->setData($users);
        $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
        $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);

        return response()->json($resultResponse);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator=$this->validateuser($request);

        $resultResponse = new ResultResponse();

        try {
            $newUser = new User([

                'name' => $request->get('name'),
                'apellido' => $request->get('apellido'),
                'email' => $request->get('email'),
                'password' => $request->get('password'),
                'cellphone' => $request->get('cellphone'),
            ]);

            $newUser->save();

            $resultResponse->setData($newUser);
            $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
        } catch(\Exception $e){
            Log::debug($e);
            $resultResponse->setData($validator->messages());
            $resultResponse->setStatusCode(ResultResponse::ERROR_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_ERROR_CODE);
        }

        return response()->json($resultResponse);
    }

    /**
     * Display the specified resource.
     */
    public function show($parameter)
    {
        $resultResponse = new ResultResponse();

        if (
            DB::table('users')
                ->where('name', 'like', '%' .$parameter. '%')
                ->orWhere('apellido', 'like', '%' . $parameter . '%')
                ->orWhere('email', 'like', '%' . $parameter . '%')
                ->orwhere('password', 'like', '%' .$parameter. '%')
                ->orWhere('cellphone', 'like', '%' . $parameter . '%')
                ->exists()
        ) {
            // Obtenemos el objeto con la consulta
            $user = DB::table('users')
                ->where('name', 'like', '%' .$parameter. '%')
                ->orWhere('apellido', 'like', '%' . $parameter . '%')
                ->orWhere('email', 'like', '%' . $parameter . '%')
                ->orwhere('password', 'like', '%' .$parameter. '%')
                ->orWhere('cellphone', 'like', '%' . $parameter . '%')
                ->get();

                $resultResponse->setData($user);
                $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
                $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
        } else {
            $resultResponse->setData("No existen coincidencias con la búsqueda");
            $resultResponse->setStatusCode(ResultResponse::ERROR_ELEMENT_NOT_FOUND_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_ERROR_ELEMENT_NOT_FOUND_CODE);            }

        return response()->json($resultResponse);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $email)
    {
        $validator=$this->validateUser($request);
        $resultResponse = new ResultResponse();

        try{
            $user = User::where('email', 'like', $email)->firstOrFail();
            try {

                $user->name = $request->get('name');
                $user->apellido = $request->get('apellido');
                $user->email = $request->get('email');
                $user->password = $request->get('password');
                $user->cellphone = $request->get('cellphone');

                $user->save();

                $resultResponse->setData($user);
                $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
                $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
            } catch(\Exception $e){
                Log::debug($e);
                $resultResponse->setData($validator->messages());
                $resultResponse->setStatusCode(ResultResponse::ERROR_CODE);
                $resultResponse->setMessage(ResultResponse::TXT_ERROR_CODE);
            }

        } catch(\Exception $e){
            $resultResponse->setData("No existen coincidencias con la búsqueda");
            $resultResponse->setStatusCode(ResultResponse::ERROR_ELEMENT_NOT_FOUND_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_ERROR_ELEMENT_NOT_FOUND_CODE);

        }

        return response()->json($resultResponse);
    }


    public function put(Request $request, $email)
    {
        $this->validateUser($request);
        $resultResponse = new ResultResponse();

        try {
            $user = User::where('email', 'like', $email)->firstOrFail();

            try{
                $user->name=$request->get('name', $user->name);
                $user->apellido=$request->get('apellido', $user->apellido);
                $user->email=$request->get('email', $user->email);
                $user->password=$request->get('password', $user->password);
                $user->cellphone=$request->get('cellphone', $user->cellphone);

                $user->save();

                $resultResponse->setData($user);
                $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
                $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
            } catch(\Exception $e){
                Log::debug($e);
                $resultResponse->setData("Si modifica el name debe ser string, max:30. Si modifica el email debe contener @gmail.com al final.");
                $resultResponse->setStatusCode(ResultResponse::ERROR_CODE);
                $resultResponse->setMessage(ResultResponse::TXT_ERROR_CODE);
            }

        } catch(\Exception $e){
            $resultResponse->setData("No existen coincidencias con la búsqueda");
            $resultResponse->setStatusCode(ResultResponse::ERROR_ELEMENT_NOT_FOUND_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_ERROR_ELEMENT_NOT_FOUND_CODE);

        }

        return response()->json($resultResponse);


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($email)
{
    $resultResponse=new ResultResponse();
        try{
            $user = User::where('email', 'like', $email)->firstOrFail();
            try{

                $user->delete();

                $resultResponse->setData($user);
                $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
                $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
            } catch(\Exception $e){
                Log::debug($e);
                $resultResponse->setData("No es posible eliminar al user porque tiene al menos una reserva.");
                $resultResponse->setStatusCode(ResultResponse::ERROR_CODE);
                $resultResponse->setMessage(ResultResponse::TXT_ERROR_CODE);
            }

        }catch(\Exception $e){
            $resultResponse->setData("No existen coincidencias con la búsqueda");
            $resultResponse->setStatusCode(ResultResponse::ERROR_ELEMENT_NOT_FOUND_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_ERROR_ELEMENT_NOT_FOUND_CODE);
        }

        return response()->json($resultResponse);
}



    private function validateUser($request){
        $rules=[];
        $messages=[];

        $rules['email']='unique:users|required|max:40';
        $messages['email.unique:users']=Lang::get('alerts.user_email_unique:users');
        $messages['email.required']=Lang::get('alerts.user_email_required');
        $messages['email.max:40']=Lang::get('alerts.user_email_max:40');

        $rules['name']='required|max:30';
        $messages['name.required']=Lang::get('alerts.user_name_required');
        $messages['name.max:40']=Lang::get('alerts.user_name_max:40');

        $rules['apellido']='required|max:30';
        $messages['apellido.required']=Lang::get('alerts.user_apellido_required');
        $messages['apellido.max:30']=Lang::get('alerts.user_apellido_max:30');

        $rules['password']='required|min:8';
        $messages['password.required']=Lang::get('alerts.user_password_required');
        $messages['password.min:8']=Lang::get('alerts.user_password_min:8');

        $rules['cellphone']='numeric|max:30';
        $messages['cellphone.numeric']=Lang::get('alerts.user_cellphone_numeric');
        $messages['cellphone.max:10']=Lang::get('alerts.user_cellphone_max:10');

        return Validator::make($request->all(), $rules, $messages);
    }
}
