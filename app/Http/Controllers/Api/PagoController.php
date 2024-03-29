<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ResultResponse;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class PagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pagos = Pago::all();

        $resultResponse=new ResultResponse();

        $resultResponse->setData($pagos);
        $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
        $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);

        return response()->json($resultResponse);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator=$this->validatePago($request);

        $resultResponse = new ResultResponse();

        try {
            $newPago = new Pago([

                'imagenPago' => $request->get('imagenPago'),
                'metodoPago' => $request->get('metodoPago'),
                'facturaId' => $request->get('facturaId'),
            ]);

            $newPago->save();

            $resultResponse->setData($newPago);
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
            DB::table('pagos')
                ->where('id', 'like', '%' .$parameter. '%')
                ->orWhere('imagenPago', 'like', '%' . $parameter . '%')
                ->orwhere('metodoPago', 'like', '%' .$parameter. '%')
                ->orwhere('facturaId', 'like', '%' .$parameter. '%')
                ->exists()
        ) {
            // Obtenemos el objeto con la consulta
            $pago = DB::table('pagos')
                ->where('id', 'like', '%' .$parameter. '%')
                ->orWhere('imagenPago', 'like', '%' . $parameter . '%')
                ->orwhere('metodoPago', 'like', '%' .$parameter. '%')
                ->orwhere('facturaId', 'like', '%' .$parameter. '%')
                ->get();

                $resultResponse->setData($pago);
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
    public function update(Request $request, $id)
    {
        $validator=$this->validatePago($request);
        $resultResponse = new ResultResponse();

        try{
            $pago=Pago::findOrFail($id);
            try {

                $pago->imagenPago = $request->get('imagenPago');
                $pago->metodoPago = $request->get('metodoPago');
                $pago->facturaId = $request->get('facturaId');

                $pago->save();

                $resultResponse->setData($pago);
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
        $this->validatePago($request);
        $resultResponse = new ResultResponse();

        try {
            $pago = Pago::findOrFail($id);

            try{
                $pago->id=$request->get('id', $pago->id);
                $pago->imagenPago=$request->get('imagenPago', $pago->imagenPago);
                $pago->metodoPago=$request->get('metodoPago', $pago->metodoPago);
                $pago->facturaId=$request->get('facturaId', $pago->facturaId);

                $pago->save();

                $resultResponse->setData($pago);
                $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
                $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
            } catch(\Exception $e){
                Log::debug($e);
                $resultResponse->setData("Si modifica el id debe ser unico en la tabla pagos. Si modifica el facturaId debe ser númerico y único en la tabla FACTURAS.");
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
    public function destroy($id)
    {
        $resultResponse=new ResultResponse();
        try{
            $pago=Pago::findOrFail($id);
            $pago->delete();

            $resultResponse->setData($pago);
            $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
        } catch(\Exception $e){
            $resultResponse->setData("No existen coincidencias con la búsqueda");
            $resultResponse->setStatusCode(ResultResponse::ERROR_ELEMENT_NOT_FOUND_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_ERROR_ELEMENT_NOT_FOUND_CODE);
        }

        return response()->json($resultResponse);
    }

    private function validatePago($request){
        $rules=[];
        $messages=[];

        $rules['id']='unique:pagos';
        $messages['id.unique:pagos']=Lang::get('alerts.pago_id_unique:pagos');

        $rules['facturaId']='exists:facturas|numeric';
        $messages['facturaId.exists:facturas']=Lang::get('alerts.pago_facturaId_exists:facturas');
        $messages['facturaId.numeric']=Lang::get('alerts.pago_facturaId_numeric');

        $rules['imagenPago']='required';
        $messages['imagenPago.required']=Lang::get('alerts.pago_imagenPago_required');

        $rules['metodoPago']='required';
        $messages['metodoPago.required']=Lang::get('alerts.pago_metodoPago_required');

        return Validator::make($request->all(), $rules, $messages);
    }
}
