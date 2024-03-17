<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;
    protected $fillable=[
        'facturaId',
        'cedulaFact',
        'nombreFact',
        'apellidoFact',
        'direccionFact',
        'celularFact',
        'reservaId',
    ];
    protected $primaryKey = 'facturaId';
    public $incrementing=true;
}
