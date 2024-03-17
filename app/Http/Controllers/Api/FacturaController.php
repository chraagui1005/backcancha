<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ResultResponse;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class FacturaController extends Controller
{
       /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $facturas = Factura::all();

        $resultResponse=new ResultResponse();

        $resultResponse->setData($facturas);
        $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
        $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);

        return response()->json($resultResponse);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator=$this->validateFactura($request);

        $resultResponse = new ResultResponse();

        try {
            $newFactura = new Factura([

                'cedulaFact' => $request->get('cedulaFact'),
                'nombreFact' => $request->get('nombreFact'),
                'apellidoFact' => $request->get('apellidoFact'),
                'direccionFact' => $request->get('direccionFact'),
                'celularFact' => $request->get('celularFact'),
                'reservaId' => $request->get('reservaId'),
            ]);

            $newFactura->save();

            $resultResponse->setData($newFactura);
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
            DB::table('facturas')
                ->where('facturaId', 'like', '%' .$parameter. '%')
                ->orWhere('cedulaFact', 'like', '%' . $parameter . '%')
                ->orwhere('nombreFact', 'like', '%' .$parameter. '%')
                ->orwhere('apellidoFact', 'like', '%' .$parameter. '%')
                ->orwhere('direccionFact', 'like', '%' .$parameter. '%')
                ->orwhere('celularFact', 'like', '%' .$parameter. '%')
                ->orwhere('reservaId', 'like', '%' .$parameter. '%')
                ->exists()
        ) {
            // Obtenemos el objeto con la consulta
            $factura = DB::table('facturas')
                ->where('facturaId', 'like', '%' .$parameter. '%')
                ->orWhere('cedulaFact', 'like', '%' . $parameter . '%')
                ->orwhere('nombreFact', 'like', '%' .$parameter. '%')
                ->orwhere('apellidoFact', 'like', '%' .$parameter. '%')
                ->orwhere('direccionFact', 'like', '%' .$parameter. '%')
                ->orwhere('celularFact', 'like', '%' .$parameter. '%')
                ->orwhere('reservaId', 'like', '%' .$parameter. '%')
                ->get();

                $resultResponse->setData($factura);
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
    public function update(Request $request, $facturaId)
    {
        $validator=$this->validateFactura($request);
        $resultResponse = new ResultResponse();

        try{
            $factura=Factura::findOrFail($facturaId);
            try {

                $factura->cedulaFact = $request->get('cedulaFact');
                $factura->nombreFact = $request->get('nombreFact');
                $factura->apellidoFact = $request->get('apellidoFact');
                $factura->direccionFact = $request->get('direccionFact');
                $factura->celularFact = $request->get('celularFact');
                $factura->reservaId = $request->get('reservaId');

                $factura->save();

                $resultResponse->setData($factura);
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
        $this->validateFactura($request);
        $resultResponse = new ResultResponse();

        try {
            $factura = Factura::findOrFail($id);

            try{
                $factura->cedulaFact=$request->get('cedulaFact', $factura->cedulaFact);
                $factura->nombreFact=$request->get('nombreFact', $factura->nombreFact);
                $factura->apellidoFact=$request->get('apellidoFact', $factura->apellidoFact);
                $factura->direccionFact=$request->get('direccionFact', $factura->direccionFact);
                $factura->celularFact=$request->get('celularFact', $factura->celularFact);
                $factura->reservaId=$request->get('reservaId', $factura->reservaId);

                $factura->save();

                $resultResponse->setData($factura);
                $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
                $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
            } catch(\Exception $e){
                Log::debug($e);
                $resultResponse->setData("Si modifica el reservaId debe ser númerico, max:10 y único en la tabla reservas. Si modifica cedula o celular debe ser numero max 10.");
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
    public function destroy($facturaId)
    {
        $resultResponse=new ResultResponse();
        try{
            $factura=Factura::findOrFail($facturaId);
            $factura->delete();

            $resultResponse->setData($factura);
            $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
        } catch(\Exception $e){
            $resultResponse->setData("No existen coincidencias con la búsqueda");
            $resultResponse->setStatusCode(ResultResponse::ERROR_ELEMENT_NOT_FOUND_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_ERROR_ELEMENT_NOT_FOUND_CODE);
        }

        return response()->json($resultResponse);
    }

    private function validateFactura($request){
        $rules=[];
        $messages=[];

        $rules['facturaId']='unique:facturas|numeric';
        $messages['facturaId.unique:facturas']=Lang::get('alerts.factura_facturaId_unique:facturas');
        $messages['facturaId.numeric']=Lang::get('alerts.factura_facturaId_numeric');


        $rules['reservaId']='unique:facturas|exists:reservas|numeric|max:10|required';
        $messages['reservaId.unique:facturas']=Lang::get('alerts.factura_reservaId_unique:facturas');
        $messages['reservaId.exists:reservas']=Lang::get('alerts.factura_reservaId_exists:reservas');
        $messages['reservaId.numeric']=Lang::get('alerts.factura_reservaId_numeric');
        $messages['reservaId.max:10']=Lang::get('alerts.factura_reservaId_max:10');
        $messages['reservaId.required']=Lang::get('alerts.factura_reservaId_required');

        $rules['cedulaFact']='required|numeric|max:10';
        $messages['cedulaFact.required']=Lang::get('alerts.factura_cedulaFact_required');
        $messages['cedulaFact.numeric']=Lang::get('alerts.factura_cedulaFact_numeric');
        $messages['cedulaFact.max:10']=Lang::get('alerts.factura_cedulaFact_max:10');

        $rules['celularFact']='numeric|max:10';
        $messages['celularFact.numeric']=Lang::get('alerts.factura_celularFact_numeric');
        $messages['celularFact.max:10']=Lang::get('alerts.factura_celularFact_max:10');


        $rules['nombreFact']='required|max:30';
        $messages['nombreFact.required']=Lang::get('alerts.factura_nombreFact_required');
        $messages['nombreFact.max:30']=Lang::get('alerts.factura_nombreFact_max:30');

        $rules['apellidoFact']='required|max:30';
        $messages['apellidoFact.required']=Lang::get('alerts.factura_apellidoFact_required');
        $messages['apellidoFact.max:30']=Lang::get('alerts.factura_apellidoFact_max:30');

        $rules['direccionFact']='required|max:50';
        $messages['direccionFact.required']=Lang::get('alerts.factura_direccionFact_required');
        $messages['direccionFact.max:50']=Lang::get('alerts.factura_direccionFact_max:50');

        return Validator::make($request->all(), $rules, $messages);
    }
}
