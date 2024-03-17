<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ResultResponse;
use App\Models\Cancha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CanchaController extends Controller
{
   /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $canchas = Cancha::all();

        $resultResponse=new ResultResponse();

        $resultResponse->setData($canchas);
        $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
        $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);

        return response()->json($resultResponse);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator=$this->validateCancha($request);

        $resultResponse = new ResultResponse();

        try {
            $newCancha = new Cancha([

                'canchaNombre' => $request->get('canchaNombre'),
                'horario' => $request->get('horario'),
                'precioCancha' => $request->get('precioCancha'),
                'reservaId' => $request->get('reservaId'),
            ]);

            $newCancha->save();

            $resultResponse->setData($newCancha);
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
            DB::table('canchas')
                ->where('canchaNombre', 'like', '%' .$parameter. '%')
                ->orWhere('horario', 'like', '%' . $parameter . '%')
                ->orwhere('precioCancha', 'like', '%' .$parameter. '%')
                ->orwhere('reservaId', 'like', '%' .$parameter. '%')
                ->exists()
        ) {
            // Obtenemos el objeto con la consulta
            $cancha = DB::table('canchas')
                ->where('canchaNombre', 'like', '%' .$parameter. '%')
                ->orWhere('horario', 'like', '%' . $parameter . '%')
                ->orwhere('precioCancha', 'like', '%' .$parameter. '%')
                ->orwhere('reservaId', 'like', '%' .$parameter. '%')
                ->get();

                $resultResponse->setData($cancha);
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
    public function update(Request $request, $canchaNombre)
    {
        $validator=$this->validateCancha($request);
        $resultResponse = new ResultResponse();

        try{
            $cancha=Cancha::findOrFail($canchaNombre);
            try {

                $cancha->canchaNombre = $request->get('canchaNombre');
                $cancha->horario = $request->get('horario');
                $cancha->precioCancha = $request->get('precioCancha');
                $cancha->reservaId = $request->get('reservaId');

                $cancha->save();

                $resultResponse->setData($cancha);
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
        $this->validateCancha($request);
        $resultResponse = new ResultResponse();

        try {
            $cancha = Cancha::findOrFail($id);

            try{
                $cancha->canchaNombre=$request->get('canchaNombre', $cancha->canchaNombre);
                $cancha->horario=$request->get('horario', $cancha->horario);
                $cancha->precioCancha=$request->get('precioCancha', $cancha->precioCancha);
                $cancha->reservaId=$request->get('reservaId', $cancha->reservaId);

                $cancha->save();

                $resultResponse->setData($cancha);
                $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
                $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
            } catch(\Exception $e){
                Log::debug($e);
                $resultResponse->setData("Si modifica el canchaNombre debe ser unico en la tabla canchas. Si modifica el reservaId debe ser númerico, max:10 y único en la tabla reservas. Si modifica el precio debe ser decimal. Si modifica el horario debe estar disponible.");
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
    public function destroy($canchaNombre)
    {
        $resultResponse=new ResultResponse();
        try{
            $cancha=Cancha::findOrFail($canchaNombre);
            $cancha->delete();

            $resultResponse->setData($cancha);
            $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
        } catch(\Exception $e){
            $resultResponse->setData("No existen coincidencias con la búsqueda");
            $resultResponse->setStatusCode(ResultResponse::ERROR_ELEMENT_NOT_FOUND_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_ERROR_ELEMENT_NOT_FOUND_CODE);
        }

        return response()->json($resultResponse);
    }

    private function validateCancha($request){
        $rules = [];
        $messages = [];

        $rules['canchaNombre'] = 'required|max:20';
        $messages['canchaNombre.required'] = Lang::get('alerts.cancha_canchaNombre_required');
        $messages['canchaNombre.max'] = Lang::get('alerts.cancha_canchaNombre_max:20');

        $rules['reservaId'] = 'exists:reservas|numeric|max:10';
        $messages['reservaId.exists'] = Lang::get('alerts.cancha_reservaId_exists:reservas');
        $messages['reservaId.numeric'] = Lang::get('alerts.cancha_reservaId_numeric');
        $messages['reservaId.max'] = Lang::get('alerts.cancha_reservaId_max:10');

        $rules['horario'] = 'required|date_format:Y-m-d H:i:s|unique:canchas,horario,canchaNombre';
        $messages['horario.required'] = Lang::get('alerts.cancha_horario_required');
        $messages['horario.date_format'] = Lang::get('alerts.cancha_horario_date_format:Y-m-d H:i:s');
        $messages['horario.unique'] = 'The combination of canchaNombre and horario already exists.';

        $rules['precioCancha'] = 'required|regex:/^\d{1,6}(\.\d{1,2})?$/';
        $messages['precioCancha.required'] = Lang::get('alerts.cancha_precioCancha_required');
        $messages['precioCancha.regex'] = Lang::get('alerts.cancha_precioCancha_regex');

        return Validator::make($request->all(), $rules, $messages);
    }


}
