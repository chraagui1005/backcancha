<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ResultResponse;
use App\Models\Bebida;
use App\Models\Cancha;
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


    public function store(Request $request)
{
    $validator = $this->validateReserva($request);

    $resultResponse = new ResultResponse();

    try {
        // Verificar si la cancha está disponible
        $canchaNombre = $request->get('canchaNombre');
        $horarioInicio = $request->get('horarioInicio');
        $horarioFin = $request->get('horarioFin');

        $cancha = Cancha::where('canchaNombre', $canchaNombre)
                        ->where('horarioInicio', $horarioInicio)
                        ->where('horarioFin', $horarioFin)
                        ->where('estado', 'Disponible')
                        ->first();

        if ($cancha) {
            // Obtener la cantidad solicitada de bebidas
            $cantidadBebidas = $request->get('cantidadBebidas');

            // Obtener el ID de la bebida
            $bebidaId = $request->get('bebidaId');

            // Obtener la bebida
            $bebida = Bebida::find($bebidaId);

            // Verificar si la bebida existe y si la cantidad solicitada no excede el stock
            if ($bebida && $cantidadBebidas <= $bebida->stockBebida) {
                // Actualizar el stock de las bebidas
                $this->actualizarStockBebidas(
                    $bebidaId,
                    $cantidadBebidas
                );

                // Crear la reserva
                $newReserva = new Reserva([
                    'horarioInicio' => $horarioInicio,
                    'horarioFin' => $horarioFin,
                    'canchaNombre' => $canchaNombre,
                    'bebidaId' => $bebidaId,
                    'cantidadBebidas' => $cantidadBebidas,
                    'precioTotal' => $request->get('precioTotal'),
                    'email' => $request->get('email'),
                ]);

                $newReserva->save();

                // Actualizar el estado de la cancha
                $this->actualizarEstado(
                    $canchaNombre,
                    $horarioInicio,
                    $horarioFin
                );

                $resultResponse->setData($newReserva);
                $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
                $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
            } else {
                // Manejar el caso donde la cantidad solicitada excede el stock
                $resultResponse->setData("La cantidad solicitada excede el stock disponible.");
                $resultResponse->setStatusCode(ResultResponse::ERROR_CODE);
                $resultResponse->setMessage(ResultResponse::TXT_ERROR_CODE);
            }
        } else {
            // Manejar el caso donde la cancha no está disponible
            $resultResponse->setData("La cancha no está disponible en el horario seleccionado.");
            $resultResponse->setStatusCode(ResultResponse::ERROR_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_ERROR_CODE);
        }
    } catch(\Exception $e){
        // Revertir la actualización del stock en caso de error
        $this->actualizarStockBebidas(
            $request->get('bebidaId'),
            $request->get('cantidadBebidas'),
            true
        );

        Log::debug($e);
        $resultResponse->setData($validator->messages());
        $resultResponse->setStatusCode(ResultResponse::ERROR_CODE);
        $resultResponse->setMessage(ResultResponse::TXT_ERROR_CODE);
    }

    return response()->json($resultResponse);
}


/**
     * Actualiza el estado de la cancha en función del nombre de la cancha, la hora de inicio y la hora de finalización.
     */
    private function actualizarEstado($canchaNombre, $horarioInicio, $horarioFin, $revertir = false)
    {
        // Obtener la cancha
        $cancha = Cancha::where('canchaNombre', $canchaNombre)
                        ->where('horarioInicio', $horarioInicio)
                        ->where('horarioFin', $horarioFin)
                        ->first();

        // Verificar si la cancha existe antes de actualizar el estado
        if ($cancha) {
            // Establecer el estado según si se está creando o revirtiendo la reserva
            $estado = $revertir ? 'Disponible' : 'Ocupado';

            // Actualizar el estado de la cancha
            $cancha->estado = $estado;
            $cancha->save();
        }
    }


/**
 * Actualiza el stock de las bebidas en la tabla correspondiente.
 */
/**
 * Actualiza el stock de las bebidas en la tabla correspondiente.
 */
private function actualizarStockBebidas($bebidaId, $cantidadBebidas, $revertir = false)
{
    // Obtener la bebida
    $bebida = Bebida::find($bebidaId);

    // Verificar si la bebida existe antes de actualizar el stock
    if ($bebida) {
        // Calcular el cambio en el stock según la reserva se esté creando o revirtiendo
        $cambioStock = $revertir ? $cantidadBebidas : -$cantidadBebidas;
        // Actualizar el stock sumando o restando el cambio calculado
        $bebida->stockBebida = max(0, $bebida->stockBebida + $cambioStock);
        $bebida->save();
    }
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
                ->orWhere('horarioInicio', 'like', '%' . $parameter . '%')
                ->orWhere('horarioFin', 'like', '%' . $parameter . '%')
                ->orWhere('canchaNombre', 'like', '%' . $parameter . '%')
                ->orWhere('bebidaId', 'like', '%' . $parameter . '%')
                ->orWhere('cantidadBebidas', 'like', '%' . $parameter . '%')
                ->orWhere('precioTotal', 'like', '%' . $parameter . '%')

                ->orwhere('email', 'like', '%' .$parameter. '%')
                ->exists()
        ) {
            // Obtenemos el objeto con la consulta
            $reserva = DB::table('reservas')
                ->where('reservaId', 'like', '%' .$parameter. '%')
                ->orWhere('horarioInicio', 'like', '%' . $parameter . '%')
                ->orWhere('horarioFin', 'like', '%' . $parameter . '%')
                ->orWhere('canchaNombre', 'like', '%' . $parameter . '%')
                ->orWhere('bebidaId', 'like', '%' . $parameter . '%')
                ->orWhere('cantidadBebidas', 'like', '%' . $parameter . '%')
                ->orWhere('precioTotal', 'like', '%' . $parameter . '%')
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
    $validator = $this->validateReserva($request);
    $resultResponse = new ResultResponse();

    try {
        // Obtener la información de la reserva antes de la actualización
        $reserva = Reserva::findOrFail($reservaId);
        $bebidaIdAnterior = $reserva->bebidaId;
        $cantidadBebidasAnterior = $reserva->cantidadBebidas;
        $canchaNombreAnterior = $reserva->canchaNombre;
        $horarioInicioAnterior = $reserva->horarioInicio;
        $horarioFinAnterior = $reserva->horarioFin;

        try {
            // Actualizar la reserva
            $reserva->horarioInicio = $request->get('horarioInicio');
            $reserva->horarioFin = $request->get('horarioFin');
            $reserva->canchaNombre = $request->get('canchaNombre');
            $reserva->bebidaId = $request->get('bebidaId');
            $reserva->cantidadBebidas = $request->get('cantidadBebidas');
            $reserva->precioTotal = $request->get('precioTotal');
            $reserva->email = $request->get('email');
            $reserva->save();

            // Revertir el stock de la bebida anterior
            $this->actualizarStockBebidas($bebidaIdAnterior, $cantidadBebidasAnterior, true); // true para revertir la actualización

            // Actualizar el stock de la nueva bebida
            $this->actualizarStockBebidas($request->get('bebidaId'), $request->get('cantidadBebidas'));

            // Revertir el estado de la cancha anterior
            $this->actualizarEstado($canchaNombreAnterior, $horarioInicioAnterior, $horarioFinAnterior, true); // true para revertir la actualización

            // Actualizar el estado de la nueva cancha
            $this->actualizarEstado($request->get('canchaNombre'), $request->get('horarioInicio'), $request->get('horarioFin'));

            $resultResponse->setData($reserva);
            $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
        } catch (\Exception $e) {
            Log::debug($e);
            $resultResponse->setData($validator->messages());
            $resultResponse->setStatusCode(ResultResponse::ERROR_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_ERROR_CODE);
        }
    } catch (\Exception $e) {
        $resultResponse->setData("No existen coincidencias con la búsqueda");
        $resultResponse->setStatusCode(ResultResponse::ERROR_ELEMENT_NOT_FOUND_CODE);
        $resultResponse->setMessage(ResultResponse::TXT_ERROR_ELEMENT_NOT_FOUND_CODE);
    }

    return response()->json($resultResponse);
}



public function put(Request $request, $id)
{
    $validator = $this->validateReserva($request);
    $resultResponse = new ResultResponse();

    try {
        $reserva = Reserva::findOrFail($id);

        // Almacenar los valores anteriores de la reserva
        $horarioInicioAnterior = $reserva->horarioInicio;
        $horarioFinAnterior = $reserva->horarioFin;
        $canchaNombreAnterior = $reserva->canchaNombre;
        $bebidaIdAnterior = $reserva->bebidaId;
        $cantidadBebidasAnterior = $reserva->cantidadBebidas;

        try {
            // Verificar si el nuevo ID de bebida existe en la tabla bebidas
            $nuevoBebidaId = $request->get('bebidaId', $reserva->bebidaId);
            $bebidaExistente = Bebida::where('bebidaId', $nuevoBebidaId)->exists();

            if (!$bebidaExistente) {
                throw new \Exception("El ID de la bebida proporcionado no existe en la tabla bebidas.");
            }

            // Actualizar la reserva
            $reserva->horarioInicio = $request->get('horarioInicio', $reserva->horarioInicio);
            $reserva->horarioFin = $request->get('horarioFin', $reserva->horarioFin);
            $reserva->canchaNombre = $request->get('canchaNombre', $reserva->canchaNombre);
            $reserva->bebidaId = $nuevoBebidaId;
            $reserva->cantidadBebidas = $request->get('cantidadBebidas', $reserva->cantidadBebidas);
            $reserva->precioTotal = $request->get('precioTotal', $reserva->precioTotal);
            $reserva->email = $request->get('email', $reserva->email);
            $reserva->save();

            // Revertir el stock de la bebida anterior
            if ($bebidaIdAnterior !== $reserva->bebidaId || $cantidadBebidasAnterior !== $reserva->cantidadBebidas) {
                $this->actualizarStockBebidas($bebidaIdAnterior, $cantidadBebidasAnterior, true); // true para revertir la actualización
            }

            // Actualizar el stock de la nueva bebida
            $this->actualizarStockBebidas($reserva->bebidaId, $reserva->cantidadBebidas);

            // Revertir el estado de la cancha anterior
            $this->actualizarEstado($canchaNombreAnterior, $horarioInicioAnterior, $horarioFinAnterior, true); // true para revertir la actualización

            // Actualizar el estado de la nueva cancha
            $this->actualizarEstado($reserva->canchaNombre, $reserva->horarioInicio, $reserva->horarioFin);

            $resultResponse->setData($reserva);
            $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
        } catch (\Exception $e) {
            Log::debug($e);
            $resultResponse->setData("Si modifica el reservaId debe ser númerico, max:10 y único. Si modifica el email debe estar creado en la tabla users.");
            $resultResponse->setStatusCode(ResultResponse::ERROR_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_ERROR_CODE);
        }
    } catch (\Exception $e) {
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
        $resultResponse = new ResultResponse();

        try {
            // Obtener la información de la reserva antes de eliminarla
            $reserva = Reserva::findOrFail($reservaId);
            $bebidaId = $reserva->bebidaId;
            $cantidadBebidas = $reserva->cantidadBebidas;
            $canchaNombre = $reserva->canchaNombre;
            $horarioInicio = $reserva->horarioInicio;
            $horarioFin = $reserva->horarioFin;

            // Eliminar la reserva
            $reserva->delete();

            // Actualizar el stock de bebidas y el estado de la cancha
            $this->actualizarStockBebidas($bebidaId, $cantidadBebidas, true); // true para revertir la actualización
            $this->actualizarEstado($canchaNombre, $horarioInicio, $horarioFin, true); // true para revertir la actualización

            $resultResponse->setData($reserva);
            $resultResponse->setStatusCode(ResultResponse::SUCCESS_CODE);
            $resultResponse->setMessage(ResultResponse::TXT_SUCCESS_CODE);
        } catch(\Exception $e) {
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

        $rules['canchaNombre'] = 'required|max:20|exists:canchas,canchaNombre';
        $messages['canchaNombre.required'] = Lang::get('alerts.reserva_canchaNombre_required');
        $messages['canchaNombre.max'] = Lang::get('alerts.reserva_canchaNombre_max:20');
        $messages['canchaNombre.exists:canchas'] = Lang::get('alerts.reserva_canchaNombre_exists:canchas');


        $rules['horarioInicio'] = 'required|date_format:Y-m-d H:i:s';
        $messages['horarioInicio.required'] = Lang::get('alerts.reserva_horarioInicio_required');
        $messages['horarioInicio.date_format'] = Lang::get('alerts.reserva_horarioInicio_date_format:Y-m-d H:i:s');


        $rules['horarioFin'] = 'required|date_format:Y-m-d H:i:s';
        $messages['horarioFin.required'] = Lang::get('alerts.reserva_horarioFin_required');
        $messages['horarioFin.date_format'] = Lang::get('alerts.reserva_horarioFin_date_format:Y-m-d H:i:s');


        $rules['bebidaId']='required|max:10|exists:bebidas,bebidaId';
        $messages['bebidaId.required']=Lang::get('alerts.reserva_bebidaId_required');
        $messages['bebidaId.max:10']=Lang::get('alerts.reserva_bebidaId_max:10');
        $messages['bebidaId.exists:bebidas'] = Lang::get('alerts.reserva_bebidaId_exists:bebidas');


        $rules['cantidadBebidas']='required|integer';
        $messages['cantidadBebidas.required']=Lang::get('alerts.reserva_cantidadBebidas_required');
        $messages['cantidadBebidas.integer']=Lang::get('alerts.reserva_cantidadBebidas_integer');


        $rules['email']='required|exists:users';
        $messages['email.required']=Lang::get('alerts.reserva_email_required');
        $messages['email.exists:users']=Lang::get('alerts.reserva_email_exists:users');

        return Validator::make($request->all(), $rules, $messages);
    }
}
