<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

    protected $fillable=[
        'horarioInicio',
        'horarioFin',
        'canchaNombre',
        'bebidaId',
        'cantidadBebidas',
        'precioTotal',
        'email',
    ];

    protected $primaryKey = 'reservaId';
    public $incrementing=true;


}
