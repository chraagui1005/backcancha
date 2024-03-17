<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ResultResponse;
use App\Models\Reserva;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class ReservaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reservas = Reserva::all();

        $resultResponse=new ResultResponse();

        $resultResponse->setData($reservas);
        $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
        $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);

        return response()->json($resultResponse);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator=$this->validateReserva($request);

        $resultResponse = new ResultResponse();

        try {
            $newReserva = new Reserva([

                'descripcion' => $request->get('descripcion'),
                'email' => $request->get('email'),
            ]);

            $newReserva->save();

            $resultResponse->setData($newReserva);
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
            DB::table('reservas')
                ->where('reservaId', 'like', '%' .$parameter. '%')
                ->orWhere('descripcion', 'like', '%' . $parameter . '%')
                ->orwhere('email', 'like', '%' .$parameter. '%')
                ->exists()
        ) {
            // Obtenemos el objeto con la consulta
            $reserva = DB::table('reservas')
                ->where('reservaId', 'like', '%' .$parameter. '%')
                ->orWhere('descripcion', 'like', '%' . $parameter . '%')
                ->orwhere('email', 'like', '%' .$parameter. '%')
                ->get();

                $resultResponse->setData($reserva);
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
    public function update(Request $request, $reservaId)
    {
        $validator=$this->validateReserva($request);
        $resultResponse = new ResultResponse();

        try{
            $reserva=Reserva::findOrFail($reservaId);
            try {

                $reserva->descripcion = $request->get('descripcion');
                $reserva->email = $request->get('email');

                $reserva->save();

                $resultResponse->setData($reserva);
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

    public function put(Request $request, $id)
    {
        $this->validateReserva($request);
        $resultResponse = new ResultResponse();

        try {
            $reserva = Reserva::findOrFail($id);

            try{
                $reserva->descripcion=$request->get('descripcion', $reserva->descripcion);
                $reserva->email=$request->get('email', $reserva->email);

                $reserva->save();

                $resultResponse->setData($reserva);
                $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
                $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
            } catch(\Exception $e){
                Log::debug($e);
                $resultResponse->setData("Si modifica el reservaId debe ser númerico, max:10 y único. Si modifica el email deber estar creado en la tabla users.");
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
    public function destroy($reservaId)
    {
        $resultResponse=new ResultResponse();
        try{
            $reserva=Reserva::findOrFail($reservaId);
            $reserva->delete();

            $resultResponse->setData($reserva);
            $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
        } catch(\Exception $e){
            $resultResponse->setData("No existen coincidencias con la búsqueda");
            $resultResponse->setStatusCode(ResultResponse::ERROR_ELEMENT_NOT_FOUND_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_ERROR_ELEMENT_NOT_FOUND_CODE);
        }

        return response()->json($resultResponse);
    }

    private function validateReserva($request){
        $rules=[];
        $messages=[];

        $rules['reservaId']='unique:reservas|numeric|max:10';
        $messages['reservaId.unique:reservas']=Lang::get('alerts.reserva_reservaId_unique:reservas');
        $messages['reservaId.numeric']=Lang::get('alerts.reserva_reservaId_numeric');
        $messages['reservaId.max:10']=Lang::get('alerts.reserva_reservaId_max:10');

        $rules['email']='required|exists:users';
        $messages['email.required']=Lang::get('alerts.reserva_email_required');
        $messages['email.exists:users']=Lang::get('alerts.reserva_email_exists:users');

        return Validator::make($request->all(), $rules, $messages);
    }
}
