<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ResultResponse;
use App\Models\Bebida;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class BebidaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bebidas = Bebida::all();

        $resultResponse=new ResultResponse();

        $resultResponse->setData($bebidas);
        $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
        $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);

        return response()->json($resultResponse);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator=$this->validateBebida($request);

        $resultResponse = new ResultResponse();

        try {
            $newBebida = new Bebida([

                'bebidaId' => $request->get('bebidaId'),
                'precioBebida' => $request->get('precioBebida'),
                'stockBebida' => $request->get('stockBebida'),
                'reservaId' => $request->get('reservaId'),
            ]);

            $newBebida->save();

            $resultResponse->setData($newBebida);
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
            DB::table('bebidas')
                ->where('bebidaId', 'like', '%' .$parameter. '%')
                ->orWhere('precioBebida', 'like', '%' . $parameter . '%')
                ->orwhere('stockBebida', 'like', '%' .$parameter. '%')
                ->orwhere('reservaId', 'like', '%' .$parameter. '%')
                ->exists()
        ) {
            // Obtenemos el objeto con la consulta
            $bebida = DB::table('bebidas')
                ->where('bebidaId', 'like', '%' .$parameter. '%')
                ->orWhere('precioBebida', 'like', '%' . $parameter . '%')
                ->orwhere('stockBebida', 'like', '%' .$parameter. '%')
                ->orwhere('reservaId', 'like', '%' .$parameter. '%')
                ->get();

                $resultResponse->setData($bebida);
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
    public function update(Request $request, $bebidaId)
    {
        $validator=$this->validateBebida($request);
        $resultResponse = new ResultResponse();

        try{
            $bebida=Bebida::findOrFail($bebidaId);
            try {

                $bebida->bebidaId = $request->get('bebidaId');
                $bebida->precioBebida = $request->get('precioBebida');
                $bebida->stockBebida = $request->get('stockBebida');
                $bebida->reservaId = $request->get('reservaId');

                $bebida->save();

                $resultResponse->setData($bebida);
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
        $this->validateBebida($request);
        $resultResponse = new ResultResponse();

        try {
            $bebida = Bebida::findOrFail($id);

            try{
                $bebida->bebidaId=$request->get('bebidaId', $bebida->bebidaId);
                $bebida->precioBebida=$request->get('precioBebida', $bebida->precioBebida);
                $bebida->stockBebida=$request->get('stockBebida', $bebida->stockBebida);
                $bebida->reservaId=$request->get('reservaId', $bebida->reservaId);

                $bebida->save();

                $resultResponse->setData($bebida);
                $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
                $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
            } catch(\Exception $e){
                Log::debug($e);
                $resultResponse->setData("Si modifica el bebidaId debe ser unico en la tabla bebidas. Si modifica el reservaId debe ser númerico, max:10 y único en la tabla reservas. Si modifica el precio debe ser decimal. Si modifica el stock debe ser numerico.");
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
    public function destroy($bebidaId)
    {
        $resultResponse=new ResultResponse();
        try{
            $bebida=Bebida::findOrFail($bebidaId);
            $bebida->delete();

            $resultResponse->setData($bebida);
            $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
        } catch(\Exception $e){
            $resultResponse->setData("No existen coincidencias con la búsqueda");
            $resultResponse->setStatusCode(ResultResponse::ERROR_ELEMENT_NOT_FOUND_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_ERROR_ELEMENT_NOT_FOUND_CODE);
        }

        return response()->json($resultResponse);
    }

    private function validateBebida($request){
        $rules=[];
        $messages=[];

        $rules['bebidaId']='unique:bebidas|required|max:10';
        $messages['bebidaId.unique:bebidas']=Lang::get('alerts.bebida_bebidaId_unique:bebidas');
        $messages['bebidaId.required']=Lang::get('alerts.bebida_bebidaId_required');
        $messages['bebidaId.max:10']=Lang::get('alerts.bebida_bebidaId_max:10');

        $rules['reservaId']='exists:reservas|numeric|max:10';
        $messages['reservaId.exists:reservas']=Lang::get('alerts.bebida_reservaId_exists:reservas');
        $messages['reservaId.numeric']=Lang::get('alerts.bebida_reservaId_numeric');
        $messages['reservaId.max:10']=Lang::get('alerts.bebida_reservaId_max:10');

        $rules['precioBebida']='required|regex:/^\d{1,6}(\.\d{1,2})?$/';
        $messages['precioBebida.required']=Lang::get('alerts.bebida_precioBebida_required');
        $messages['precioBebida.regex']=Lang::get('alerts.bebida_precioBebida_regex');

        $rules['stockBebida']='required|integer';
        $messages['stockBebida.required']=Lang::get('alerts.bebida_stockBebida_required');
        $messages['stockBebida.integer']=Lang::get('alerts.bebida_stockBebida_integer');

        return Validator::make($request->all(), $rules, $messages);
    }
}
